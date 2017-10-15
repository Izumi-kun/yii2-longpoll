<?php

namespace izumi\tests\longpoll;

use izumi\longpoll\EventInterface;
use Yii;

class EventTest extends TestCase
{
    public $eventClass = 'izumi\longpoll\Event';

    public function setUp()
    {
        $this->mockApplication();
    }

    /**
     * @param string $key
     * @return EventInterface|object
     */
    public function createEvent($key)
    {
        return Yii::createObject([
            'class' => $this->eventClass,
            'key' => $key,
        ]);
    }

    public function testEvent()
    {
        $key = 'testEvent1';
        $event = $this->createEvent($key);
        $this->assertEquals($key, $event->getKey());
    }

    /**
     * @depends testEvent
     */
    public function testParamName()
    {
        $event1 = $this->createEvent('testParamNameKey1');
        $event2 = $this->createEvent('testParamNameKey2');
        $this->assertNotEmpty($event1->getParamName());
        $this->assertNotEmpty($event2->getParamName());
        $this->assertNotEquals($event1->getParamName(), $event2->getParamName());
    }

    /**
     * @depends testEvent
     */
    public function testTrigger()
    {
        $event = $this->createEvent('testEvent1');
        $oldState = $event->getState();
        $newState = $event->trigger();
        $this->assertNotNull($newState);
        $this->assertNotEquals($newState, $oldState);

        $oldState = $newState;
        $newState = $event->trigger();
        $this->assertNotEquals($newState, $oldState);

        $event->updateState();
        $this->assertEquals($event->getState(), $newState);
    }
}
