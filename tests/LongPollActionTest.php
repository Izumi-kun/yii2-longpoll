<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use izumi\longpoll\Server;
use Yii;

class LongPollActionTest extends TestCase
{
    public function testRun()
    {
        $result = Yii::$app->runAction('/poll/index');
        $this->assertInstanceOf(Server::class, $result);
    }
}
