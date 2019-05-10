<?php
//******************************************************************************
//                         _actions-on-selection.php 
// PHIS-SILEX
// Copyright © INRA 2017
// Creation date: May 2019
// Contact: andreas.garcia@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
//******************************************************************************

use  app\components\widgets\EventButtonWidget;
use app\components\widgets\AnnotationButtonWidget;

/** 
 * Selection count view.
 */
?>
<div id="<?= $selectionCountAlertId ?>" class="alert alert-info alert-dismissible">
    <span id="<?= $selectionCountValueId ?>"></span> <?= Yii::t("app", "selected"); ?>
    <?= EventButtonWidget::widget([
            EventButtonWidget::AS_LINK => true,
            'id' => $selectionEventButtonId
        ]); ?>
    <?= AnnotationButtonWidget::widget([
            AnnotationButtonWidget::AS_LINK => true
        ]); ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
