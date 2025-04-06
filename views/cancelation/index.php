<?php

use app\models\Habilitation;
use app\models\Jouissance;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\Roles;
use app\models\Menus;
use yii\jui\DatePicker;
use app\models\Employe;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CancelationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Suspension de jouissance';
$this->params['breadcrumbs'][] = $this->title;

$compte = \app\models\USER::findIdentity(Yii::$app->user->identity->IDUSER); $menu = "M19";

$habilation = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu])->one();
?>
<div class="cancelation-index">

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

           // 'ID',
            [
                'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                'label' => 'Employés',
                'filter' => Html::activeDropDownList($searchModel, 'employe', ArrayHelper::map(\app\models\Employe::find()->orderBy(["NOM"=>SORT_ASC])->all(),"MATRICULE","fullname"),['class'=>'form-control','prompt' => 'Choisir']),
                'attribute' => 'direction',
                'value' => function ($data) {

                    $jouissance = Jouissance::findOne($data->JOUISSANCE);
                    $decision  = \app\models\Decisionconges::findOne($jouissance->IDDECISION);
                    $employe = Employe::find()->where(["MATRICULE"=>$decision->MATICULE])->one();
                    if($employe != null) return $employe->getFullname(); else return  "";

                },
            ],
            'JOUISSANCE'=>[

                'header' => 'Jouissance',
                'attribute' => 'JOUISSANCE',
                'filter' => Html::activeDropDownList($searchModel, 'JOUISSANCE', ArrayHelper::map(\app\models\Jouissance::find()->where(['STATUT'=>['V','R']])->all(),"IDNATURE","NUMERO"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Jouissance::findOne($model->JOUISSANCE);
                    return $tab==null?'Non speicifie':$tab->NUMERO;
                }
            ],
            /*'DEBUT'=>[
                'header' => 'Date de suspension',
                'attribute' => 'DEBUT',
                'value'=>'DEBUT',
                'format' => 'html',
                'filter' => DatePicker::widget([
                    'name'=>'CancelationSearch[DEBUT]',
                    'language' => 'fr',
                    'dateFormat' => 'yyyy-MM-dd'
                ]),
            ],*/
            'DEBUT'=>[
                'header' => 'Date de debut',
                'attribute' => 'DEBUT',
                'value'=>'DEBUT',
                'format' => 'date',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'DEBUT',
                    'convertFormat' => true,
                    'pluginOptions' => [
                        'opens' => 'right',
                        'locale' => [
                            'format' => 'd-m-Y',
                            'cancelLabel' => 'Clear'
                        ]

                    ]
                ]),
            ],
            'PERIODE',
            'STATUT'=>[

                'header' => 'Statut',
                'attribute' => 'STATUT',
                'filter' => Html::activeDropDownList($searchModel, 'STATUT', [0 => 'Non consommé', 1 => 'Consommé'],['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    return $model->STATUT == 0 ?'Non consommé':'Consommé';
                }
            ],
            [
                'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                'label' => 'Direction',
                'filter' => Html::activeDropDownList($searchModel, 'direction', ArrayHelper::map(\app\models\Direction::find()->all(),"ID","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'attribute' => 'direction',
                'value' => function ($data) {
                    $jouissance = Jouissance::findOne($data->JOUISSANCE);
                    $decision  = \app\models\Decisionconges::findOne($jouissance->IDDECISION);
                    $employe = Employe::find()->where(["MATRICULE"=>$decision->MATICULE])->one();
                    if($employe != null) {
                        $d = \app\models\Direction::findOne($employe->DIRECTION);
                        if($d == null) return $d->LIBELLE; else return ""; }else return  "";

                },
            ],
            [
                'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                'label' => 'Département',
                'filter' => Html::activeDropDownList($searchModel, 'departement', ArrayHelper::map(\app\models\Departements::find()->all(),"CODEDPT","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'attribute' => 'departement',
                'value' => function ($data) {

                    $jouissance = Jouissance::findOne($data->JOUISSANCE);
                    $decision  = \app\models\Decisionconges::findOne($jouissance->IDDECISION);

                    $employe = Employe::find()->where(["MATRICULE"=>$decision->MATICULE ])->one();

                    if($employe != null) {

                        $d = \app\models\Departements::findOne($employe->CODEDPT);

                        if($d != null) return $d->LIBELLE; else return "";

                    }
                    else return  "";
                },
            ],
            [
                'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                'label' => 'Service',
                'filter' => Html::activeDropDownList($searchModel, 'service', ArrayHelper::map(\app\models\Service::find()->all(),"ID","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'attribute' => 'service',
                'value' => function ($data) {

                    $jouissance = Jouissance::findOne($data->JOUISSANCE);
                    $decision  = \app\models\Decisionconges::findOne($jouissance->IDDECISION);

                    $employe = Employe::find()->where(["MATRICULE"=>$decision->MATICULE])->one();
                    if($employe != null) {
                        $d = \app\models\Service::findOne($employe->SERVICE);
                        if($d != null) return $d->LIBELLE; else return ""; } else return  "";
                },
            ],
            //'FICHIER',
            //'IDUSER',
            //'DATECREATION',

            ['class' => 'yii\grid\ActionColumn','template'=>$vue],
        ],
    ]); ?>
    </div>
</div>
