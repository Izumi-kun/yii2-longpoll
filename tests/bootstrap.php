<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

// ensure we get report on all possible php errors
error_reporting(-1);

const YII_ENABLE_ERROR_HANDLER = false;
const YII_DEBUG = true;
const YII_ENV = 'test';

$_SERVER['SCRIPT_NAME'] = basename(__FILE__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@tests', __DIR__);

foreach (glob(Yii::getAlias('@tests/app/runtime/*'), GLOB_ONLYDIR) as $dir) {
    \yii\helpers\FileHelper::removeDirectory($dir);
}

$config = require(__DIR__ . '/app/config/phpunit.php');
$app = new \yii\web\Application($config);
