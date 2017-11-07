<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2017 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use Exception;
use izumi\longpoll\EventCollection;
use izumi\longpoll\widgets\LongPoll;
use Symfony\Component\Process\Process;
use Yii;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;

class ServerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Process
     */
    private static $server;

    public static function setUpBeforeClass()
    {
        $documentRoot = Yii::getAlias('@app/web');
        $server = new Process(PHP_BINARY . " -S 127.0.0.1:8080 -t \"{$documentRoot}\"");
        $server->start();
        self::$server = $server;
        $timeout = 5 + time();
        while (true) {
            usleep(250000);
            if ($server->isRunning()) {
                $test = @file_get_contents(Url::to('test.txt', true));
                if ($test === "success\n") {
                    break;
                }
            } else {
                throw new Exception($server->getErrorOutput());
            }
            if ($timeout < time()) {
                throw new Exception();
            }
        }
    }

    public static function tearDownAfterClass()
    {
        self::$server->stop();
    }

    protected function getLastRequest()
    {
        $output = explode("\n", trim(self::$server->getErrorOutput()));
        return array_pop($output);
    }

    protected function changeMessage($text, int $delay = 2)
    {
        $text = '"' . escapeshellcmd($text) . '"';
        $cmd = "message/change --delay={$delay} {$text}";
        $process = new Process(PHP_BINARY . " tests/yii $cmd");
        $process->start();
    }

    protected function runLongPoll(float $timeout, $extraParams = [])
    {
        $params = LongPoll::createPollParams(new EventCollection(['events' => 'newMessage']), $extraParams);
        $url = Url::to(array_merge(['/poll/index'], $params), true);
        $request = (new Client(['transport' => CurlTransport::class]))
            ->createRequest()
            ->setUrl($url)
            ->setOptions([
                CURLOPT_TIMEOUT => $timeout,
            ]);
        try {
            $response = $request->send();
        } catch (\yii\httpclient\Exception $e) {
            return '';
        }
        $result = $response->getData();
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('params', $result);
        $this->assertEquals('chunked', $response->getHeaders()->get('Transfer-Encoding'));

        return $result;
    }

    public function testChangeMessage()
    {
        $newMessage = Yii::$app->getSecurity()->generateRandomString();
        $this->changeMessage($newMessage, 2);
        $result = $this->runLongPoll(5);
        $this->assertEquals($newMessage, $result['data']);
    }

    public function testConnectionAbort()
    {
        $string = Yii::$app->getSecurity()->generateRandomString();
        $this->runLongPoll(2, ['s' => $string]);
        sleep(2);
        $this->assertNotFalse(strpos($this->getLastRequest(), $string));
    }
}
