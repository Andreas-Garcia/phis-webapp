<?php
//******************************************************************************
//                                 index.php 
// SILEX-PHIS
// Copyright © INRA 2017
// Creation date: Oct. 2017
// Contact: morgane.vidal@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
//******************************************************************************
use kartik\icons\Icon;
use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use app\models\yiiModels\ScientificObjectSearch;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ScientificObjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '{n, plural, =1{Scientific Object} other{Scientific Objects}}', ['n' => 2]);
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="scientific-object-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('yii', 'Create'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(
                Icon::show('download-alt', [], Icon::BSG) . " " . Yii::t('yii', 'Download Search Result'), 
                ['download-csv', 'model' => $searchModel], 
                ['class' => 'btn btn-primary']) ?>
    </p>
    
   <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
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
                    'model' => $searchModel,
                    'data' => $scientificObjectTypes,
                    'options' => [
                        'placeholder' => Yii::t('app', ScientificObjectSearch::RDF_TYPE_SELECT_LABEL)
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            [
                'attribute' => 'properties',
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
                        'model' => $searchModel,
                        'data' => $this->params['listExperiments'],
                        'options' => [
                            'placeholder' => Yii::t('app', ScientificObjectSearch::EXPERIMENT_SELECT_LABEL)
                        ]
                    ]),
            ]
        ],
    ]); ?>
</div>