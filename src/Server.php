<?php

namespace izumi\longpoll;

use izumi\longpoll\widgets\LongPoll;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\web\Response;

/**
 * Class implements long polling connection
 * @property EventInterface[]|null $triggeredEvents
 * @property Response $response
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class Server extends Object
{
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
     * @throws InvalidConfigException
     */
    public function run()
    {
        if ($this->_triggeredEvents !== null) {
            throw new InvalidCallException('Poll can be run only once.');
        }

        $events = $this->eventCollection->getEvents();
        if (empty($events)) {
            throw new InvalidConfigException('At least one event should be added to the poll.');
        }

        $lastStates = [];
        foreach ($events as $eventKey => $event) {
            if (isset($this->lastStates[$eventKey])) {
                $lastState = $this->lastStates[$eventKey];
            } else {
                $lastState = Yii::$app->getRequest()->getQueryParam($event->getParamName());
            }
            if ($lastState !== null) {
                $lastState = (int) $lastState;
            }
            $lastStates[$eventKey] = $lastState;
        }

        Yii::$app->getSession()->close();

        $endTime = time() + $this->timeout;
        do {
            $triggered = [];
            foreach ($events as $eventKey => $event) {
                $event->updateState();
                if ($event->getState() !== $lastStates[$eventKey]) {
                    $triggered[] = $event;
                }
            }
            if ($triggered) {
                break;
            }
            usleep($this->sleepTime);
        } while (time() < $endTime);

        $this->_triggeredEvents = $triggered;
    }

    /**
     * Return formatted response.
     * @return Response
     */
    public function getResponse()
    {
        if ($this->_triggeredEvents === null) {
            throw new InvalidCallException('Run poll first');
        }
        $params = (array) $this->responseParams;

        $response = new Response();
        $response->format = Response::FORMAT_JSON;
        $response->data = [
            'data' => $this->responseData,
            'params' => LongPoll::createPollParams($this->eventCollection, $params)
        ];

        return $response;
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
     * @param array $events the events for waiting (any).
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
