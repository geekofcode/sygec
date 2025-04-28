<?php

use app\models\Habilitation;
use yii\helpers\Html;
use yii\grid\GridView;
use app\widgets\Alert;
use app\models\Employe;
use app\models\Exercice;
use yii\helpers\ArrayHelper;
use app\models\Departements;
use app\models\Roles;
use app\models\Menus;
use yii\jui\DatePicker;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DecisioncongesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Décision de congés';
$this->params['breadcrumbs'][] = $this->title;

$compte = \app\models\USER::findIdentity(Yii::$app->user->identity->IDUSER); $menu = "M4";

$habilation = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu])->one();

?>
<div class="decisionconges-index">

    <?php echo Html::beginForm(['generation'],'post',['id'=>'formexport']); ?>

    <p>
    <?php // if($habilation->ACREATE == 1) echo Html::a('CRÉER UNE DÉCISION DE CONGÉS', ['create'], ['class' => 'btn btn-success']) ?>

        <?php  if($habilation->ACREATE == 1) echo Html::a('GÉNÉRER DES DÉCISIONS DE CONGÉS', ['exercice/decision'], ['class' => 'btn btn-primary']) ?>

        <?php if($habilation->ACREATE == 1)   echo Html::a('IMPORTER DES DÉCISIONS DE CONGÉS', ['decisionconges/importation'], ['class' => 'btn btn-success']) ?>

        <?php if($habilation->ADELETE == 1) echo Html::a('EXPORTER LES DECISIONS', ['export'], ['class' => 'btn btn-primary']) ?>
        <br>

    </p>

<?= Alert::widget() ?>

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
            ['class' => 'yii\grid\CheckboxColumn'],

           // 'ID_DECISION',


            [
                'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                'label' => 'Matricule',
                'filter' => Html::activeTextInput($searchModel,'matricule1',[]),
                'attribute' => 'matricule1',
                'value' => function ($data) {
                    return $data->MATICULE;
                },
            ],
            'MATICULE'=>[

                'header' => 'Employé',
                'attribute' => 'MATICULE',
                'filter' => Html::activeDropDownList($searchModel, 'MATICULE', ArrayHelper::map(Employe::find()->orderBy(["NOM"=>SORT_ASC])->all(),"MATRICULE","fullname"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Employe::findOne($model->MATICULE);
                    return $tab==null?'Non spécifié':$tab->getfullname();
                }
            ],

            'ANNEEEXIGIBLE'=>[
                'header' => 'Exercice',
                'attribute' => 'ANNEEEXIGIBLE',
                'filter' => Html::activeDropDownList($searchModel, 'ANNEEEXIGIBLE', ArrayHelper::map(Exercice::find()->all(),"ANNEEEXIGIBLE","ANNEEEXIGIBLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Exercice::findOne($model->ANNEEEXIGIBLE);
                    return $tab==null?'Non spécifié':$tab->ANNEEEXIGIBLE;
                }
            ],
            'REF_DECISION',

            'DEBUTPLANIF'=>[
                'header' => 'Date de début',
                'attribute' => 'DEBUTPLANIF',
                'value'=>'DEBUTPLANIF',
                'format' => 'date',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'DEBUTPLANIF',
                    'startAttribute' => 'DEBUTPLANIF',
                    'endAttribute' => 'FINPLANIF',
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
            //'DEBUTPERIODE',
            //'FINPERIODE',
            /*'DEBUTPLANIF'=>[
                'header' => 'Début planifié',
                'attribute' => 'DEBUTPLANIF',
                'value'=>'DEBUTPLANIF',
                'format' => 'html',
                'filter' => DatePicker::widget([
                    'name'=>'DecisioncongesSearch[DEBUTPLANIF]',
                    'language' => 'fr',
                    'dateFormat' => 'yyyy-MM-dd'
                ]),
            ],*/

           /* 'FINPLANIF'=>[
                'header' => 'Fin planifiée',
                'attribute' => 'FINPLANIF',
                'value'=>'FINPLANIF',
                'format' => 'date',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'FINPLANIF',
                    'pluginOptions' => [
                        'format' => 'd-m-Y',
                        'autoUpdateInput' => false
                    ]
                ]),
            ],*/
            'FICHIER'=>[
                'header' => 'Edition',
                'attribute' => 'FICHIER',
                'filter' => Html::activeDropDownList($searchModel, 'FICHIER', [0=>"NON",1=>"OUI"],['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    if($model->FICHIER == 0) return "NON";
                    else return "OUI";
                }
            ],
            [
                'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                'label' => 'Direction',
                'filter' => Html::activeDropDownList($searchModel, 'direction', ArrayHelper::map(\app\models\Direction::find()->all(),"ID","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'attribute' => 'direction',
                'value' => function ($data) {
                     $employe = Employe::find()->where(["MATRICULE"=>$data->MATICULE])->one();
                     if($employe != null) {
                         $d = \app\models\Direction::findOne($employe->DIRECTION);
                         if($d != null )return $d->LIBELLE; else return ""; } else return  "";

                },
            ],
            'DEPARTEMENT'=>[

                'header' => 'Département',
                'attribute' => 'DEPARTEMENT',
                'filter' => Html::activeDropDownList($searchModel, 'DEPARTEMENT', ArrayHelper::map(Departements::find()->all(),"CODEDPT","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Departements::findOne($model->DEPARTEMENT);
                    return $tab==null?'Non spécifié':$tab->LIBELLE;
                }
            ],
            [
                'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                'label' => 'Service',
                'filter' => Html::activeDropDownList($searchModel, 'service', ArrayHelper::map(\app\models\Service::find()->all(),"ID","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'attribute' => 'service',
                'value' => function ($data) {

                    $employe = Employe::find()->where(["MATRICULE"=>$data->MATICULE])->one();
                    if($employe != null) {
                        $s = \app\models\Service::findOne($employe->SERVICE);
                        if($s != null) return $s->LIBELLE; else return "";

                    } else return  "";
                },
            ],
            //'DEBUTREELL',
            //'FINREEL',
            /*'STATUT'=>[
                'header' => 'Statut',
                'attribute' => 'STATUT',
                'filter' => Html::activeDropDownList($searchModel, 'STATUT', ["E"=>"Emise","A"=>"Annule","V"=>"Valide","R"=>"Reprise normale","S"=>"Suspendu"],['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    return $model->STATUT == null?'Inconnu':$model->getStatut($model->STATUT);
                }
            ], */
            //'DATEEMIS',
            //'DATEVAL',
            //'DATESUSP',
            //'DATEANN',
            //'DATEREPRISE',
            //'DATECLOTURE',
            //'SITUTATIONFAMILIALE',
            //'MODETRANSPORT',

            ['class' => 'yii\grid\ActionColumn','template'=>$vue],
        ],
    ]);

    echo  Html::endForm();

    ?></div>
</div>
