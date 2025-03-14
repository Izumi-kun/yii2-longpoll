<?php
/**
 * @link https://github.com/Izumi-kun/yii2-longpoll
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\longpoll\widgets;

use yii\web\AssetBundle;

/**
 * Long poll asset
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class LongPollAsset extends AssetBundle
{
    public $sourcePath = '@npm/jquery-longpoll-client/dist';
    public $js = [
        'jquery.longpoll.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
