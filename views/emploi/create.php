<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Emploi */

$this->title = 'Ajouter un emploi';
$this->params['breadcrumbs'][] = ['label' => 'Emploi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="roles-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
