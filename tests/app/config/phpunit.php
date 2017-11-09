<?php

$main = require('main.php');
$main['bootstrap'][] = 'log';
$main['components']['log'] = [
    'targets' => [
        [
            'class'=> \yii\log\FileTarget::class,
            'logFile' => '@runtime/phpunit/app.log',
            'logVars' => [],
            'levels' => ['error', 'warning', 'trace'],
            'exportInterval' => 1,
        ],
    ],
];

return $main;
