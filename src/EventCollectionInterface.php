<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
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
     * @param EventInterface|string|array $event the event object or key
     * @return EventInterface
     */
    public function addEvent(EventInterface|string|array $event): EventInterface;

    /**
     * @param array|EventInterface[] $events
     */
    public function setEvents(array $events): void;

    /**
     * @return EventInterface[] array of events (key => event).
     */
    public function getEvents(): array;
}
