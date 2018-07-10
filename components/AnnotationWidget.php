<?php

//******************************************************************************
//                                       AnnotationWidget.php
//
// Author(s): Arnaud Charleroy <arnaud.charleroy@inra.fr>
// PHIS-SILEX version 1.0
// Copyright © - INRA - 2018
// Creation date: 10 july 2018
// Contact: arnaud.charleroy@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
// Last modification date:  10 july 2018
// Subject: A widget used to generate a customizable annotation interface bouton
//******************************************************************************

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;
use Yii;
use rmrevin\yii\fontawesome\FAS;
use app\models\yiiModels\YiiAnnotationModel;

/**
 *  A widget used to generate a customizable annotation interface bouton
 */
class AnnotationWidget extends Widget {

    /**
     * Define the model which will be annoted
     * @var mixed
     */
    public $targets;

    const TARGETS = "targets";

    public function init() {
        parent::init();
        // must be not null
        if ($this->targets === null) {
           throw new \Exception("Targets aren't set");
        }
         // must be an array
        if (!is_array($this->targets)) {
          throw new \Exception("Targets list is not an array");
        }
         // must contains at least one element
        if (empty($this->targets)) {
            throw new \Exception("Targets list is empty");
        }
    }

    /**
     * Render the annotation button
     * @return string the string rendered
     */
    public function run() {

        return Html::a(
                        FAS::icon('comment') . ' ' . Yii::t('app', 'Add annotation'), [
                    'annotation/create',
                    YiiAnnotationModel::TARGETS => $this->targets,
                        ], [
                    'class' => 'btn btn-default',
                        ]
        );
    }

}
