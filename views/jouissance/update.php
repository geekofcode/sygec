<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Jouissance */
/* @var $matricule string */

$this->title = 'Modifier ';
$this->params['breadcrumbs'][] = ['label' => 'Jouissances', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->IDNATURE, 'url' => ['view', 'id' => $model->IDNATURE]];
$this->params['breadcrumbs'][] = 'Modifier';
?>
<div class="jouissance-update">

    <?php

    if($model->TYPES == "01" || $model->TYPES == "02" || $model->TYPES == "04") {

       echo $this->render('_form', [
        'model' => $model,
           'matricule' => $matricule,
        ]);

    }

    else if($model->TYPES == "03") {
       echo $this->render('_form1', [
            'model' => $model,
               'matricule' => $matricule,
        ]); }

    else if($model->TYPES == "05") {
       echo $this->render('_form2', [
            'model' => $model,
               'matricule' => $matricule,
        ]); }

    ?>

</div>
