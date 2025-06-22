<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Telechargement */

$this->title = 'Détails du telechargement';
$this->params['breadcrumbs'][] = ['label' => 'Telechargement de decision', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


?>
<div class="suspension-view">


    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'dateoperation',
            'fichier'=>[

                'label' => 'Fichier zip de décisions',
                'format'=>'raw',
                'value'=>Html::a('Télécharger', $model->showFile()),
            ],
        ],
    ]) ?>

</div>
