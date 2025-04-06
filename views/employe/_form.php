<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Categorie;
use app\models\Echellon;
use app\models\Emploi;
use app\models\Etablissement;
use app\models\Civilite;
use app\models\Contrat;
use kartik\date\DatePicker;
use app\models\Departements;

/* @var $this yii\web\View */
/* @var $model app\models\Employe */
/* @var $form yii\widgets\ActiveForm */

$exist = false;

if(!$model->isNewRecord) {
    $decision = \app\models\Decisionconges::findOne(["MATICULE"=>$model->MATRICULE]);
    if($decision != null) $exist = true;
}


?>

<div class="employe-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'MATRICULE')->textInput(['maxlength' => true,'readonly'=>$model->isNewRecord?false:true]) ?>

    <?= $form->field($model, 'CODECAT')->dropDownList(ArrayHelper::map(Categorie::find()->all(),"CODECAT","LIBELLE"),['prompt'=>'Choisir']) ?>

    <?= $form->field($model, 'CODEECH')->dropDownList(ArrayHelper::map(Echellon::find()->all(),"CODEECH","LIBELLE"),['prompt'=>'Choisir']) ?>

    <?= $form->field($model, 'CODEEMP')->dropDownList(ArrayHelper::map(Emploi::find()->orderBy(['LIBELLE'=>SORT_ASC])->all(),"CODEEMP","LIBELLE"),['prompt'=>'Choisir']) ?>

    <?= $form->field($model, 'DIRECTION')->dropDownList(ArrayHelper::map(\app\models\Direction::find()->all(),"ID","LIBELLE"),['prompt'=>'Choisir']) ?>

    <?= $form->field($model, 'CODEDPT')->dropDownList(ArrayHelper::map(Departements::find()->all(),"CODEDPT","LIBELLE"),['prompt'=>'Choisir']) ?>

    <?= $form->field($model, 'SERVICE')->dropDownList(ArrayHelper::map(\app\models\Service::find()->all(),"ID","LIBELLE"),['prompt'=>'Choisir'])  ?>

    <?= $form->field($model, 'CODEETS')->dropDownList(ArrayHelper::map(Etablissement::find()->all(),"CODEETS","LIBELLE"),['prompt'=>'Choisir']) ?>

  <?= $form->field($model, 'CODEETS_EMB')->dropDownList(ArrayHelper::map(Etablissement::find()->all(),"CODEETS","LIBELLE"),['prompt'=>'Choisir']) ?>

    <?= $form->field($model, 'CODECIV')->dropDownList(ArrayHelper::map(Civilite::find()->all(),"CODECIV","LIBELLE"),['prompt'=>'Choisir']) ?>

    <?= $form->field($model, 'SITMAT')->dropDownList(ArrayHelper::map(\app\models\Sitmat::find()->all(),"CODESIT","LIBELLE"),['prompt'=>'Choisir']) ?>

    <?= $form->field($model, 'CODECONT')->dropDownList(ArrayHelper::map(Contrat::find()->all(),"CODECONT","LIBELLE"),['prompt'=>'Choisir']) ?>

    <?= $form->field($model, 'NOM')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PRENOM')->textInput(['maxlength' => true]) ?>

    <?php

  /*  echo '<label class="control-label">Date d\'embauche</label>';
    echo DatePicker::widget([
        'model' => $model,
        'value' => $model->DATEEMBAUCHE,
        'attribute' => 'DATEEMBAUCHE',
        'options' => ['placeholder' => 'Choisir une date'],
        'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'yyyy-mm-dd',
            'todayHighlight' => true
        ]
    ]); */

    echo $form->field($model, 'DATEEMBAUCHE')->textInput(['required' => true,'type'=>'date'])

    ?>


    <?php if(!$model->isNewRecord) echo $form->field($model, 'SOLDECREDIT')->textInput(['type'=>'number','readonly'=>false]) ?>

    <?php if(!$model->isNewRecord) echo  $form->field($model, 'SOLDEAVANCE')->textInput(['type'=>'number','readonly'=>false]) ?>

    <?php

    if (!$model->isNewRecord) {

       /* echo '<label class="control-label">Date de prochain congé</label>';
        echo DatePicker::widget([
            'model' => $model,
            'value' => $model->DATECALCUL,
            'attribute' => 'DATECALCUL',
            'options' => ['placeholder' => 'Choisir une date'],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'todayHighlight' => true
            ]
        ]);*/

        echo $form->field($model, 'DATECALCUL')->textInput(['required' => $exist?true:false,'type'=>'date']);

    }

    ?>


    <?php

    if (!$model->isNewRecord) {

       /* echo '<br><label class="control-label">Date de dernier congé</label>';
        echo DatePicker::widget([
            'model' => $model,
            'value' => $model->LASTCONGE,
            'attribute' => 'LASTCONGE',
            'options' => ['placeholder' => 'Choisir une date'],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'todayHighlight' => true
            ]
        ]); */

        echo $form->field($model, 'LASTCONGE')->textInput(['required' => $exist?true:false,'type'=>'date']);

        //echo "<br>";
    }
?>

    <?php

  /*  echo '<label class="control-label">Date de naissance</label>';
    echo DatePicker::widget([
        'model' => $model,
        'value' => $model->DATNAISS,
        'attribute' => 'DATNAISS',
        'options' => ['placeholder' => 'Choisir une date'],
        'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'yyyy-mm-dd',
            'todayHighlight' => true
        ]
    ]);*/

    echo $form->field($model, 'DATNAISS')->textInput(['required' => true,'type'=>'date']);

    ?>

    <?= $form->field($model, 'STATUT')->dropDownList([1=>'ACTIF',0=>'INACTIF'],['prompt'=>'Choisir']); ?>

    <?= $form->field($model, 'DEPLACE')->dropDownList([1=>'OUI',0=>'NON'],['prompt'=>'Choisir']); ?>

    <?= $form->field($model, 'COMMENTAIRE')->textarea() ?>

    <?= $form->field($model, 'RH')->dropDownList([1=>'OUI',0=>'NON'],['prompt'=>'Choisir']); ?>

    <?= $form->field($model, 'AMPLIATION')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'TIMBRE')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Enregistrer', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
