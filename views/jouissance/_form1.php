<?php

use app\models\Employe;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Decisionconges;
use app\models\Roles;
use app\models\Menus;
use kartik\select2\Select2;
use yii\web\View;
use app\models\Habilitation;

/* @var $this yii\web\View */
/* @var $model app\models\Jouissance */
/* @var $form yii\widgets\ActiveForm */


$compte = \app\models\USER::findIdentity(Yii::$app->user->identity->IDUSER); $menu = "M9";

$habilation = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu])->one();

?>

<div class="jouissance-form">

   <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?php

    if($model->isNewRecord){

        $data = array();
        $emps = Employe::find()->where(['STATUT' => 1])->orderBy(["NOM" => SORT_ASC])->all();
        foreach ($emps as $emp) {
            $data[$emp->MATRICULE] = $emp->NOM . " " . $emp->PRENOM;
        }

        echo $form->field($model, 'employe')->widget(Select2::classname(), [
            'data' => $data,
            'language' => 'fr',
            'options' => [
                'placeholder' => 'Choisir une personne ...',
                'multiple' => false
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
            'pluginEvents' => [
                'change' => "function() { 
                                        
                    $(this).children(':selected').each(function(){
                                        
                    instancier();

                                        });
                                            
                                      }",
            ]
        ]);


    } else {

        echo $form->field($model, 'employe')->textInput(['type'=>'text','disabled'=>true]);

    }

    ?>

   <?php
       if ($model->isNewRecord) echo $form->field($model, 'IDDECISION')->widget(\yii\jui\AutoComplete::classname(), [
           'clientOptions' => ['source' => Decisionconges::find()->select(['ID_DECISION AS value', 'REF_DECISION as  label'])->where(['STATUT'=>'V'])->andWhere(['!=','EDITION',''])->orderBy('REF_DECISION')->asArray()->all()],
           'options' => ['class' => 'form-control']
       ]); else echo $form->field($model, 'IDDECISION')->dropDownList(ArrayHelper::map(Decisionconges::find()->where(['STATUT'=>'V'])->andWhere(['!=','EDITION',''])->orderBy(['ID_DECISION'=>SORT_ASC])->all(),"ID_DECISION","name2"),['prompt'=>'Choisir','disabled'=>$model->isNewRecord?false:true])  ?>

         <?php //$form->field($model, 'NUMERO')->textInput(['maxlength' => true, 'disabled'=>$model->isNewRecord?false:true])->label("Timbre de jouissance de cong&eacute;s (Ex: 18/ADC/DG/DH/DH.P/DHPP/SAD/ahm)")?>

    <?php echo $form->field($model, 'TYPES')->dropDownList(["01"=>"JOUISSANCE TOTALE","02"=>"JOUISSANCE PARTIELLE","04"=>"RELIQUAT CONGE","03"=>"NON JOUISSANCE"],['prompt'=>'Choisir','disabled'=>$model->isNewRecord?false:true,'id'=>'types','onchange'=>'choix()']);   ?>

    <?php

    if($model->isNewRecord) {

        echo $form->field($model, 'TIMBRE')->textInput(['type'=>'text', 'required'=>true]);

        echo $form->field($model, 'SIGNATAIRE')->textInput(['type'=>'text', 'required'=>true]);

    }

    else {

        echo $form->field($model, 'TIMBRE')->textInput(['type'=>'text', 'required'=>true, 'disabled'=> true]);

        echo $form->field($model, 'SIGNATAIRE')->textInput(['type'=>'text', 'required'=>true, 'disabled'=>($model->STATUT == "V")?true:false]);
    }

    ?>

    <?= $form->field($model, 'COMMENTAIRE')->textarea(['rows' => 6]) ?>

     <?= $form->field($model, 'DOCUMENTFILE')->fileInput()->label("DOCUMENT"); $model->getDOC(); ?>

    <?php echo $form->field($model, 'PLATEFORME')->dropDownList(ArrayHelper::map(\app\models\Etablissement::find()->all(),"CODEETS","LIBELLE"),['prompt'=>'Choisir','disabled'=>$model->isNewRecord?false:true])  ?>

     <div class="form-group">
         <?php if($model->isNewRecord) echo Html::submitButton('ENREGISTRER', ['class' => 'btn btn-success']) ?>

         <?php

         if(!$model->isNewRecord) {  echo Html::submitButton('ENREGISTRER', ['class' => 'btn btn-success'])."  "; if($model->STATUT == "B") {  if ($habilation->ADELETE == 1) echo Html::a('VALIDER ET GENERER LE DOCUMENT D\'AUTORISATION',['jouissance/valider','id'=>$model->IDNATURE], ['class' => 'btn btn-primary']);    if ($habilation->ADELETE == 1) echo "&nbsp;&nbsp;".Html::a('ANNULER ET SUPPRIMER LA NON JOUISSANCE',['jouissance/delete2','id'=>$model->IDNATURE], ['class' => 'btn btn-danger']);  } else  { echo '<a class="btn btn-primary" href="../web/uploads/'.$model->DOCUMENT.'" target="_blank">AFFICHER LE DOCUMENT D\'AUTORISATION</a>  ';   } } ?>


     </div>

     <?php ActiveForm::end(); ?>

</div>
