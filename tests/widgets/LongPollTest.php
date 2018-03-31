<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2017 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests\widgets;

use izumi\longpoll\Event;
use izumi\longpoll\EventCollection;
use izumi\longpoll\widgets\LongPoll;
use tests\TestCase;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\AssetManager;
use yii\web\JsExpression;
use yii\web\View;

class LongPollTest extends TestCase
{
    public function testInit()
    {
        $widget = new LongPoll();
        $this->assertInstanceOf($widget->eventCollectionClass, $widget->eventCollection);
    }

    public function testCreatePollParams()
    {
        $event = new Event(['key' => 'test']);
        $collection = new EventCollection(['events' => $event]);
        $params = LongPoll::createPollParams($collection);
        $this->assertArraySubset([
            $event->getParamName() => $event->getState(),
        ], $params);

        $params = LongPoll::createPollParams($collection, ['extraParam' => 'test']);
        $this->assertArraySubset([
            $event->getParamName() => $event->getState(),
            'extraParam' => 'test',
        ], $params);
    }

    public function testCreatePollParamsEmpty()
    {
        $collection = new EventCollection();
        $this->expectException(InvalidConfigException::class);
        LongPoll::createPollParams($collection);
    }

    public function testCreatePollParamsIntersect()
    {
        $event = new Event(['key' => 'test']);
        $collection = new EventCollection(['events' => $event]);
        $this->expectException(InvalidArgumentException::class);
        LongPoll::createPollParams($collection, [$event->getParamName() => 'val']);
    }

    public function testSetEvents()
    {
        $widget = new LongPoll();
        $widget->setEvents(['test1', 'test2']);
        $this->assertInstanceOf($widget->eventCollectionClass, $widget->eventCollection);

        $events = $widget->eventCollection->getEvents();
        $this->assertArrayHasKey('test1', $events);
        $this->assertArrayHasKey('test2', $events);
    }

    /**
     * @depends testCreatePollParams
     * @depends testSetEvents
     */
    public function testCreateJsOptions()
    {
        $event1 = new Event(['key' => 'test1']);
        $event2 = new Event(['key' => '@test2@']);
        $widget = new LongPoll([
            'events' => [$event1, $event2],
            'url' => ['/poll/index', 'p' => 2],
            'callback' => 'console.log',
            'clientOptions' => [
                'pollInterval' => 750,
                'type' => 'POST',
            ],
        ]);
        $jsOptions = $widget->createJsOptions();
        $this->assertArraySubset([
            'pollInterval' => 750,
            'type' => 'POST',
            'url' => '/index.php?r=poll%2Findex&p=2',
            'params' => [
                $event1->getParamName() => $event1->getState(),
                $event2->getParamName() => $event2->getState(),
            ],
            'callback' => new JsExpression('console.log'),
        ], $jsOptions);
    }

    public function testCreateJsOptionsWithoutUrl()
    {
        $widget = new LongPoll(['events' => 'test']);
        $this->expectException(InvalidConfigException::class);
        $widget->createJsOptions();
    }

    /**
     * @depends testCreateJsOptions
     */
    public function testRun()
    {
        FileHelper::createDirectory(Yii::getAlias('@runtime/assets'));
        $view = new View();
        $view->setAssetManager(new AssetManager([
            'basePath' => '@runtime/assets',
            'baseUrl' => '/assets',
        ]));
        $event = new Event(['key' => 'eventId']);
        $widget = LongPoll::widget([
            'id' => 'testId',
            'view' => $view,
            'url' => ['/poll/index'],
            'events' => [$event],
            'callback' => 'console.log',
        ]);
        $this->assertEmpty($widget);
        $jsEnd = ArrayHelper::getValue($view->js, View::POS_END, []);
        $needle = <<<EOD
jQuery.longpoll.register('testId', {"url":"\/index.php?r=poll%2Findex","params":{"{$event->getParamName()}":{$event->getState()}},"callback":console.log});
EOD;
        $this->assertNotFalse(array_search($needle, $jsEnd));

        $jsReady = ArrayHelper::getValue($view->js, View::POS_READY, []);
        $needle = <<<EOD
jQuery.longpoll.get('testId').start();
EOD;
        $this->assertNotFalse(array_search($needle, $jsReady));

        $assetPath = $view->getAssetManager()->getPublishedPath('@npm/jquery-longpoll-client/dist');
        $this->assertFileExists($assetPath . DIRECTORY_SEPARATOR . 'jquery.longpoll.min.js');
    }
}
