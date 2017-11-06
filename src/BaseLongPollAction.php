<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2017 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\longpoll;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 * Base class for long poll actions.
 * Please extend this class for creating complex actions.
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class BaseLongPollAction extends Action
{
    /**
     * @var array
     */
    public $events;
    /**
     * @var callable
     */
    public $callback;
    /**
     * @var string
     */
    public $serverClass = 'izumi\longpoll\Server';

    /**
     * @return Response
     * @throws InvalidConfigException
     */
    protected function runInternal()
    {
        if (!is_callable($this->callback)) {
            throw new InvalidConfigException('"' . get_class($this) . '::callback" should be a valid callback.');
        }
        /** @var Server $server */
        $server = Yii::createObject([
            'class' => $this->serverClass,
            'events' => $this->events,
        ]);
        $server->run();
        if ($server->getTriggeredEvents()) {
            call_user_func($this->callback, $server);
        }

        return $server->getResponse();
    }
}
