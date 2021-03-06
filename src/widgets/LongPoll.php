<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2017 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\longpoll\widgets;

use izumi\longpoll\EventCollection;
use izumi\longpoll\EventCollectionInterface;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/**
 * Usage:
 *
 * ```php
 * LongPoll::widget([
 *     'url' => ['site/polling'],
 *     'events' => ['eventId'],
 *     'callback' => 'console.log',
 * ]);
 * ```
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class LongPoll extends Widget
{
    public $url;
    /**
     * @var EventCollectionInterface
     */
    public $eventCollection;
    /**
     * @var string event collection class name.
     */
    public $eventCollectionClass = EventCollection::class;
    /**
     * @var array additional options to be passed to JS registerer.
     */
    public $clientOptions;
    /**
     * @var array params will be passed to JS XHR
     */
    public $requestParams = [];
    /**
     * @var string
     */
    public $callback;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->eventCollection instanceof EventCollectionInterface) {
            $this->eventCollection = Yii::createObject([
                'class' => $this->eventCollectionClass,
            ]);
        }
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function run()
    {
        $id = $this->getId();
        $options = Json::htmlEncode($this->createJsOptions());
        $view = $this->getView();
        LongPollAsset::register($view);
        $view->registerJs("jQuery.longpoll.register('$id', $options);", View::POS_END);
        $view->registerJs("jQuery.longpoll.get('$id').start();", View::POS_READY);
    }

    /**
     * @param EventCollectionInterface $eventCollection
     * @param array $params
     * @return array
     * @throws InvalidConfigException
     */
    public static function createPollParams(EventCollectionInterface $eventCollection, $params = [])
    {
        $events = [];
        foreach ($eventCollection->getEvents() as $event) {
            $events[$event->getParamName()] = $event->getState();
        }
        if (empty($events)) {
            throw new InvalidConfigException('At least one event should be added.');
        }
        if (array_intersect_key($events, $params)) {
            throw new InvalidArgumentException('The "params" property contains keys that intersect with events.');
        }

        return $params + $events;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function createJsOptions()
    {
        if (!isset($this->url)) {
            throw new InvalidConfigException('The "url" property must be set.');
        }
        $options = $this->clientOptions;
        $options['url'] = Url::to($this->url);
        $options['params'] = self::createPollParams($this->eventCollection, $this->requestParams);
        if ($this->callback) {
            $options['callback'] = new JsExpression($this->callback);
        }
        return $options;
    }

    /**
     * @param array $events
     * @throws InvalidConfigException
     */
    public function setEvents($events)
    {
        $this->eventCollection = Yii::createObject([
            'class' => $this->eventCollectionClass,
            'events' => $events,
        ]);
    }
}
