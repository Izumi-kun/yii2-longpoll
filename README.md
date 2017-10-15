# Yii2 longpoll

Implements long polling AJAX mechanism.

[![Latest Stable Version](https://poser.pugx.org/izumi-kun/yii2-longpoll/v/stable)](https://packagist.org/packages/izumi-kun/yii2-longpoll)
[![Total Downloads](https://poser.pugx.org/izumi-kun/yii2-longpoll/downloads)](https://packagist.org/packages/izumi-kun/yii2-longpoll)
[![Build Status](https://travis-ci.org/Izumi-kun/yii2-longpoll.svg?branch=master)](https://travis-ci.org/Izumi-kun/yii2-longpoll)

## Usage

### Controller

```php
class SiteController extends Controller
{
    public function actions()
    {
        return [
            'polling' => [
                'class' => 'izumi\longpoll\LongPollAction',
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

## Example

[https://github.com/Izumi-kun/yii2-longpoll-example](https://github.com/Izumi-kun/yii2-longpoll-example)

## License

BSD-3-Clause
