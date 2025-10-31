<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../../../vendor/autoload.php');
require(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@tests', dirname(__DIR__, 2));

\yii\helpers\FileHelper::removeDirectory(Yii::getAlias('@tests/app/runtime/logs'));

$config = require(__DIR__ . '/../config/remote.php');
(new yii\web\Application($config))->run();
