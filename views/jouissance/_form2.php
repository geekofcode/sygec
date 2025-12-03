<?php

use app\models\Employe;
use app\models\Jouissance;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Decisionconges;
use app\models\Roles;
use app\models\Menus;
use app\models\Habilitation;

/* @var $this yii\web\View */
/* @var $model app\models\Jouissance */
/* @var $form yii\widgets\ActiveForm */
/* @var $matricule string */

$compte = \app\models\USER::findIdentity(Yii::$app->user->identity->IDUSER); $menu = "M9";

$habilation = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu])->one();


?>

<div class="jouissance-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?php
    if ($model->STATUT == "B") echo $form->field($model, 'IDDECISION')->widget(\yii\jui\AutoComplete::classname(), [
        'clientOptions' => ['source' => Decisionconges::find()->select(['ID_DECISION AS value', 'REF_DECISION as  label'])->where(['STATUT'=>'V'])->andWhere(['!=','EDITION',''])->orderBy('REF_DECISION')->asArray()->all()],
        'options' => ['class' => 'form-control']
    ]); else echo $form->field($model, 'IDDECISION')->dropDownList(ArrayHelper::map(Decisionconges::find()->where(['STATUT'=>'V'])->andWhere(['!=','EDITION',''])->orderBy(['ID_DECISION'=>SORT_ASC])->all(),"ID_DECISION","REF_DECISION"),['prompt'=>'Choisir','disabled'=>($model->STATUT == "B")?false:true])  ?>

    <?php //$form->field($model, 'TITRE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'NUMERO')->textInput(['maxlength' => true, 'disabled'=>($model->STATUT == "B")?false:true])->label("Timbre de jouissance de cong&eacute;s (Ex: 18/ADC/DG/DH/DH.P/DHPP/SAD/ahm)")?>

    <?= $form->field($model, 'DEBUT')->textInput(['type'=>'date','disabled'=>($model->STATUT == "B")?false:true])->label("Debut report") ?>

    <?= $form->field($model, 'FIN')->textInput(['type'=>'date','disabled'=>($model->STATUT == "B")?false:true])->label("Fin report") ?>

    <?php //$form->field($model, 'JOUR')->textInput(['type'=>'date']) ?>

    <?= $form->field($model, 'COMMENTAIRE')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'DOCUMENTFILE')->fileInput()->label("DOCUMENT"); $model->getDOC(); ?>

    <?php echo $form->field($model, 'PLATEFORME')->dropDownList(ArrayHelper::map(\app\models\Etablissement::find()->all(),"CODEETS","LIBELLE"),['prompt'=>'Choisir','disabled'=>($model->STATUT == "B")?false:true])  ?>

    <div class="form-group">
        <?php if($model->isNewRecord) echo Html::submitButton('ENREGISTRER', ['class' => 'btn btn-success']) ?>

        <?php

        if(!$model->isNewRecord) {  echo Html::submitButton('ENREGISTRER', ['class' => 'btn btn-success'])."  "; if($model->STATUT == "B") {
            if ($habilation->ADELETE == 1) echo Html::a('VALIDER ET GENERER LE DOCUMENT D\'AUTORISATION',['jouissance/valider','id'=>$model->IDNATURE], ['class' => 'btn btn-primary']); } else  {
            echo '<a class="btn btn-primary" href="../web/uploads/'.$model->DOCUMENT.'" target="_blank">AFFICHER LE DOCUMENT D\'AUTORISATION</a>  ';   if (($habilation->ADELETE == 1)) echo Html::a('RAPPELER LE DOCUMENT',['jouissance/rappeler','id'=>$model->IDNATURE], ['class' => 'btn btn-success'])."&nbsp;&nbsp;"; } } ?>


    </div>

    <?php ActiveForm::end(); ?>

</div>
