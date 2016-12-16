<?php

namespace izumi\longpoll\widgets;

use yii\web\AssetBundle;

/**
 * Long poll asset
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class LongPollAsset extends AssetBundle
{
    public $sourcePath = '@vendor/izumi-kun/yii2-longpoll/src/assets';
    public $js = [
        'longpoll.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
