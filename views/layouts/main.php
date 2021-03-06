<?php
//**********************************************************************************************
//                                       main.php 
//
// SILEX-PHIS
// Copyright © INRA 2017
// Creation date: February 2017
// Contact: morgane.vidal@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
//***********************************************************************************************

/* @var $this \yii\web\View */
/* @var $content string */

use kartik\icons\Icon;
use yii\helpers\Html;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use lavrentiev\widgets\toastr\ToastrAsset;
use kartik\nav\NavX;

require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/web_services.php');

AppAsset::register($this);
ToastrAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="wrap">
    <?php
    //To use the fontawesome glyphicons on the page
    Icon::map($this, Icon::FA);
    
    NavBar::begin([
        'brandLabel' => 'PHIS <i> ' . Yii::$app->params['platform'] . '</i>',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems;
    //Cas d'un utilisateur non connecté (invité)
    if (Yii::$app->session['isGuest'] || Yii::$app->session['isGuest'] === null) {
        $menuItems = [['label' => Yii::t('app', 'Login'), 'url' => ['/site/login']]];
    } else if (Yii::$app->session['isAdmin']) { //Cas d'un admin
        $menuItems[] = ['label' => Yii::t('app', 'Experimental Organization'),
                        'items' => [
                            [
                                'label' => Icon::show('home', ['class' => 'fa-large'], Icon::FA) . " " . Yii::t('app', '{n, plural, =1{Scientific frame} other{Scientific frames}}', ['n' => 2]), 
                                'url' => ['/infrastructure'],
                            ],
                            [
                                'label' => Icon::show('folder-open', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Project} other{Projects}}', ['n' => 2]),
                                'url' => ['/project/index'],
                            ],
                            [
                                'label' => Icon::show('flask', ['class' => 'fa-large'], Icon::FA) . " " . Yii::t('app', '{n, plural, =1{Experiment} other{Experiments}}', ['n' => 2]),
                                'url' => ['/experiment/index']
                            ],
                            [
                                'label' => Icon::show('leaf', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Agronomical Object} other{Agronomical Objects}}', ['n' => 2]),
                                'url' => ['/agronomical-object/index']
                            ],
                            [
                                'label' => Icon::show('eye-open', [], Icon::BSG) . " " . Yii::t('app', 'Variables'), 
                                'url' => ['/variable/index']
                            ],
                            //SILEX:info
                            //Uncomment this code if you want to allow access to annotation index
//                            [
//                                'label' => Icon::show('comment', [], Icon::FA) . " " . Yii::t('app', 'Annotations'),
//                                'url' => ['/annotation/index']
//                            ]
                            //\SILEX:info
                        ]];
        $menuItems[] = ['label' => Yii::t('app', 'Dataset'), 'url' => ['/dataset/create']];
        $menuItems[] = ['label' => Yii::t('app', 'Installation'),
                        'items' => [
                            [
                                'label' => Icon::show('camera', ['class' => 'fa-large'], Icon::FA) . " " . Yii::t('app', '{n, plural, =1{Sensor} other{Sensors}}', ['n' => 2]), 
                                'url' => ['/sensor/index']
                            ],
                            [
                                'label' => Icon::show('blackboard', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Vector} other{Vectors}}', ['n' => 2]), 
                                'url' => ['/vector/index']
                            ],
                            [
                                'label' => Icon::show('screenshot', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Radiometric Target} other{Radiometric Targets}}', ['n' => 2]), 
                                'url' => ['/radiometric-target/index']
                            ],
                            Html::tag('li','',['class' => 'divider']),
                            [
                                'label' => Yii::t('app', 'Acquisition session template'),
                                'items' => [
                                    [
                                        'label' => Icon::show('file-excel-o', [], Icon::FA). " " . Yii::t('app', "UAV"), 
                                        'url' => ['/acquisition-session-metadata-file/generate-uav-metadata-file']
                                    ],
                                    [
                                        'label' => Icon::show('file-excel-o', [], Icon::FA). " " . Yii::t('app', "Phenomobile"), 
                                        'url' => ['/acquisition-session-metadata-file/generate-field-robot-metadata-file']
                                    ],
                                ]
                            ]
                        ]];
        $menuItems[] = ['label' => Yii::t('app', 'Tools'),
                        'items' => [
                                [
                                    'label' => Icon::show('th', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Group} other{Groups}}', ['n' => 2]), 
                                    'url' => ['/group/index']
                                ],
                                [
                                    'label' => Icon::show('user', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Person} other{Persons}}', ['n' => 2]), 
                                    'url' => ['/user/index']
                                ],
                                [
                                    'label' => Icon::show('book', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Document} other{Documents}}', ['n' => 2]), 
                                    'url' => ['/document/index']
                                ],
                                [
                                    'label' => Icon::show('fire', [], Icon::BSG) . " " . Yii::t('app', 'Web API'), 
                                    'url' => WS_PHIS_PATH_DOC
                                ],
                                [
                                    'label' => Icon::show('link', [], Icon::BSG) . " " . Yii::t('app', 'Documentation'), 
                                    'url' => "http://147.100.175.121/phis-docs-community/"
                                ],
                                [
                                    'label' => Icon::show('paperclip', [], Icon::BSG) . " " . Yii::t('app', 'Vocabulary'), 
                                    'url' => ['/site/ontology']
                                ],
                            ]
                        ];
        
        $menuItems[] = [
                            'label' => Icon::show('log-out', [], Icon::BSG) . " " . Yii::t('app', 'Logout'). ' ('. Yii::$app->session['email']. ')', 
                            'url' => ['/site/disconnect']
                        ];
    } else { // Cas d'un utilisateur simple connecté
        $menuItems[] = ['label' => Yii::t('app', 'Experimental Organization'),
                        'items' => [
                            [
                                'label' => Icon::show('home', ['class' => 'fas'], Icon::FA) . " " . Yii::t('app', '{n, plural, =1{Scientific frame} other{Scientific frames}}', ['n' => 2]), 
                                'url' => ['/infrastructure'],
                            ],
                            [
                                'label' => Icon::show('folder-open', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Project} other{Projects}}', ['n' => 2]), 
                                'url' => ['/project/index'],
                            ],
                            [
                                'label' => Icon::show('flask', [], Icon::FA) . " " . Yii::t('app', '{n, plural, =1{Experiment} other{Experiments}}', ['n' => 2]),
                                'url' => ['/experiment/index']
                            ],
                            [
                                'label' => Icon::show('leaf', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Agronomical Object} other{Agronomical Objects}}', ['n' => 2]),
                                'url' => ['/agronomical-object/index']
                            ],
                            [
                                'label' => Icon::show('eye-open', [], Icon::BSG) . " " . Yii::t('app', 'Variables'), 
                                'url' => ['/variable/index']
                            ],
                            //SILEX:info
                            //Uncomment this code if you want to allow access to annotation index
//                            [
//                                'label' => Icon::show('comment', [], Icon::FA) . " " . Yii::t('app', 'Annotations'),
//                                'url' => ['/annotation/index']
//                            ]
                            //\SILEX:info
                        ]];
        $menuItems[] = ['label' => Yii::t('app', 'Installation'),
                        'items' => [
                            [
                                'label' => Icon::show('camera', ['class' => 'fa-large'], Icon::FA) . " " . Yii::t('app', '{n, plural, =1{Sensor} other{Sensors}}', ['n' => 2]), 
                                'url' => ['/sensor/index']
                            ],
                            [
                                'label' => Icon::show('blackboard', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Vector} other{Vectors}}', ['n' => 2]), 
                                'url' => ['/vector/index']
                            ],
                            [
                                'label' => Icon::show('screenshot', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Radiometric Target} other{Radiometric Targets}}', ['n' => 2]), 
                                'url' => ['/radiometric-target/index']
                            ],
                        ]];
        $menuItems[] = ['label' => Yii::t('app', 'Tools'),
                        'items' => [
                                [
                                    'label' => Icon::show('th', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Group} other{Groups}}', ['n' => 2]), 
                                    'url' => ['/group/index']
                                ],
                                [
                                    'label' => Icon::show('user', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Person} other{Persons}}', ['n' => 2]), 
                                    'url' => ['/user/index']
                                ],
                                [
                                    'label' => Icon::show('book', [], Icon::BSG) . " " . Yii::t('app', '{n, plural, =1{Document} other{Documents}}', ['n' => 2]), 
                                    'url' => ['/document/index']
                                ],
                                [
                                    'label' => Icon::show('fire', [], Icon::BSG) . " " . Yii::t('app', 'Web API'), 
                                    'url' => WS_PHIS_PATH_DOC
                                ],
                                [
                                    'label' => Icon::show('link', [], Icon::BSG) . " " . Yii::t('app', 'Documentation'), 
                                    'url' => "http://147.100.175.121/phis-docs-community/"
                                ],
                            ]   
                        ];
        
        $menuItems[] = [
                            'label' => Icon::show('log-out', [], Icon::BSG) . " " . Yii::t('app', 'Logout'). ' ('. Yii::$app->session['email']. ')', 
                            'url' => ['/site/disconnect']
                        ];
    }
    
    echo NavX::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'encodeLabels' => false,
        'items' => $menuItems,
        'activateParents' => true,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        
        <div class="pull-right">
            <?php
                $urlFlag = \config::path()['basePath'] . '/images/icons/flags/';
                $urlLangage = \config::path()['baseIndexPath'] . '?r=site%2Flanguage&flag=';
            ?> 
            <?= Html::a('<img title="english" alt="EN", src="' . $urlFlag . 'drapeau-rond-en.png">', ['site/language', 'language' => 'en'], []) ?>
            <?= Html::a('<img title="french" alt="FR", src="' . $urlFlag . 'drapeau-rond-fr.png">', ['site/language', 'language' => 'fr'], []) ?>    
        </div>
        <div class="clearfix"></div>
        
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>


<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; OpenSILEX - PHIS v.2.6 - 20 September 2018 ; Software is licensed under AGPL-3.0 and data under CC BY-NC-SA 4.0</p>
    </div> 
</footer> 


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
