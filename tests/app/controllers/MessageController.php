<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests\app\controllers;

use izumi\longpoll\Event;
use Yii;
use yii\console\Controller;

class MessageController extends Controller
{
    public int $delay = 1;
    public $defaultAction = 'change';

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'delay',
        ]);
    }

    public function actionChange($text)
    {
        if ($this->delay > 0) {
            sleep($this->delay);
        }
        Yii::$app->getCache()->set('message', $text);
        Event::triggerByKey('newMessage');
    }
}
