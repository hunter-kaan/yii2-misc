<?php
namespace valiant\yii2\behaviors;

use dosamigos\transliterator\TransliteratorHelper;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * Class SluggableBehavior
 * @package common\behaviors
 *
 * @property ActiveRecord $owner
 */
class SluggableBehavior extends Behavior
{
    /** @var string incoming  attribute name */
    public $from_attribute;

    /** @var string outgoing attribute name */
    public $to_attribute;

    /** @var bool */
    public $transliteration = true;

    /** @var bool|callable */
    public $unique = false;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'process',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'process'
        ];
    }

    public function process()
    {
        $attribute = empty($this->owner->{$this->to_attribute}) ? $this->from_attribute : $this->to_attribute;
        $this->owner->{$this->to_attribute} = $this->generateSlug($this->owner->{$attribute});
    }

    private function generateSlug($slug)
    {
        $slug = $this->transliterationSlug($slug);

        if (!$this->checkUnique($slug)) {
            for ($suffix = 2; !$this->checkUnique($new_slug = $slug . '-' . $suffix); $suffix++) ;
            $slug = $new_slug;
        }

        return $slug;
    }

    private function transliterationSlug($slug)
    {
        if ($this->transliteration) {
            $slug = Inflector::slug(TransliteratorHelper::process($slug), '-', true);
        } else {
            $slug = preg_replace('/[^\p{L}\p{Nd}]+/u', '-', $slug);
            $string = trim($slug, '-');
            $slug = strtolower($string);
        }
        return $slug;
    }

    private function checkUnique($slug)
    {
        if (is_bool($this->unique) && !$this->unique) {
            return true;
        }

        if (is_callable($this->unique)) {
            return call_user_func($this->unique, $slug, $this->from_attribute, $this->to_attribute);
        }

        $pk = $this->owner->primaryKey();
        $pk = $pk[0];

        $condition = $this->to_attribute . ' = :to_attribute';
        $params = [':to_attribute' => $slug];
        if (!$this->owner->isNewRecord) {
            $condition .= ' and ' . $pk . ' != :pk';
            $params[':pk'] = $this->owner->{$pk};
        }

        return !$this->owner->find()->andWhere($condition, $params)->exists();
    }

}