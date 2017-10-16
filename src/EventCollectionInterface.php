<?php

namespace izumi\longpoll;

/**
 * Interface EventCollectionInterface
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
interface EventCollectionInterface
{
    /**
     * @param EventInterface|array|string $event the event object, config or key
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
