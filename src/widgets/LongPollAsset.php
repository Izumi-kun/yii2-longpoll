<?php

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
