<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2017 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use izumi\longpoll\EventInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class EventTestCase extends TestCase
{
    public $eventClass;

    /**
     * @param array $config
     * @return EventInterface|object
     */
    public function createEvent($config = [])
    {
        return Yii::createObject(ArrayHelper::merge($config, ['class' => $this->eventClass]));
    }

    public function testWithoutKey()
    {
        $this->expectException(InvalidConfigException::class);
        $this->createEvent();
    }

    public function validKeysProvider()
    {
        return [
            [''],
            ['testKey'],
            ['any Strings supported'],
            [" \n key \t\t"],
            ['98string_'],
        ];
    }

    /**
     * @dataProvider validKeysProvider
     * @param string $key
     */
    public function testValidKey($key)
    {
        $event = $this->createEvent(['key' => $key]);
        $this->assertEquals($key, $event->getKey());
    }

    public function invalidKeysProvider()
    {
        return [
            [123],
            [1.23],
            [['123']],
            [new \stdClass()],
            [true],
        ];
    }

    /**
     * @dataProvider invalidKeysProvider
     * @param $key
     */
    public function testInvalidKey($key)
    {
        $this->expectException(\Exception::class);
        $this->createEvent(['key' => $key]);
    }

    public function testKeyReassign()
    {
        $event = $this->createEvent(['key' => 'testKey']);
        $this->expectException(\Exception::class);
        $event->setKey('testKey2');
    }

    /**
     * @dataProvider validKeysProvider
     * @depends testValidKey
     * @param string $key
     */
    public function testParamName($key)
    {
        $event1 = $this->createEvent(['key' => $key]);
        $this->assertNotEmpty($event1->getParamName());

        $key2 = $key . '2';
        $event2 = $this->createEvent(['key' => $key2]);
        $this->assertNotEmpty($event2->getParamName());
        $this->assertNotEquals($event1->getParamName(), $event2->getParamName());
    }

    /**
     * @depends testValidKey
     */
    public function testGetState()
    {
        $event = $this->createEvent(['key' => 'testEvent']);
        $this->assertInternalType('integer', $event->getState());
    }

    /**
     * @depends testValidKey
     */
    public function testTrigger()
    {
        $event = $this->createEvent(['key' => 'testEvent']);
        $oldState = $event->getState();
        $newState = $event->trigger();
        $this->assertNotNull($newState);
        $this->assertNotEquals($newState, $oldState);
    }

    /**
     * @depends testTrigger
     */
    public function testUpdateState()
    {
        $key = 'testKey';
        $event1 = $this->createEvent(['key' => $key]);
        $event2 = $this->createEvent(['key' => $key]);
        $this->assertEquals($event1->getState(), $event2->getState());

        $event1->trigger();
        $this->assertNotEquals($event1->getState(), $event2->getState());

        $event2->updateState();
        $this->assertEquals($event1->getState(), $event2->getState());
    }
}
