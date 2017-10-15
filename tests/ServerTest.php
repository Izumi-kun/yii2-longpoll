<?php

namespace izumi\tests\longpoll;

use izumi\longpoll\Event;
use izumi\longpoll\Server;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class ServerTest extends TestCase
{
    public function setUp()
    {
        $this->mockWebApplication();
    }

    /**
     * @param array $config
     * @return Server
     */
    protected function createServer($config = [])
    {
        $server = new Server(ArrayHelper::merge($config, [
            'timeout' => 2,
        ]));
        $params = [];
        foreach ($server->eventCollection->getEvents() as $event) {
            $params[$event->getParamName()] = (string) $event->getState();
        }
        Yii::$app->getRequest()->setQueryParams($params);
        return $server;
    }

    public function testServer()
    {
        $server = new Server([
            'events' => 'testEvent1',
            'timeout' => 2,
        ]);
        $events = $server->eventCollection->getEvents();
        $this->assertThat($events, $this->countOf(1));
    }

    /**
     * @depends testServer
     */
    public function testRun()
    {
        $event1 = new Event(['key' => 'testEvent1']);
        $server = $this->createServer(['events' => $event1]);
        $this->assertNull($server->getTriggeredEvents());
        $timeStart = time();
        $server->run();
        $timeEnd = time();
        $this->assertGreaterThanOrEqual($server->timeout, $timeEnd - $timeStart);
        $this->assertEmpty($server->getTriggeredEvents());

        $event2 = new Event(['key' => 'testEvent2']);
        $server = $this->createServer(['events' => [$event1, $event2]]);
        Event::triggerByKey($event2->key);
        $server->run();
        $triggeredEvents = $server->getTriggeredEvents();
        $this->assertContains($event2, $triggeredEvents);
        $this->assertNotContains($event1, $triggeredEvents);
    }

    /**
     * @depends testRun
     */
    public function testResponse()
    {
        $event = new Event(['key' => 'testEvent1']);
        $server = $this->createServer(['events' => $event]);
        $state = $event->trigger();
        $server->run();
        $server->responseData = ['testData' => 'testString'];
        $server->responseParams = ['testParam' => 9];
        $response = $server->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::FORMAT_JSON, $response->format);
        $this->assertArraySubset([
            'data' => [
                'testData' => 'testString',
            ],
            'params' => [
                'testParam' => 9,
                $event->getParamName() => $state,
            ],
        ], $response->data, true);
    }
}
