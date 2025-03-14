<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\longpoll;

use Yii;
use yii\base\Action;
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
    public array $events;
    /**
     * @var callable
     */
    public $callback;
    /**
     * @var string
     */
    public string $serverClass = Server::class;

    /**
     * @return Response
     */
    protected function runInternal(): Response
    {
        /** @var Server $server */
        $server = Yii::createObject([
            'class' => $this->serverClass,
            'events' => $this->events,
            'callback' => $this->callback,
        ]);

        return $server;
    }
}
