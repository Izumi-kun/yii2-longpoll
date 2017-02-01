# Yii2 longpoll

Implements long polling AJAX mechanism.

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

## Example

[https://github.com/Izumi-kun/yii2-longpoll-example](https://github.com/Izumi-kun/yii2-longpoll-example)

## License

BSD-3-Clause
