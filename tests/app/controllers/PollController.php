<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests\app\controllers;

use izumi\longpoll\Server;
use Yii;
use yii\web\Controller;

class PollController extends Controller
{
    public $enableCsrfValidation = false;

    public function actions()
    {
        return [
            'index' => [
                'class' => 'izumi\longpoll\LongPollAction',
                'events' => ['newMessage'],
                'callback' => $this->longPollCallback(...),
            ],
        ];
    }

    public function longPollCallback(Server $server)
    {
        $server->responseData = Yii::$app->cache->get('message');
    }
}
