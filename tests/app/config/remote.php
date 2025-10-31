<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

$main = require('main.php');
$main['bootstrap'][] = 'log';
$main['components']['log'] = [
    'targets' => [
        [
            'class'=> \yii\log\FileTarget::class,
            'logVars' => [],
            'levels' => ['error', 'warning', 'trace'],
        ],
    ],
];

return $main;
