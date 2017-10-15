<?php

namespace izumi\tests\longpoll;

use izumi\longpoll\EventCollectionInterface;
use Yii;

class EventCollectionTest extends TestCase
{
    public $eventClass = 'izumi\longpoll\Event';
    public $eventCollectionClass = 'izumi\longpoll\EventCollection';

    public function setUp()
    {
        $this->mockApplication();
    }

    /**
     * @param mixed $events
     * @return EventCollectionInterface|object
     */
    public function createEventCollection($events = null)
    {
        return Yii::createObject([
            'class' => $this->eventCollectionClass,
            'events' => $events,
        ]);
    }

    public function eventsProvider()
    {
        $key1 = 'testEvent1';
        $key2 = 'testEvent2';
        return [
            'single' => [$key1, 1],
            'array single' => [[$key1], 1],
            'array multiple' => [[$key1, $key2], 2],
            'array multiple duplicated' => [[$key1, $key1], 1],
            'config single' => [['class' => $this->eventClass, 'key' => $key1], 2],
            'config multiple' => [[
                ['class' => $this->eventClass, 'key' => $key1],
                ['class' => $this->eventClass, 'key' => $key2],
            ], 2],
            'mixed' => [[
                $key1,
                ['class' => $this->eventClass, 'key' => $key2],
            ], 2],
        ];
    }

    /**
     * @dataProvider eventsProvider
     * @param mixed $events
     * @param int $cnt
     */
    public function testEventCollection($events, $cnt)
    {
        $eventCollection = $this->createEventCollection($events);
        $objects = $eventCollection->getEvents();
        $this->assertThat($objects, $this->countOf($cnt));
        $this->assertThat($objects, $this->containsOnlyInstancesOf('izumi\longpoll\EventInterface'));
        foreach ($objects as $key => $event) {
            $this->assertEquals($key, $event->getKey());
        }
    }
}
