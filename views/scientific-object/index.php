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
use app\components\widgets\ScientificObjectGridViewWidget;

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
    
    <?= ScientificObjectGridViewWidget::widget([
            ScientificObjectGridViewWidget::DATA_PROVIDER => $dataProvider,
            ScientificObjectGridViewWidget::SEARCH_MODEL => $searchModel,
            ScientificObjectGridViewWidget::SCIENTIFIC_OBJECT_TYPES => $scientificObjectTypes,
            ScientificObjectGridViewWidget::EXPERIMENTS => $this->params['listExperiments']
        ])
    ?>
</div>