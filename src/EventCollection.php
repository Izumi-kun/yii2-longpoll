<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\longpoll;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Class EventCollection
 * @property EventInterface[] $events array of events.
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class EventCollection extends BaseObject implements EventCollectionInterface
{
    /**
     * @var EventInterface[] array of events (key => event).
     */
    private array $_events = [];
    /**
     * @var string event class name.
     */
    public string $eventClass = Event::class;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function addEvent(EventInterface|string|array $event): EventInterface
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
    public function getEvents(): array
    {
        return $this->_events;
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function setEvents(array $events): void
    {
        $this->_events = [];
        foreach ($events as $event) {
            $this->addEvent($event);
        }
    }
}
