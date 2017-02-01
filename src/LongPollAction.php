<?php

namespace izumi\longpoll;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class LongPollAction extends Action
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
    public function run()
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
