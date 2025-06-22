<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TelechargementSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Telechargemens de décisions';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="suspension-index">

    <?php

    $vue = "{view}";

    echo  GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'dateoperation',
            'fichier',
            ['class' => 'yii\grid\ActionColumn','template'=>$vue],
        ],
    ]); ?>
</div>
