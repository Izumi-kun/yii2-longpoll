<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\longpoll;

/**
 * Interface EventInterface
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
interface EventInterface
{
    /**
     * Updates state of the event
     */
    public function updateState();

    /**
     * Returns current state.
     * @return int current state of event
     * @see updateState()
     */
    public function getState(): int;

    /**
     * Trigger an event.
     * @return int|null new state or null on failure
     */
    public function trigger(): ?int;

    /**
     * Name of the GET-parameter storing the state of event.
     * @return string
     */
    public function getParamName(): string;

    /**
     * Sets the event key.
     * @param string $key The event key.
     */
    public function setKey(string $key): void;

    /**
     * Returns the event key.
     * @return string The event key.
     */
    public function getKey(): string;
}
