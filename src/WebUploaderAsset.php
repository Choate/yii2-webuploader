<?php
/**
 * Created by PhpStorm.
 * User: Choate
 * Date: 2018/3/9
 * Time: 10:01
 */

namespace choate\yii2\webuploader;


use yii\web\AssetBundle;

class WebUploaderAsset extends AssetBundle
{
    public $sourcePath = '@bower/fex-webuploader/dist/';

    public $js = [
        'webuploader.nolog.min.js',
    ];
}