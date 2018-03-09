<?php
/**
 * Created by PhpStorm.
 * User: Choate
 * Date: 2018/3/9
 * Time: 10:03
 */

namespace choate\yii2\webuploader;


use yii\web\AssetBundle;

class WebUploaderBootstrapAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/assets';

    public $js = [
        'js/webuploader-bootstrap.js'
    ];

    public $css = [
        'css/webuploader-bootstrap.css'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'choate\yii2\webuploader\WebUploaderAsset',
        'choate\yii2\laddabootstrap\LaddaBootstrapAsset',
    ];
}