<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use izumi\longpoll\EventInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

abstract class EventTestCase extends TestCase
{
    public string $eventClass;

    public function createEvent(array $config = []): EventInterface
    {
        return Yii::createObject(ArrayHelper::merge($config, ['class' => $this->eventClass]));
    }

    public function testWithoutKey()
    {
        $this->expectException(InvalidConfigException::class);
        $this->createEvent();
    }

    public static function validKeysProvider()
    {
        return [
            [''],
            ['testKey'],
            ['any Strings supported'],
            [" \n key \t\t"],
            ['98string_'],
        ];
    }

    #[DataProvider('validKeysProvider')]
    public function testValidKey(string $key)
    {
        $event = $this->createEvent(['key' => $key]);
        $this->assertEquals($key, $event->getKey());
    }

    public function testKeyReassign()
    {
        $event = $this->createEvent(['key' => 'testKey']);
        $this->expectException(\Exception::class);
        $event->setKey('testKey2');
    }

    #[DataProvider('validKeysProvider')]
    #[Depends('testValidKey')]
    public function testParamName($key)
    {
        $event1 = $this->createEvent(['key' => $key]);
        $this->assertNotEmpty($event1->getParamName());

        $key2 = $key . '2';
        $event2 = $this->createEvent(['key' => $key2]);
        $this->assertNotEmpty($event2->getParamName());
        $this->assertNotEquals($event1->getParamName(), $event2->getParamName());
    }

    #[Depends('testValidKey')]
    public function testGetState()
    {
        $event = $this->createEvent(['key' => 'testEvent']);
        $this->assertTrue(is_int($event->getState()));
    }

    #[Depends('testValidKey')]
    public function testTrigger()
    {
        $event = $this->createEvent(['key' => 'testEvent']);
        $oldState = $event->getState();
        $newState = $event->trigger();
        $this->assertNotNull($newState);
        $this->assertNotEquals($newState, $oldState);
    }

    #[Depends('testTrigger')]
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
