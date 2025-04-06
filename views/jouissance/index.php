<?php

use app\models\Habilitation;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Employe;
use app\models\Exercice;
use yii\helpers\ArrayHelper;
use app\models\Decisionconges;
use app\models\Roles;
use app\models\Menus;

/* @var $this yii\web\View */
/* @var $searchModel app\models\JouissanceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Jouissance de congés';
$this->params['breadcrumbs'][] = $this->title;

$setting = \app\models\Parametre::findOne(1);

$compte = \app\models\USER::findIdentity(Yii::$app->user->identity->IDUSER); $menu = "M9";

$habilation = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu])->one();

$exo = Exercice::find()->where(["STATUT"=>"O"])->orderBy(["ANNEEEXIGIBLE"=>SORT_DESC])->one();

if($exo == null) $ex = date("Y"); else $ex = $exo->ANNEEEXIGIBLE;

?>
<div class="jouissance-index">


    <p>
        <?php if($habilation->ACREATE == 1) echo Html::a('Créer une Jouissance', ['create'], ['class' => 'btn btn-success']) ?>
        <?php   if($habilation->ACREATE == 1)  { if($setting->NONJOUISSANCE == 1) echo Html::a('Créer une non jouissance', ['create1'], ['class' => 'btn btn-danger']); }   ?>
        <?php //if($habilation->ACREATE == 1)  echo Html::a('Créer un report de congé', ['create2'], ['class' => 'btn btn-primary']) ?>
        <?php //if($habilation->ACREATE == 1)  echo Html::a('Créer une récuperation de jouissance', ['create3'], ['class' => 'btn btn-info']) ?>
        <?php if($habilation->ADELETE == 1) echo Html::a('Exporter les jouissances', ['export'], ['class' => 'btn btn-primary']) ?>
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

            //'IDNATURE',

            [

                'header' => 'Employé',
                'attribute' => 'employe',
                'filter' => Html::activeDropDownList($searchModel, 'employe', ArrayHelper::map(Employe::find()->orderBy(["NOM"=>SORT_ASC])->all(),"MATRICULE","fullname"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $decision = Decisionconges::findOne($model->IDDECISION);
                    $tab  = Employe::findOne($decision->MATICULE);
                    return $tab==null?'Non spécifié':$tab->getfullname();
                }
            ],
            'TITRE',
            'NUMERO',
            //'PERIODE',
            //'MESSAGE:ntext',
           // 'LIEU',
            //'JOUR',
            'TYPES'=>[

                'header' => 'Types de jouissance',
                'attribute' => 'TYPES',
                'filter' => Html::activeDropDownList($searchModel, 'TYPES', ["01"=>"JOUISSANCE DE CONGES","02"=>"JOUISSANCE PARTIELLE","03"=>"NON JOUISSANCE","04"=>"RELIQUAT CONGES"],['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){

                    if($model->TYPES == "01") return "JOUISSANCE TOTALE";
                    
                    else if($model->TYPES == "02") return "JOUISSANCE PARTIELLE";

                    else if($model->TYPES == "03") return "NON JOUISSANCE";

                    else if($model->TYPES == "04") return "RELIQUAT DE CONGES";

                    else if($model->TYPES == "05") return "REPORT DE CONGES";

                  //  else if($model->TYPES == "06") return "RECUPERATION DE JOUISSANCE";

                }
            ],
            'PLATEFORME'=>[
                'header' => 'Plateforme',
                'attribute' => 'PLATEFORME',
                'filter' => Html::activeDropDownList($searchModel, 'PLATEFORME', ArrayHelper::map(\app\models\Etablissement::find()->all(),"CODEETS","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = \app\models\Etablissement::findOne($model->PLATEFORME);
                    return $tab==null?'Non spécifié':$tab->LIBELLE;
                }
            ],
            'EXERCICE'=>[
                'header' => 'Exercice',
                'attribute' => 'EXERCICE',
                'filter' => Html::activeDropDownList($searchModel, 'EXERCICE', ArrayHelper::map(Exercice::find()->all(),"ANNEEEXIGIBLE","ANNEEEXIGIBLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Exercice::findOne($model->EXERCICE);
                    return $tab==null?'Non spécifié':$tab->ANNEEEXIGIBLE;
                }
            ],
            'IDDECISION'=>[

                'header' => 'Décision de congés',
                'attribute' => 'IDDECISION',
                'filter' => Html::activeDropDownList($searchModel, 'IDDECISION', ArrayHelper::map(Decisionconges::find()->all(),"ID_DECISION","REF_DECISION"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Decisionconges::findOne($model->IDDECISION);
                    return $tab==null?'Inconnu':$tab->REF_DECISION;
                }
            ],
            'STATUT'=>[
                'header' => 'Statut',
                'attribute' => 'STATUT',
                'filter' => Html::activeDropDownList($searchModel, 'STATUT', ["B"=>"Brouillon","A"=>"Annulée","V"=>"Validée","R"=>"Reportée","S"=>"Suspendue"],['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    return $model->STATUT == null?'Inconnu':$model->getStatut($model->STATUT);
                }
            ],
            [
                'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                'label' => 'Direction',
                'attribute' => 'direction',
                'filter' => Html::activeDropDownList($searchModel, 'direction', ArrayHelper::map(\app\models\Direction::find()->all(),"ID","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),

                'value' => function ($data) {
                    $decision = Decisionconges::findOne($data->IDDECISION);
                    $employe = Employe::find()->where(["MATRICULE"=>$decision->MATICULE])->one();
                    if($employe != null) {
                        $d = \app\models\Direction::findOne($employe->DIRECTION);
                        if($d!= null) return $d->LIBELLE; else return "";

                    }

                    else return  "";

                },
            ],
            [
                'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                'label' => 'Département',
                'filter' => Html::activeDropDownList($searchModel, 'departement', ArrayHelper::map(\app\models\Departements::find()->all(),"CODEDPT","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'attribute' => 'departement',
                'value' => function ($data) {

                    $decision = Decisionconges::findOne($data->IDDECISION);
                    $employe = Employe::find()->where(["MATRICULE"=>$decision->MATICULE])->one();

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
                'attribute' => 'service',
                'filter' => Html::activeDropDownList($searchModel, 'service', ArrayHelper::map(\app\models\Service::find()->all(),"ID","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'value' => function ($data) {
                    $decision = Decisionconges::findOne($data->IDDECISION);
                    $employe = Employe::find()->where(["MATRICULE"=>$decision->MATICULE])->one();
                    if($employe != null) {

                        $d = \app\models\Service::findOne($employe->SERVICE);

                        if($d != null) return $d->LIBELLE; else return ""; }

                    else return  "";

                },
            ],
            //'DATECREATION',
            //'USERCREATE',


            ['class' => 'yii\grid\ActionColumn','template'=>$vue],
        ],
    ]); ?>

    </div>

</div>
