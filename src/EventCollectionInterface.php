<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2017 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\longpoll;

/**
 * Interface EventCollectionInterface
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
interface EventCollectionInterface
{
    /**
     * @param EventInterface|string $event the event object or key
     * @return EventInterface
     */
    public function addEvent($event);

    /**
     * @param EventInterface[]|array|string $events
     */
    public function setEvents($events);

    /**
     * @return EventInterface[] array of events (key => event).
     */
    public function getEvents();
}
