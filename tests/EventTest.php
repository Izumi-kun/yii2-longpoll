<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2017 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use izumi\longpoll\Event;
use Symfony\Component\Process\PhpProcess;
use Yii;
use yii\helpers\FileHelper;

class EventTest extends EventTestCase
{
    public $eventClass = Event::class;

    public function testStaticTriggerByKey()
    {
        $key = 'testEvent';
        $event = $this->createEvent(['key' => $key]);
        $newState = Event::triggerByKey($key);
        $event->updateState();
        $this->assertEquals($newState, $event->getState());
    }

    /**
     * @depends testStaticTriggerByKey
     */
    public function testTriggerWithFileLock()
    {
        Event::triggerByKey('lockTest');
        $filePath = Yii::getAlias('@runtime/events/lockTest');
        $process = new PhpProcess(<<<PHP
<?php
\$fp = fopen('{$filePath}', 'c+');
flock(\$fp, LOCK_EX);
sleep(3);
flock(\$fp, LOCK_UN);
PHP
);
        $process->start();
        sleep(1);
        $result = Event::triggerByKey('lockTest');
        $this->assertNull($result);
        $process->wait();

        $result = Event::triggerByKey('lockTest');
        $this->assertNotNull($result);
    }

    public function testTriggerWithRemovedDir()
    {
        $event = new Event(['key' => 'rmDirTest']);
        FileHelper::removeDirectory(Yii::getAlias('@runtime/events'));
        $result = $event->trigger();
        $this->assertNull($result);
    }

    /**
     * @depends testStaticTriggerByKey
     */
    public function testMaxState()
    {
        Event::triggerByKey('maxStateTest');
        $filePath = Yii::getAlias('@runtime/events/maxStateTest');
        $initState = time() + 1000000 - 2;
        $state = $initState;
        file_put_contents($filePath, (string) $initState);
        for ($i = 4; $i > 0; $i--) {
            $newState = Event::triggerByKey('maxStateTest');
            $this->assertNotEquals($state, $newState);
            $state = $newState;
        }
        $this->assertLessThan($initState, $state);
    }
}
