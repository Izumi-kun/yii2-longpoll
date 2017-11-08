<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2017 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\longpoll;

use izumi\longpoll\widgets\LongPoll;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\Json;
use yii\web\Response;

/**
 * Class implements long polling connection.
 *
 * @property EventInterface[]|null $triggeredEvents
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class Server extends Response
{
    /**
     * @var callable
     */
    public $callback;
    /**
     * @var mixed response data
     */
    public $responseData;
    /**
     * @var array query params
     */
    public $responseParams = [];
    /**
     * @var int how long poll will be (in seconds).
     */
    public $timeout = 25;
    /**
     * @var int time between events check (in microseconds).
     */
    public $sleepTime = 250000;
    /**
     * @var EventCollectionInterface events for waiting (any).
     */
    public $eventCollection;
    /**
     * @var string event collection class name.
     */
    public $eventCollectionClass = 'izumi\longpoll\EventCollection';
    /**
     * @var array events (string eventId => int lastState) for waiting (any).
     */
    protected $lastStates = [];
    /**
     * @var EventInterface[]|null
     */
    protected $_triggeredEvents;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->eventCollection instanceof EventCollectionInterface) {
            $this->eventCollection = Yii::createObject([
                'class' => $this->eventCollectionClass,
            ]);
        }
    }

    /**
     * Prepares for sending the response.
     * @throws InvalidConfigException
     */
    protected function prepare()
    {
        $events = $this->eventCollection->getEvents();
        if (empty($events)) {
            throw new InvalidConfigException('At least one event should be added to the poll.');
        }
        foreach ($events as $eventKey => $event) {
            if (!isset($this->lastStates[$eventKey])) {
                $this->lastStates[$eventKey] = (int) Yii::$app->getRequest()->getQueryParam($event->getParamName());
            }
        }

        $this->version = '1.1';
        $this->getHeaders()
            ->set('Transfer-Encoding', 'chunked')
            ->set('Content-Type', 'application/json; charset=UTF-8');

        Yii::$app->getSession()->close();
    }

    /**
     * Sends the response content to the client.
     */
    protected function sendContent()
    {
        $events = $this->eventCollection->getEvents();
        $endTime = time() + $this->timeout;
        $connectionTestTime = time() + 1;
        $this->clearOutputBuffers();
        ignore_user_abort(true);
        do {
            $triggered = [];
            foreach ($events as $eventKey => $event) {
                $event->updateState();
                if ($event->getState() !== $this->lastStates[$eventKey]) {
                    $triggered[] = $event;
                }
            }
            if (!empty($triggered)) {
                break;
            }
            usleep($this->sleepTime);
            if (time() >= $connectionTestTime) {
                echo '0';
                flush();
                if (connection_aborted()) {
                    Yii::trace('Client disconnected', __METHOD__);
                    Yii::$app->end();
                }
                $connectionTestTime++;
            }
        } while (time() < $endTime);

        $this->_triggeredEvents = $triggered;

        if (!empty($triggered) && is_callable($this->callback)) {
            call_user_func($this->callback, $this);
        }

        $params = (array) $this->responseParams;
        $json = Json::encode([
            'data' => $this->responseData,
            'params' => LongPoll::createPollParams($this->eventCollection, $params)
        ]);

        echo dechex(strlen($json)), "\r\n", $json, "\r\n";
        echo "0\r\n\r\n";
    }

    /**
     * @param EventInterface|string $event
     * @param int|null $lastState
     */
    public function addEvent($event, $lastState = null)
    {
        $event = $this->eventCollection->addEvent($event);
        if ($lastState !== null) {
            if (!is_int($lastState)) {
                throw new InvalidParamException('$lastState must be an integer');
            }
            $this->lastStates[$event->getKey()] = $lastState;
        }
    }

    /**
     * @param array|EventInterface[]|string $events the events for waiting (any).
     */
    public function setEvents($events)
    {
        $this->eventCollection = Yii::createObject([
            'class' => $this->eventCollectionClass,
            'events' => $events,
        ]);
    }

    /**
     * @return EventInterface[]|null
     */
    public function getTriggeredEvents()
    {
        return $this->_triggeredEvents;
    }

}
