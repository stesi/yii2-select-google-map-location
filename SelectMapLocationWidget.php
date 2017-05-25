<?php

namespace kalyabin\maplocation;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

/**
 * Widget for select map location. It\'s render google map and input field for type a map location.
 * Latitude and longitude are provided in the attributes $attributeLatitude and $attributeLongitude.
 * Base usage:
 *
 * $form->field($model, 'location')->widget(\app\widgets\SelectMapLocationWidget::className(), [
 *     'attributeLatitude' => 'latitude',
 *     'attributeLongitude' => 'longitude',
 * ]);
 *
 * or
 *
 * \app\widgets\SelectMapLocationWidget::widget([
 *     'model' => $model,
 *     'attribute' => 'location',
 *     'attributeLatitude' => 'latitude',
 *     'attributeLongitude' => 'longitude',
 * ]);
 *
 * @author Max Kalyabin <maksim@kalyabin.ru>
 * @package yii2-select-google-map-location
 * @copyright (c) 2015, Max Kalyabin, http://github.com/kalyabin
 *
 * @property Model $model base yii2 model or ActiveRecord object
 * @property string $attribute attribute to write map location
 * @property string $attributeLatitude attribute to write location latitude
 * @property string $attributeLongitude attribute to write location longitude
 * @property callable|null $renderWidgetMap custom function to render map
 */
class SelectMapLocationWidget extends InputWidget
{
    public $addressId;
    /**
     * @var string latitude attribute name
     */
    public $attributeLatitude;
    public $attributeLatitudeId;
    /**
     * @var string longitude attribute name
     */
    public $attributeLongitude;
    public $attributeLongitudeId;
    /**
     * @var boolean marker draggable option
     */
    public $draggable = false;

    /**
     * @var array options for map wrapper div
     */
    public $wrapperOptions;

    /**
     * @var array options for attribute text input
     */
    public $textOptions = ['class' => 'form-control'];

    /**
     * @var array JavaScript options
     */
    public $jsOptions = [];

    /**
     * @var callable function for custom map render
     */
    public $renderWidgetMap;

    /**
     * @var string Google API Key for Google Maps
     */
    public $googleMapApiKey;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->id = uniqid('map_');
        $this->attributeLatitudeId = uniqid(Html::getInputId($this->model, $this->attributeLatitude));
        $this->attributeLongitudeId = uniqid(Html::getInputId($this->model, $this->attributeLongitude));
        $this->addressId = Html::getInputId($this->model, $this->attribute);

    }

    /**
     * Run widget
     */
    public function run()
    {
        parent::run();

        if (!isset($this->wrapperOptions)) {
            $this->wrapperOptions = [];
        }
        if (!isset($this->wrapperOptions['id'])) {
            $this->wrapperOptions['id'] = $this->id;
        }
        if (!isset($this->wrapperOptions['style'])) {
            $this->wrapperOptions['style'] = 'width: 100%; height: 500px;';
        }
        /*   SelectMapLocationAssets::$googleMapApiKey = $this->googleMapApiKey;
           SelectMapLocationAssets::register($this->view);
   */
        // getting inputs ids


        $jsOptions = ArrayHelper::merge($this->jsOptions, [
            'address' => '#' . $this->addressId,
            'latitude' => '#' . $this->attributeLatitudeId,
            'longitude' => '#' . $this->attributeLongitudeId,
            'draggable' => $this->draggable,
        ]);
        // message about not founded addess
        if (!isset($jsOptions['addressNotFound'])) {
            $hasMainCategory = isset(Yii::$app->i18n->translations['*']) || isset(Yii::$app->i18n->translations['main']);
            $jsOptions['addressNotFound'] = $hasMainCategory ? Yii::t('main', 'Address not found') : 'Address not found';
        }
        $this->view->registerJs(new JsExpression('
            
           setTimeout(function(){
                $(\'#' . $this->wrapperOptions['id'] . '\').selectLocation(' . Json::encode($jsOptions) . ');},1000);
           
        '));
        $mapHtml = Html::tag('div', '', $this->wrapperOptions);
        $mapHtml .= Html::activeHiddenInput($this->model, $this->attributeLatitude, ['id' => $this->attributeLatitudeId,'name'=>null]);
        $mapHtml .= Html::activeHiddenInput($this->model, $this->attributeLongitude, ['id' => $this->attributeLongitudeId,'name'=>null]);

        if (is_callable($this->renderWidgetMap)) {
            return call_user_func_array($this->renderWidgetMap, [$mapHtml]);
        }

        return Html::activeInput('text', $this->model, $this->attribute, $this->textOptions) . $mapHtml;
    }
}
