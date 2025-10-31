<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

$config = [
    'id' => 'test-app',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(__DIR__, 3) . '/vendor',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'message' => [
            'class' => \tests\app\controllers\MessageController::class,
        ],
        'poll' => [
            'class' => \tests\app\controllers\PollController::class,
        ],
    ],
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'urlManager' => [
            'class' => \yii\web\UrlManager::class,
            'hostInfo' => 'http://127.0.0.1:8080',
            'scriptUrl' => '/index.php',
        ],
    ],
];

return $config;
