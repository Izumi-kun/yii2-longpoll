<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
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
    public string|array $url;
    /**
     * @var EventCollectionInterface
     */
    public EventCollectionInterface $eventCollection;
    /**
     * @var string event collection class name.
     */
    public string $eventCollectionClass = EventCollection::class;
    /**
     * @var array additional options to be passed to JS registerer.
     */
    public array $clientOptions = [];
    /**
     * @var array params will be passed to JS XHR
     */
    public array $requestParams = [];
    /**
     * @var string
     */
    public string $callback;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (!isset($this->eventCollection)) {
            $collection = Yii::createObject([
                'class' => $this->eventCollectionClass,
            ]);
            if (!$collection instanceof EventCollectionInterface) {
                throw new InvalidConfigException('The eventCollectionClass should be a subclass of "\izumi\longpoll\EventCollectionInterface".');
            }
            $this->eventCollection = $collection;
        }
    }

    /**
     * @inheritdoc
     */
    public function run(): void
    {
        $id = $this->getId();
        $options = Json::htmlEncode($this->createJsOptions());
        $view = $this->getView();
        LongPollAsset::register($view);
        $view->registerJs("jQuery.longpoll.register('$id', $options);", View::POS_END);
        $view->registerJs("jQuery.longpoll.get('$id').start();");
    }

    /**
     * @param EventCollectionInterface $eventCollection
     * @param array $params
     * @return array
     * @throws InvalidConfigException
     */
    public static function createPollParams(EventCollectionInterface $eventCollection, array $params = []): array
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
    public function createJsOptions(): array
    {
        if (!isset($this->url)) {
            throw new InvalidConfigException('The "url" property must be set.');
        }
        $options = $this->clientOptions;
        $options['url'] = Url::to($this->url);
        $options['params'] = self::createPollParams($this->eventCollection, $this->requestParams);
        if (isset($this->callback)) {
            $options['callback'] = new JsExpression($this->callback);
        }
        return $options;
    }

    /**
     * @param array $events
     * @throws InvalidConfigException
     */
    public function setEvents(array $events): void
    {
        if (!isset($this->eventCollection)) {
            $collection = Yii::createObject([
                'class' => $this->eventCollectionClass,
            ]);
            if (!$collection instanceof EventCollectionInterface) {
                throw new InvalidConfigException('The eventCollectionClass should be a subclass of "\izumi\longpoll\EventCollectionInterface".');
            }
            $this->eventCollection = $collection;
        }
        $this->eventCollection->setEvents($events);
    }
}
