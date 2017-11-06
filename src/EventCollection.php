<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2017 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\longpoll;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;

/**
 * Class EventCollection
 * @property EventInterface[] $events array of events.
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class EventCollection extends Object implements EventCollectionInterface
{
    /**
     * @var EventInterface[] array of events (key => event).
     */
    private $_events;
    /**
     * @var string event class name.
     */
    public $eventClass = 'izumi\longpoll\Event';

    /**
     * @inheritdoc
     */
    public function addEvent($event)
    {
        if (!$event instanceof EventInterface) {
            if (!is_array($event)) {
                $event = [
                    'class' => $this->eventClass,
                    'key' => $event,
                ];
            }
            $event = Yii::createObject($event);
            if (!$event instanceof EventInterface) {
                throw new InvalidConfigException('The event should be an instance of "\izumi\longpoll\EventInterface".');
            }
        }
        $this->_events[$event->getKey()] = $event;

        return $event;
    }

    /**
     * @inheritdoc
     */
    public function getEvents()
    {
        return $this->_events;
    }

    /**
     * @inheritdoc
     */
    public function setEvents($events)
    {
        $this->_events = [];
        if (!is_array($events)) {
            $events = [$events];
        }
        foreach ($events as $event) {
            $this->addEvent($event);
        }
    }
}
