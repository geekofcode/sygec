<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Emploi */

$this->title = 'Modifier l\'emploi';
$this->params['breadcrumbs'][] = ['label' => 'Roles', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->CODEEMP, 'url' => ['view', 'id' => $model->CODEEMP]];
$this->params['breadcrumbs'][] = 'Modifier';
?>
<div class="roles-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
