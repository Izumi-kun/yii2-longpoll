<?php

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
