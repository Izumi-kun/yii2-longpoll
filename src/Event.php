<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2017 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\longpoll;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

/**
 * Event uses filesystem for monitoring and triggering events.
 * @property string $key The event key.
 * @property int $state The event state.
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class Event extends BaseObject implements EventInterface
{
    /**
     * @var string prefix of the parameter storing the state of event
     */
    protected $eventParamPrefix = 'event-';
    /**
     * @var string the directory to store state files. You may use path alias here.
     */
    protected $statesPath = '@runtime/events';
    /**
     * @var string event key
     */
    private $_key;
    /**
     * @var string normalized event key
     */
    private $_keyNormalized;
    /**
     * @var string the state file path
     */
    private $_filePath;
    /**
     * @var int current state
     */
    private $_state;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->_key === null) {
            throw new InvalidConfigException("The event key is required.");
        }
        if (ctype_alnum($this->_key) && StringHelper::byteLength($this->_key) <= 32) {
            $this->_keyNormalized = $this->_key;
        } else {
            $this->_keyNormalized = md5($this->_key);
        }
        $this->statesPath = Yii::getAlias($this->statesPath);
        if (!is_dir($this->statesPath)) {
            FileHelper::createDirectory($this->statesPath);
        }
        $this->_filePath = $this->statesPath . DIRECTORY_SEPARATOR . $this->_keyNormalized;
    }

    /**
     * @inheritdoc
     */
    public function setKey($key)
    {
        if ($this->_key !== null) {
            throw new InvalidCallException("The key can't be changed.");
        }
        if (!is_string($key)) {
            throw new InvalidParamException("The event key must be a string.");
        }
        $this->_key = $key;
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @inheritdoc
     */
    public function trigger()
    {
        $filePath = $this->_filePath;
        $tries = 4;
        $lastError = null;
        $state = null;
        set_error_handler(function () use (&$lastError) {
            $lastError = func_get_arg(1);
        });
        while ($tries > 0) {
            $tries--;

            $file = fopen($filePath, 'c+');

            if ($file === false) {
                break;
            }

            if (!flock($file, LOCK_EX | LOCK_NB)) {
                fclose($file);
                usleep(250000);
                continue;
            }

            $state = (int) stream_get_contents($file) ?: 0;
            if ($state >= time() + 1000000) {
                $state = 0;
            }
            $state++;

            ftruncate($file, 0);
            rewind($file);
            fwrite($file, (string) $state);
            flock($file, LOCK_UN);
            fclose($file);

            if (touch($filePath, $state)) {
                $this->_state = $state;
                $lastError = null;
                break;
            }
        }
        restore_error_handler();

        if ($lastError === null) {
            return $state;
        }

        Yii::warning("Unable to trigger event '{$this->_key}': {$lastError}", __METHOD__);

        return null;
    }

    /**
     * Trigger an event given the event key.
     * @param string $key event key
     * @return int|null new state or null on failure
     */
    public static function triggerByKey($key)
    {
        return (new static(['key' => $key]))->trigger();
    }

    /**
     * @inheritdoc
     */
    public function updateState()
    {
        clearstatcache(true, $this->_filePath);

        $this->_state = @filemtime($this->_filePath) ?: 0;
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        if ($this->_state === null) {
            $this->updateState();
        }

        return $this->_state;
    }

    /**
     * @inheritdoc
     */
    public function getParamName()
    {
        return $this->eventParamPrefix . $this->_keyNormalized;
    }
}
