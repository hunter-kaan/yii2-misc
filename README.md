Yii2 misc
=========
Yii2 misc

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist valiant/yii2-misc "*"
```

or add

```
"valiant/yii2-misc": "*"
```

to the require section of your `composer.json` file.


Usage
-----

SluggableBehavior:

```php

	public function behaviors()
	{
		return [
		    ...
			'slug' => [
				'class' => valiant\yii2\behaviors\SluggableBehavior::className(),
				'from_attribute' => 'name_attribute',
				'to_attribute' => 'slug_attribute',
				'transliteration' => true,
				'unique' => true,
			],
		];
	}

```