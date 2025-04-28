<?php

use app\models\Etablissement;
use app\models\Habilitation;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Roles;
use app\models\Menus;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AmpliationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ampliations';
$this->params['breadcrumbs'][] = $this->title;

$compte = \app\models\USER::findIdentity(Yii::$app->user->identity->IDUSER); $menu = "M14";

$habilation = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu])->one();

?>
<div class="ampliation-index">

    <p>
        <?php if($habilation->ACREATE == 1) echo Html::a('Ajouter une ampliation', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php

    $vue = "";

    if ($habilation->AREAD== 1) $vue .= "{view} ";
    if ($habilation->AUPDATE == 1) $vue .= "{update} ";
    if ($habilation->ADELETE == 1) $vue .= "{delete}";

    echo    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'ID',
            'NUMERO' => [
                'header' => 'NUMERO ',
                'attribute' => 'VILLE',
            ],
            'VILLE'=>[
                'header' => 'VILLE',
                'attribute' => 'VILLE',
                'filter' => Html::activeDropDownList($searchModel, 'VILLE', ArrayHelper::map(Etablissement::find()->orderBy(['LIBELLE'=>SORT_ASC])->all(),"CODEETS","LIBELLE"),['class'=>'form-control','prompt' => 'Choisir']),
                'content' => function($model){
                    $tab  = Etablissement::findOne($model->VILLE);
                    return $tab==null?'Inconnu':$tab->LIBELLE;
                }
            ],
            'CONTENU:ntext',
            'TIMBRE',

            ['class' => 'yii\grid\ActionColumn','template'=>$vue],
        ],
    ]); ?>
</div>
