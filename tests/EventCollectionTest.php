<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2017 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use izumi\longpoll\Event;
use izumi\longpoll\EventCollection;
use izumi\longpoll\EventCollectionInterface;
use izumi\longpoll\EventInterface;
use Yii;
use yii\base\InvalidConfigException;

class EventCollectionTest extends TestCase
{
    public $eventClass = Event::class;
    public $eventCollectionClass = EventCollection::class;

    /**
     * @param mixed $events
     * @return EventCollectionInterface|object
     */
    public function createEventCollection($events = null)
    {
        $config = ['class' => $this->eventCollectionClass];
        if ($events !== null) {
            $config['events'] = $events;
        }
        return Yii::createObject($config);
    }

    public function eventsProvider()
    {
        $key1 = 'testEvent1';
        $key2 = 'testEvent2';
        return [
            'null' => [null, 0],
            'empty' => [[], 0],
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
        $this->assertCount($cnt, $objects);
        $this->assertContainsOnlyInstancesOf(EventInterface::class, $objects);
        foreach ($objects as $key => $event) {
            $this->assertEquals($key, $event->getKey());
        }
    }

    public function testInvalidEvents()
    {
        $this->expectException(InvalidConfigException::class);
        $this->createEventCollection([['class' => \stdClass::class, 'key' => 'test']]);
    }
}
