<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use Exception;
use izumi\longpoll\Event;
use izumi\longpoll\EventCollection;
use izumi\longpoll\EventCollectionInterface;
use izumi\longpoll\Server;
use izumi\longpoll\widgets\LongPoll;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\Process\Process;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;

class ServerTest extends TestCase
{
    /**
     * @var Process
     */
    private static Process $server;

    /**
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        $documentRoot = Yii::getAlias('@app/web');
        $server = new Process([PHP_BINARY, '-S', '127.0.0.1:8080', '-t', $documentRoot]);
        $server->start();
        self::$server = $server;
        $timeout = 5 + time();
        while (true) {
            usleep(250000);
            if ($server->isRunning()) {
                $test = @file_get_contents(Url::to('test.txt', true));
                if (str_starts_with($test, 'success')) {
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

    public static function tearDownAfterClass(): void
    {
        self::$server->stop();
    }

    protected function changeMessage($text, int $delay)
    {
        $process = new Process([PHP_BINARY, 'tests/yii', 'message/change', "--delay={$delay}", $text]);
        $process->start();
    }

    protected function runLongPoll(float $timeout, $extraParams = [])
    {
        $params = LongPoll::createPollParams(new EventCollection(['events' => ['newMessage']]), $extraParams);
        $url = Url::to(array_merge(['/poll/index'], $params), true);
        $request = (new Client(['transport' => CurlTransport::class]))
            ->createRequest()
            ->setUrl($url)
            ->setOptions([
                CURLOPT_TIMEOUT => $timeout,
            ]);
        try {
            $response = $request->send();
        } catch (\yii\httpclient\Exception) {
            return '';
        }
        if (!$response->getIsOk()) {
            return '';
        }
        $result = $response->getData();
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('params', $result);
        $this->assertEquals('chunked', $response->getHeaders()->get('Transfer-Encoding'));

        return $result;
    }

    public function testSetEvents()
    {
        $server = new Server();
        $collection = $server->eventCollection;
        $this->assertInstanceOf(EventCollectionInterface::class, $collection);
        $this->assertEmpty($collection->getEvents());

        $server->setEvents(['testEvent']);
        $this->assertArrayHasKey('testEvent', $server->eventCollection->getEvents());

        $server = new Server(['events' => ['test2']]);
        $collection = $server->eventCollection;
        $this->assertInstanceOf(EventCollectionInterface::class, $collection);
        $this->assertArrayHasKey('test2', $collection->getEvents());
    }

    #[Depends('testSetEvents')]
    public function testAddEvent()
    {
        $server = new Server();
        $server->addEvent('addTest');
        $this->assertArrayHasKey('addTest', $server->eventCollection->getEvents());

        $server->addEvent('addTest2', 123);
        $this->assertArrayHasKey('addTest2', $server->eventCollection->getEvents());
    }

    public static function sendDataProvider()
    {
        return [
            [2],
            [4],
            [8],
        ];
    }

    #[Depends('testAddEvent')]
    #[DataProvider('sendDataProvider')]
    public function testSend($delay)
    {
        $event = new Event(['key' => 'newMessage']);
        $server = new Server(['timeout' => $delay + 5]);
        $server->addEvent($event, $event->getState());
        $callbackCalled = false;
        $state = 0;
        $server->callback = function (Server $server) use (&$callbackCalled, &$state): void {
            $server->responseData = [
                'key' => 'data',
                'message' => Yii::$app->getCache()->get('message'),
            ];
            $server->responseParams = [
                'param1' => 'test',
            ];
            $callbackCalled = true;
            $state = $server->getTriggeredEvents()['newMessage']->getState();
        };
        $this->changeMessage('hello', $delay);
        $start = time();
        $server->send();
        $this->assertTrue($callbackCalled);

        $waitTime = time() - $start;
        $json = <<<JSON
{"data":{"key":"data","message":"hello"},"params":{"param1":"test","event-newMessage":{$state}}}
JSON;
        $leadingZeros = str_repeat('0', $waitTime);
        $expectedResponse = $leadingZeros . dechex(strlen($json)) . "\r\n" . $json . "\r\n0\r\n\r\n";
        $this->expectOutputString($expectedResponse);
    }

    #[Depends('testAddEvent')]
    public function testSendTimeout()
    {
        $event = new Event(['key' => 'newMessage']);
        $server = new Server(['timeout' => 2]);
        $server->addEvent($event, $event->getState());
        $callbackCalled = false;
        $server->callback = function (Server $server) use (&$callbackCalled): void {
            $server->responseData = 'no';
            $server->responseParams = ['no' => 'no'];
            $callbackCalled = true;
        };
        $start = time();
        $server->send();
        $this->assertFalse($callbackCalled);

        $waitTime = time() - $start;
        $json = <<<JSON
{"data":null,"params":{"event-newMessage":{$event->getState()}}}
JSON;
        $leadingZeros = str_repeat('0', $waitTime);
        $expectedResponse = $leadingZeros . dechex(strlen($json)) . "\r\n" . $json . "\r\n0\r\n\r\n";
        $this->expectOutputString($expectedResponse);
    }

    public function testSendWithoutEvents()
    {
        $server = new Server();
        $this->expectException(InvalidConfigException::class);
        $server->send();
    }

    #[Depends('testSend')]
    public function testChangeMessageRemote()
    {
        $newMessage = Yii::$app->getSecurity()->generateRandomString();
        $this->changeMessage($newMessage, 2);
        $result = $this->runLongPoll(5);
        $this->assertIsArray($result);
        $this->assertEquals($newMessage, $result['data']);
    }

    #[Depends('testSend')]
    public function testConnectionAbort()
    {
        $string = Yii::$app->getSecurity()->generateRandomString();
        $this->runLongPoll(2, ['s' => $string]);
        sleep(2);
        $log = file_get_contents(Yii::getAlias('@runtime/logs/app.log'));
        $this->assertNotFalse(strpos($log, 'Client disconnected'));
    }
}
