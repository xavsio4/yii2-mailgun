Aface Mailgun SDK PHP
=====================


Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/). This requires the 
composer-asset-plugin, which is also a dependency for yii2 – so if you have yii2 installed, you are most likely already 
set.


Either run

```
composer require analyticsface/yii2-mailgun:*
```
or add

```json
"analyticsface/yii2-mailgun" : "*"
```

to the require section of your application's `composer.json` file.

Usage
-----

```
'components' => [
     ...
    'mailer' => [
        'class' => 'aface\mailgun\Mailer',
        'viewPath' => '@common/mail',
        'key' => 'key-example',
        'domain' => 'mg.example.com',
    ],
    ...
],
```