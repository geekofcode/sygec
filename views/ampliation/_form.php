<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Etablissement;

/* @var $this yii\web\View */
/* @var $model app\models\Ampliation */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ampliation-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'VILLE')->dropDownList(ArrayHelper::map(Etablissement::find()->all(),"CODEETS","LIBELLE"),['prompt'=>'choisir']) ?>

    <?= $form->field($model, 'CONTENU')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'TIMBRE')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Enregistrer', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
