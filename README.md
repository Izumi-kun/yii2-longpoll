Yii2 longpoll
=============

Implements long polling AJAX mechanism.

[![Latest Stable Version](https://poser.pugx.org/izumi-kun/yii2-longpoll/v/stable)](https://packagist.org/packages/izumi-kun/yii2-longpoll)
[![Total Downloads](https://poser.pugx.org/izumi-kun/yii2-longpoll/downloads)](https://packagist.org/packages/izumi-kun/yii2-longpoll)
[![Build Status](https://travis-ci.org/Izumi-kun/yii2-longpoll.svg?branch=master)](https://travis-ci.org/Izumi-kun/yii2-longpoll)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Izumi-kun/yii2-longpoll/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Izumi-kun/yii2-longpoll/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Izumi-kun/yii2-longpoll/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Izumi-kun/yii2-longpoll/?branch=master)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist izumi-kun/yii2-longpoll
```

or add

```
"izumi-kun/yii2-longpoll": "~1.0.0"
```

to the require section of your composer.json.

Basic Usage
-----------

### Controller

```php
class SiteController extends Controller
{
    public function actions()
    {
        return [
            'polling' => [
                'class' => LongPollAction::class,
                'events' => ['eventId'],
                'callback' => [$this, 'longPollCallback'],
            ],
        ];
    }
    public function longPollCallback(Server $server)
    {
        $server->responseData = 'any data';
    }
}
```

### View

```php
LongPoll::widget([
    'url' => ['site/polling'],
    'events' => ['eventId'],
    'callback' => 'console.log',
]);
```

### Model

```php
\izumi\longpoll\Event::triggerByKey('eventId');
```

Example
-------

[https://github.com/Izumi-kun/yii2-longpoll-example](https://github.com/Izumi-kun/yii2-longpoll-example)

License
-------

BSD-3-Clause
