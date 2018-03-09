<?php
/**
 * Created by PhpStorm.
 * User: Choate
 * Date: 2018/3/9
 * Time: 9:51
 */

namespace choate\yii2\webuploader;


use yii\bootstrap\Html;
use yii\bootstrap\InputWidget;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;

class FileInputWidget extends InputWidget
{
    public $auto = false;

    public $accept = null;

    public $serverUrl = null;

    public $fieldName = 'file';

    public $template = 'basis';

    public $thumbWidth = '200px';

    public $thumbHeight = '150px';

    public $defaultThumb;

    public $defaultValue;

    public function init()
    {
        parent::init();
        $this->options['id'] = $this->getInputId();
    }

    public function run()
    {
        $this->registerWebUploaderBootstrap();
        $this->registerClientEvents();

        switch ($this->template) {
            case 'image':
                $template = $this->renderImage();
                break;
            default:
                $template = $this->renderBasis();
        }

        echo $template;
    }

    protected function renderBasis()
    {
        $className = $this->defaultValue ? 'webuploader-upload' : 'webuploader-new';

        $html[] = Html::beginTag('div', ['class' => "webuploader {$className} input-group"]);;

        $html[] = Html::beginTag('div', ['class' => 'form-control uneditable-input span3']);
        $html[] = Html::tag('i', '', ['class' => 'glyphicon glyphicon-file webuploader-exists']);
        $html[] = Html::tag('span', $this->defaultValue, ['class' => 'webuploader-filename']);
        $html[] = Html::endTag('div');


        $html[] = Html::beginTag('a', ['class' => 'input-group-addon btn btn-default ladda-button webuploader-upload', 'data-upload' => 'webuploader', 'data-style' => 'expand-left']);
        $html[] = Html::tag('span', 'Upload', ['class' => 'ladda-label']);
        $html[] = Html::endTag('a');

        $html[] = Html::beginTag('a', ['class' => 'input-group-addon btn btn-default ladda-button webuploader-exists', 'data-remove' => 'webuploader', 'data-style' => 'expand-left']);
        $html[] = Html::tag('span', 'Remove', ['class' => 'ladda-label']);
        $html[] = Html::endTag('a');

        $html[] = Html::tag('div', Html::tag('span', 'Select file', ['class' => 'webuploader-new']) . Html::tag('span', 'Change', ['class' => 'webuploader-exists']), ['id' => $this->getWebUploaderId(), 'class' => 'input-group-addon btn btn-default btn-file']);

        $html[] = $this->getHiddenInput();

        $html[] = Html::endTag('div');

        return implode("\n", $html);
    }

    protected function renderImage()
    {
        $className = 'webuploader-new';
        $defaultImage = '';
        if ($this->defaultThumb) {
            $defaultImage = Html::img($this->defaultThumb);
        }
        if ($this->defaultValue) {
            $className = 'webuploader-upload';
            $defaultImage = Html::img($this->defaultValue);
        }

        $html[] = Html::beginTag('div', ['class' => "webuploader {$className}"]);

        $html[] = Html::tag('div', $defaultImage, ['class' => 'webuploader-preview thumbnail', 'style' => "width: {$this->thumbWidth}; height: {$this->thumbHeight}"]);

        $html[] = Html::beginTag('div');

        $html[] = Html::beginTag('a', ['class' => 'btn btn-default ladda-button webuploader-upload', 'data-upload' => 'webuploader', 'data-style' => 'expand-left']);
        $html[] = Html::tag('span', 'Upload', ['class' => 'ladda-label']);
        $html[] = Html::endTag('a');

        $html[] = Html::beginTag('a', ['class' => 'btn btn-default ladda-button webuploader-exists', 'data-remove' => 'webuploader', 'data-style' => 'expand-left']);
        $html[] = Html::tag('span', 'Remove', ['class' => 'ladda-label']);
        $html[] = Html::endTag('a');

        $html[] = Html::tag('div', Html::tag('span', 'Select file', ['class' => 'webuploader-new']) . Html::tag('span', 'Change', ['class' => 'webuploader-exists']), ['id' => $this->getWebUploaderId(), 'class' => 'btn btn-default btn-file']);

        $html[] = $this->getHiddenInput();

        $html[] = Html::endTag('div');
        $html[] = Html::endTag('div');

        return implode("\n", $html);
    }

    protected function getHiddenInput()
    {
        $options = $this->options;
        $inputId = $this->getInputId();
        $options['id'] = $inputId;

        if ($this->hasModel()) {
            $input = Html::activeHiddenInput($this->model, $this->attribute, $options);
        } else {
            $input = Html::hiddenInput($this->name, $this->value, $options);
        }

        return $input;
    }

    protected function getInputId()
    {
        $options = $this->options;
        if (isset($options['id'])) {
            $inputId = $options['id'];
        } elseif ($this->hasModel()) {
            $inputId = Html::getInputId($this->model, $this->attribute);
        } else {
            $inputId = $this->getId();
        }

        return $inputId;
    }

    protected function getWebUploaderId()
    {
        return 'webuploader-' . $this->getInputId();
    }

    protected function registerWebUploaderBootstrap()
    {
        $view = $this->getView();
        WebUploaderBootstrapAsset::register($view);
        $bundle = $view->getAssetManager()->getBundle(WebUploaderAsset::class, false);
        $webUploaderId = $this->getWebUploaderId();
        $request = Yii::$app->getRequest();
        $options = array_merge_recursive($this->clientOptions, ['formData' => [$request->csrfParam => $request->getCsrfToken()]]);

        $clientOptions = Json::encode([
            'auto' => $this->auto,
            'swf' => "{$bundle->baseUrl}/Uploader.swf",
            'accept' => $this->accept,
            'serverUrl' => Url::toRoute($this->serverUrl),
            'fieldName' => $this->fieldName,
            'options' => $options,
        ]);

        $view->registerJs("$('#{$webUploaderId}').webUploader({$clientOptions});");
    }

    protected function registerClientEvents()
    {
        if (!empty($this->clientEvents)) {
            $id = $this->getWebUploaderId();
            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "jQuery('#$id').on('$event', $handler);";
            }
            $this->getView()->registerJs(implode("\n", $js));
        }
    }
}