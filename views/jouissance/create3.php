<?php

use app\models\Employe;
use app\models\Jouissance;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Decisionconges;
use app\models\Roles;
use app\models\Habilitation;
use app\models\Menus;


/* @var $this yii\web\View */
/* @var $model app\models\Jouissance */

$this->title = 'Créer une suspension de congé';
$this->params['breadcrumbs'][] = ['label' => 'Jouissances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


$compte = \app\models\USER::findIdentity(Yii::$app->user->identity->IDUSER); $menu = "M9";

$habilation = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu])->one();

?>
<div class="jouissance-create">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?php
    /* if ($model->isNewRecord) echo $form->field($model, 'NUMERO2')->widget(\yii\jui\AutoComplete::classname(), [
         'clientOptions' => ['source' => Jouissance::find()->select(['IDNATURE AS value', 'NUMERO as  label'])->where(['TYPES'=> ['02','01']])->andWhere(['STATUT'=>'A'])->orderBy('NUMERO')->asArray()->all()],
         'options' => ['class' => 'form-control']
     ]); */  ?>

    <?php //$form->field($model, 'TITRE')->textInput(['maxlength' => true]) ?>

    <?php //$form->field($model, 'NUMERO')->textInput(['maxlength' => true, 'disabled'=>$model->isNewRecord?false:true])->label("Timbre de jouissance de cong&eacute;s (Ex: 18/ADC/DG/DH/DH.P/DHPP/SAD/ahm)")?>

    <?= $form->field($model, 'DEBUTREPORT')->textInput(['type'=>'date','min'=>$model->DEBUT])->label("Debut report") ?>

    <?php //$form->field($model, 'FINREPORT')->textInput(['type'=>'date'])->label("Fin report") ?>

    <?php //$form->field($model, 'JOUR')->textInput(['type'=>'date']) ?>

    <?= $form->field($model, 'COMMENTAIRE')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'DOCUMENTFILE2')->fileInput()->label("DOCUMENT"); $model->getDOC1(); ?>

    <?php echo $form->field($model, 'PLATEFORME')->dropDownList(ArrayHelper::map(\app\models\Etablissement::find()->all(),"CODEETS","LIBELLE"),['prompt'=>'Choisir','disabled'=>$model->isNewRecord?false:true])  ?>
    
    <div class="form-group">
        <?php if($model->isNewRecord) echo Html::submitButton('ENREGISTRER', ['class' => 'btn btn-success']) ?>

        <?php

        if(!$model->isNewRecord) {

            echo Html::submitButton('VALIDER ET GENERER LE DOCUMENT DE REPORT', ['class' => 'btn btn-success'])."  ";
        } ?>


    </div>

    <?php ActiveForm::end(); ?>

</div>
