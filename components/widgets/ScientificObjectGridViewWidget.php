<?php
//******************************************************************************
//                      ScientificObjectGridViewWidget.php
// SILEX-PHIS
// Copyright © INRA 2019
// Creation date: 25 Apr. 2019
// Contact: andreas.garcia@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
//******************************************************************************
namespace app\components\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use app\models\yiiModels\ScientificObjectSearch;

/**
 * Scientific object grid view widget.
 * @author Andréas Garcia <andreas.garcia@inra.fr>
 */
class ScientificObjectGridViewWidget extends Widget {
    
    /**
     * Scientific objects to render.
     * @var mixed
     */
    public $dataProvider;
    const DATA_PROVIDER = "dataProvider";
    
    /**
     * Scientific objects search model.
     * @var mixed
     */
    public $searchModel;
    const SEARCH_MODEL = "searchModel";
    
    /**
     * Experiments.
     * @var mixed
     */
    public $experiments;
    const EXPERIMENTS = "experiments";
    
    public $scientificObjectTypes;
    const SCIENTIFIC_OBJECT_TYPES = "scientificObjectTypes";
    
    const HTML_CLASS_NAME = "scientific-object-widget";

    /**
     * Renders the list of the concerned items
     * @return string the HTML string rendered
     */
    public function run() {
        $htmlRendered = GridView::widget([
            'dataProvider' => $this->dataProvider,
            'filterModel' => $this->searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                  'attribute' => ScientificObjectSearch::URI,
                  'format' => 'raw',
                  'filter' => false,
                ],
                ScientificObjectSearch::LABEL,
                [
                    'attribute' => ScientificObjectSearch::RDF_TYPE,
                    'format' => 'raw',
                    'value' => function($model, $key, $index) {
                        return explode("#", $model->rdfType)[1];
                    },
                    'headerOptions' => ['style' => 'min-width:169px'],
                    'filter' => Select2::widget([
                        'attribute' => ScientificObjectSearch::TYPE,
                        'model' => $this->searchModel,
                        'data' => $this->scientificObjectTypes,
                        'options' => [
                            'placeholder' => Yii::t('app', ScientificObjectSearch::RDF_TYPE_SELECT_LABEL)
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]),
                ],
                [
                    'attribute' => ScientificObjectSearch::PROPERTIES,
                    'format' => 'raw',
                    'value' => function($model, $key, $index) {
                        $toReturn = "<ul>";
                        foreach ($model->properties as $property) {
                            if (explode("#", $property->relation)[1] !== ScientificObjectSearch::TYPE) {
                                $toReturn .= "<li>"
                                        . "<b>" . explode("#", $property->relation)[1] . "</b>"
                                        . " : "
                                        . $property->value
                                        . "</li>";
                            }
                        }
                        $toReturn .= "</ul>";
                        return $toReturn;
                    },
                ],
                [
                    'attribute' => ScientificObjectSearch::EXPERIMENT,
                    'format' => 'raw',
                    'value' => function ($model, $key, $index) {
                        return Html::a($model->experiment, ['experiment/view', 'id' => $model->experiment]);
                    },
                    'filter' => Select2::widget([
                            'attribute' => ScientificObjectSearch::EXPERIMENT,
                            'model' => $this->searchModel,
                            'data' => $this->experiments,
                            'options' => [
                                'placeholder' => Yii::t('app', ScientificObjectSearch::EXPERIMENT_SELECT_LABEL)
                            ]
                        ]),
                ]
            ],
            'options' => ['class' => self::HTML_CLASS_NAME]                    
        ]);
        return $htmlRendered;
    }
}
