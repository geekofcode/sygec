<?php

use app\models\Habilitation;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\Categorie;
use app\models\Echellon;
use app\models\Emploi;
use app\models\Etablissement;
use app\models\Civilite;
use app\models\Contrat;
use app\models\Roles;
use app\models\Menus;
use yii\jui\DatePicker;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\EmployeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Employés';
$this->params['breadcrumbs'][] = $this->title;

$compte = \app\models\USER::findIdentity(Yii::$app->user->identity->IDUSER); $menu = "M3";

$habilation = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu])->one();

?>
<div class="employe-index">

    <p>
        <?php if($habilation->ACREATE == 1) echo Html::a('Ajouter un employé', ['create'], ['class' => 'btn btn-success']) ?>
       &nbsp;&nbsp;
        <?php if($habilation->ACREATE == 1) echo Html::a('Mis à jour des employés', ['importation'], ['class' => 'btn btn-primary']) ?>
    </p>

    <div style="overflow: auto;overflow-y: hidden; Height:?">
    <?php

    $vue = "";

    if ($habilation->AREAD== 1) $vue .= "{view} ";
    if ($habilation->AUPDATE == 1) $vue .= "{update} ";
    if ($habilation->ADELETE == 1) $vue .= "{delete}";

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'DATECALCUL'=>[
                'header' => 'Prochain congé',
                'attribute' => 'DATECALCUL',
                'value'=>'DATECALCUL',
                'format' => 'date',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'DATECALCUL',
                    'convertFormat' => true,
                    'pluginOptions' => [
                        'opens' => 'right',
                        'locale' => [
                            'format' => 'Y-m-d',
                            'cancelLabel' => 'Clear'
                        ]

                    ]
                ]),
            ],
            'MATRICULE',
            'CODECAT'=>[
                'header' => 'Categorie',
                'attribute' => 'CODECAT',
                'filter' => Html::activeDropDownList($searchModel, 'CODECAT', ArrayHelper::map(Categorie::find()->orderBy(['LIBELLE'=>SORT_ASC])->all(),"CODECAT","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Categorie::findOne($model->CODECAT);
                    return $tab==null?'Inconnu':$tab->LIBELLE;
                }
            ],
            'CODEECH'=>[
                'header' => 'Echelon',
                'attribute' => 'CODEECH',
                'filter' => Html::activeDropDownList($searchModel, 'CODEECH', ArrayHelper::map(Echellon::find()->orderBy(['LIBELLE'=>SORT_ASC])->all(),"CODEECH","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Echellon::findOne($model->CODEECH);
                    return $tab==null?'Inconnu':$tab->LIBELLE;
                }
            ],
           /* 'CODEEMP'=>[
                'header' => 'Poste',
                'attribute' => 'CODEEMP',
                'filter' => Html::activeDropDownList($searchModel, 'CODEEMP', ArrayHelper::map(Emploi::find()->orderBy(['LIBELLE'=>SORT_ASC])->all(),"CODEEMP","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Emploi::findOne($model->CODEEMP);
                    return $tab==null?'Inconnu':$tab->LIBELLE;
                }
            ],*/
            'CODEETS'=>[
                'header' => 'Lieu d\'embauche',
                'attribute' => 'CODEETS',
                'filter' => Html::activeDropDownList($searchModel, 'CODEETS', ArrayHelper::map(Etablissement::find()->orderBy(['LIBELLE'=>SORT_ASC])->all(),"CODEETS","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Etablissement::findOne($model->CODEETS);
                    return $tab==null?'Inconnu':$tab->LIBELLE;
                }
            ],
            'CODECIV'=>[
                'header' => 'Civilité',
                'attribute' => 'CODECIV',
                'filter' => Html::activeDropDownList($searchModel, 'CODECIV', ArrayHelper::map(Civilite::find()->orderBy(['LIBELLE'=>SORT_ASC])->all(),"CODECIV","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Civilite::findOne($model->CODECIV);
                    return $tab==null?'Inconnu':$tab->LIBELLE;
                }
            ],
            'CODECONT'=>[
                'header' => 'Contrat',
                'attribute' => 'CODECONT',
                'filter' => Html::activeDropDownList($searchModel, 'CODECONT', ArrayHelper::map(Contrat::find()->orderBy(['LIBELLE'=>SORT_ASC])->all(),"CODECONT","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Contrat::findOne($model->CODECONT);
                    return $tab==null?'Inconnu':$tab->LIBELLE;
                }
            ],
            //'CODEETS_EMB',
            'NOM',
            'PRENOM',
            //'DATEEMBAUCHE',
            //'SOLDECREDIT',
            //'SOLDEAVANCE',
            'DIRECTION'=>[
                'header' => 'Direction',
                'attribute' => 'DIRECTION',
                'filter' => Html::activeDropDownList($searchModel, 'DIRECTION', ArrayHelper::map(\app\models\Direction::find()->orderBy(['LIBELLE'=>SORT_ASC])->all(),"ID","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = \app\models\Direction::findOne($model->DIRECTION);
                    return $tab==null?'Inconnu':$tab->LIBELLE;
                }
            ],
            'CODEDPT'=>[
                'header' => 'Département',
                'attribute' => 'CODEDPT',
                'filter' => Html::activeDropDownList($searchModel, 'CODEDPT', ArrayHelper::map(\app\models\Departements::find()->orderBy(['LIBELLE'=>SORT_ASC])->all(),"CODEDPT","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = \app\models\Departements::findOne($model->CODEDPT);
                    return $tab==null?'Inconnu':$tab->LIBELLE;
                }
            ],
            'SERVICE'=>[
                'header' => 'Service',
                'attribute' => 'SERVICE',
                'filter' => Html::activeDropDownList($searchModel, 'SERVICE', ArrayHelper::map(\app\models\Service::find()->orderBy(['LIBELLE'=>SORT_ASC])->all(),"ID","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = \app\models\Service::findOne($model->SERVICE);
                    return $tab==null?'Inconnu':$tab->LIBELLE;
                }
            ],
            /*'DATECALCUL'=>[
                    'header' => 'Prochain congé',
                    'attribute' => 'DATECALCUL',
                    'value'=>'DATECALCUL',
                    'format' => 'html',
                    'filter' => DatePicker::widget([
                            'name'=>'EmployeSearch[DATECALCUL]',
                            'language' => 'fr',
                            'dateFormat' => 'yyyy-MM-dd'
                    ]),
                ],*/

            'DEPLACE'=>[
                'header' => 'Déplacé',
                'attribute' => 'DEPLACE',
                'filter' => Html::activeDropDownList($searchModel, 'DEPLACE',[1=>'OUI',0=>'NON'],['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                   if($model->DEPLACE == 0) return "NON";
                   else return "OUI";
                }
            ],
            'STATUT'=>[
                'header' => 'Statut',
                'attribute' => 'STATUT',
                'filter' => Html::activeDropDownList($searchModel, 'STATUT',[1=>'ACTIF',0=>'INACTIF'],['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                   if($model->STATUT == 0) return "INACTIF";
                   else return "ACTIF";
                }
            ],

            ['class' => 'yii\grid\ActionColumn','template'=>$vue],
        ],
    ]); ?>

    </div>
</div>
