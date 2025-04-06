<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\Parametre */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="parametre-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php //$form->field($model, 'DELAIEMISSION')->textInput() ?>

    <?= $form->field($model, 'SUFFIXEREF')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'TIMBREJOUISSANCE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'DUREECONGES')->textInput(['type'=>'number']) ?>

    <?= $form->field($model, 'DUREECONGES2')->textInput(['type'=>'number']) ?>

    <?= $form->field($model, 'DUREESERVICE')->textInput(['type'=>'number']) ?>

    <?= $form->field($model, 'SIGNATAIRE')->textInput() ?>

    <?= $form->field($model, 'SIGNATAIRE1')->textInput() ?>

    <?= $form->field($model, 'SIGNATAIRE2')->textInput() ?>

<?= $form->field($model, 'TEXTE')->textArea(['rows'=>8]) ?>

<?= $form->field($model, 'TEXTE2')->textArea(['rows'=>8]) ?>

<?= $form->field($model, 'TEXTE3')->textArea(['rows'=>8]) ?>

<?= $form->field($model, 'TEXTE4')->textArea(['rows'=>8]) ?>

<?= $form->field($model, 'ARTICLE1')->textArea(['rows'=>4]) ?>

<?= $form->field($model, 'ARTICLE2')->textArea(['rows'=>4]) ?>

<?= $form->field($model, 'ARTICLE3')->textArea(['rows'=>4]) ?>

    <?= $form->field($model, 'ARTICLE31')->textArea(['rows'=>4]) ?>

<?= $form->field($model, 'JOUISSANCE1')->textArea(['rows'=>8]) ?>

<?= $form->field($model, 'JOUISSANCE2')->textArea(['rows'=>8]) ?>

<?= $form->field($model, 'JOUISSANCE3')->textArea(['rows'=>8]) ?>

    <?= $form->field($model, 'JOUISSANCE4')->textArea(['rows'=>8]) ?>

    <?= $form->field($model, 'JOUISSANCE5')->textArea(['rows'=>8]) ?>

    <?= $form->field($model, 'NONJOUISSANCE')->dropDownList([0=>'NON',1=>'OUI'],['prompt'=>'choisir']) ?>

    <div class="form-group">
        <?= Html::submitButton('Enregistrer', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
