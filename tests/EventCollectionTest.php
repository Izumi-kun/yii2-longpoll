<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use izumi\longpoll\Event;
use izumi\longpoll\EventCollection;
use izumi\longpoll\EventCollectionInterface;
use izumi\longpoll\EventInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Yii;
use yii\base\InvalidConfigException;

class EventCollectionTest extends TestCase
{
    public const EVENT_CLASS = Event::class;
    public const EVENT_COLLECTION_CLASS = EventCollection::class;

    public function createEventCollection(mixed $events = null): EventCollectionInterface
    {
        $config = ['class' => self::EVENT_COLLECTION_CLASS];
        if ($events !== null) {
            $config['events'] = $events;
        }
        return Yii::createObject($config);
    }

    public static function eventsProvider()
    {
        $key1 = 'testEvent1';
        $key2 = 'testEvent2';
        return [
            'empty' => [[], 0],
            'array single' => [[$key1], 1],
            'array multiple' => [[$key1, $key2], 2],
            'array multiple duplicated' => [[$key1, $key1], 1],
            'config multiple' => [[
                ['class' => static::EVENT_CLASS, 'key' => $key1],
                ['class' => static::EVENT_CLASS, 'key' => $key2],
            ], 2],
            'mixed' => [[
                $key1,
                ['class' => static::EVENT_CLASS, 'key' => $key2],
            ], 2],
        ];
    }

    #[DataProvider('eventsProvider')]
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
