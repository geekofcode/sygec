<?php

use app\models\Employe;
use app\models\Habilitation;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;
use app\models\Decisionconges;
use app\models\Roles;
use app\models\Menus;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Jouissance */
/* @var $form yii\widgets\ActiveForm */
/* @var $matricule string */


$compte = \app\models\USER::findIdentity(Yii::$app->user->identity->IDUSER); $menu = "M9";  $menu2 = "M19";

$habilation = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu])->one();

$habilation1 = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu2])->one();

?>

<div class="jouissance-form">

  <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?php

    if($model->isNewRecord){

        $data = array();
        $query = "SELECT * FROM employe WHERE STATUT = 1 "; $d = array();

        if(Yii::$app->user->identity->ROLE == "R3") {

        $direction = "(";

        if(Yii::$app->user->identity->DIRECTION != null) {
            $emps = Employe::find()->where(["DIRECTION" => Yii::$app->user->identity->DIRECTION])->all();
            foreach($emps as $emp) {
                if(!in_array($emp->MATRICULE,$d)) {
                    if(count($d) == 0 ) $direction.= "'".$emp->MATRICULE."'";
                    else $direction.= ",'".$emp->MATRICULE."'";
                    $d[] = $emp->MATRICULE;
                }
            }
        }

        if(Yii::$app->user->identity->DEPARTEMENT != null) {
            $emps = Employe::find()->where(["CODEDPT" => Yii::$app->user->identity->DEPARTEMENT])->all();
            foreach($emps as $emp) {
                if(!in_array($emp->MATRICULE,$d)) {
                    if(count($d) == 0 ) $direction.= "'".$emp->MATRICULE."'";
                    else $direction.= ",'".$emp->MATRICULE."'";
                    $d[] = $emp->MATRICULE;
                }
            }
        }

        if(Yii::$app->user->identity->SERVICE != null) {
            $emps = Employe::find()->where(["SERVICE" => Yii::$app->user->identity->SERVICE])->all();
            foreach($emps as $emp) {
                if(!in_array($emp->MATRICULE,$d)) {
                    if(count($d) == 0 ) $direction.= "'".$emp->MATRICULE."'";
                    else $direction.= ",'".$emp->MATRICULE."'";
                    $d[] = $emp->MATRICULE;
                }
            }
        }

        $direction.=")";

        }

        if(count($d) != 0) $query.=" AND MATRICULE IN $direction";

        $emps = Employe::findBySql($query)->orderBy(["NOM"=>SORT_ASC])->all();

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
    if ($model->isNewRecord) echo $form->field($model, 'IDDECISION')->dropDownList([],['prompt'=>'Choisir']);
    else echo $form->field($model, 'IDDECISION')->dropDownList(ArrayHelper::map(Decisionconges::find()->where(['STATUT'=>'V'])->andWhere(['!=','EDITION',''])->andWhere(['MATICULE' => $matricule])->orderBy(['ID_DECISION'=>SORT_ASC])->all(),"ID_DECISION","name3"),['prompt'=>'Choisir','disabled'=>($model->STATUT == "B")?false:true])  ?>

    <?php echo $form->field($model, 'TYPES')->dropDownList(["01"=>"JOUISSANCE TOTALE","02"=>"JOUISSANCE PARTIELLE","04"=>"RELIQUAT CONGE"],['prompt'=>'Choisir','disabled'=>($model->isNewRecord)? false:true,'id'=>'types','onchange'=>'choix()']);   ?>

    <?php //$form->field($model, 'TITRE')->textInput(['maxlength' => true]) ?>

    <?php //$form->field($model, 'NUMERO')->textInput(['maxlength' => true, 'disabled'=>$model->isNewRecord?false:true])->label("Timbre de jouissance de cong&eacute;s (Ex: 18/ADC/DG/DH/DH.P/DHPP/SAD/ahm)")?>

    <?php if($model->STATUT != "B") echo $form->field($model, 'DEBUT')->textInput(['type'=>'date','readonly'=>true]) ?>

    <?php if($model->STATUT != "B")  echo $form->field($model, 'FIN')->textInput(['type'=>'date','readonly'=>true]) ?>
<!-- <span>NB : En cas de jouissance totale, la date de fin est calcul&eacute;e en automatiquement</span><br><br> -->
    <?php //$form->field($model, 'LIEU')->textInput(['maxlength' => true]) ?>

    <?php

    if($model->STATUT == "R") {

        echo $form->field($model, 'DEBUTREPORT')->textInput(['type'=>'date','readonly'=>true]);

        echo $form->field($model, 'FINREPORT')->textInput(['type'=>'date','readonly'=>true]);
    }

    ?>


    <?php

    if($model->STATUT == "B") {

        echo $form->field($model, 'debutconge')->textInput(['required' => true,'type'=>'date']);

        echo "<br>";

        echo $form->field($model, 'nbjour')->textInput(['type'=>'number', 'min'=>12, 'required'=>false,'id'=>'nbjour']);

        echo $form->field($model, 'TIMBRE')->textInput(['type'=>'text', 'required'=>true]);

        echo $form->field($model, 'SIGNATAIRE')->textInput(['type'=>'text', 'required'=>true]);

        echo $form->field($model, 'RESPONSABLE')->textInput(['type'=>'text', 'required'=>true]);

    }

    else {

        echo $form->field($model, 'TIMBRE')->textInput(['type'=>'text', 'required'=>true, 'disabled'=> true]);

        echo $form->field($model, 'SIGNATAIRE')->textInput(['type'=>'text', 'required'=>true, 'disabled'=>($model->STATUT == "V")?true:false]);

        echo $form->field($model, 'RESPONSABLE')->textInput(['type'=>'text', 'required'=>true, 'disabled'=>($model->STATUT == "V")?true:false]);

    }

    ?>

    <?php //$form->field($model, 'JOUR')->textInput(['type'=>'date']) ?>

    <?= $form->field($model, 'COMMENTAIRE')->textarea(['rows' => 6]) ?>

<?php if($model->STATUT == "B") { echo $form->field($model, 'DOCUMENTFILE')->fileInput()->label("DOCUMENT"); } else {
    if($model->STATUT != "V")  echo $form->field($model, 'DOCUMENTFILE')->fileInput()->label("DOCUMENT");
}  $model->getDOC(); ?>

    <?php echo $form->field($model, 'PLATEFORME')->dropDownList(ArrayHelper::map(\app\models\Etablissement::find()->all(),"CODEETS","LIBELLE"),['prompt'=>'Choisir','disabled'=>($model->STATUT == "B")?false:true])  ?>

    <div class="form-group">
        <?php if($model->isNewRecord) echo Html::submitButton('ENREGISTRER', ['class' => 'btn btn-success']) ?>

        <?php 

if(!$model->isNewRecord) {

    echo Html::submitButton('ENREGISTRER', ['class' => 'btn btn-success'])."  ";


    if($model->STATUT == "B") {

        if ($habilation->ADELETE == 1) echo Html::a('VALIDER ET GENERER LE DOCUMENT D\'AUTORISATION',['jouissance/valider','id'=>$model->IDNATURE], ['class' => 'btn btn-primary']);

        if ($habilation->ADELETE == 1) echo "&nbsp;&nbsp;".Html::a('ANNULER ET SUPPRIMER LA JOUISSANCE',['jouissance/delete2','id'=>$model->IDNATURE], ['class' => 'btn btn-danger']);


    }

    else  {

        echo '<a class="btn btn-primary" href="../web/uploads/'.$model->DOCUMENT.'" target="_blank">AFFICHER LE DOCUMENT D\'AUTORISATION</a>  ';

        if($model->STATUT == "R") {

            echo '<a class="btn btn-primary" href="../web/uploads/'.$model->NUMERO2.'" target="_blank">AFFICHER LE DOCUMENT DE REPORT</a>  ';
        }

       if($model->TYPES == "01" || $model->TYPES == "02" || $model->TYPES == "04")  {

        if($model->STATUT == "V") {

        if (($habilation->ADELETE == 1) && ($habilation1->ACREATE == 1)) echo Html::a('SUSPENDRE LA JOUISSANCE',['cancelation/create','id'=>$model->IDNATURE], ['class' => 'btn btn-danger'])."&nbsp;&nbsp;";

        if (($habilation->ADELETE == 1)) echo Html::a('RAPPELER LE DOCUMENT',['jouissance/rappeler','id'=>$model->IDNATURE], ['class' => 'btn btn-success'])."&nbsp;&nbsp;";

        if($habilation->ADELETE == 1) echo "&nbsp;&nbsp;".Html::a('REPORTER LA JOUISSANCE',['jouissance/create2','id'=>$model->IDNATURE], ['class' => 'btn btn-info']);

       }

    }

    }
}
?>
      

    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

$lien = Yii::$app->getUrlManager()->createUrl('jouissance/checker');

$script = <<< JS

 $(document).ready(function() {
      //$(".field-nbjour").css({'display':'none'}); 
      $("#nbjour").prop("required",false);
 });

function choix(){

    var types = $("#types").val();
    
    if(types == "02") {
        
        $(".field-nbjour").css({'display':'block'});
        
        $("#nbjour").prop("required",true);
        
    }
    
    else  { $(".field-nbjour").css({'display':'none'}); $("#nbjour").prop("required",false); }
}

function instancier() {
    
    var personne = $("#jouissance-employe").val(); 
    
    $("#jouissance-iddecision").html("");
    
       $.ajax({
            url: "$lien",
            data: {'personne':personne},
            success: function(data) {
                
            if(data != "" ) {
                
                var tab = JSON.parse(data);
                
                for (var i=0;i<tab.length;i++) {
                    
                     var options = '<option value="'+tab[i].id+'">Décision '+tab[i].libelle+' pour une période de '+tab[i].duree+' jour(s) (du '+tab[i].debut+' au '+tab[i].fin+')</option>';
               
                      $("#jouissance-iddecision").append(options);   
                 }
               }
            }
        });

}

JS;

$this->registerJs($script,View::POS_END);

?>