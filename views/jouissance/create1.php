<?php

use yii\helpers\Html;
use app\models\Employe;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Decisionconges;
use app\models\Roles;
use app\models\Menus;
use app\models\Habilitation;
use kartik\select2\Select2;
use yii\web\View;


/* @var $this yii\web\View */
/* @var $model app\models\Jouissance */

$this->title = 'Créer une non jouissance';
$this->params['breadcrumbs'][] = ['label' => 'Jouissances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


$compte = \app\models\USER::findIdentity(Yii::$app->user->identity->IDUSER); $menu = "M9";

$habilation = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu])->one();

?>
<div class="jouissance-create">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?php

    if($model->isNewRecord){

        $data = array();
        $query = "SELECT * FROM employe WHERE STATUT = 1 "; $d = array();

        if(Yii::$app->user->identity->ROLE == "R3") {

            $direction = "(";

            if (Yii::$app->user->identity->DIRECTION != null) {
                $emps = Employe::find()->where(["DIRECTION" => Yii::$app->user->identity->DIRECTION])->all();
                foreach ($emps as $emp) {
                    if (!in_array($emp->MATRICULE, $d)) {
                        if (count($d) == 0) $direction .= "'" . $emp->MATRICULE . "'";
                        else $direction .= ",'" . $emp->MATRICULE . "'";
                        $d[] = $emp->MATRICULE;
                    }
                }
            }

            if (Yii::$app->user->identity->DEPARTEMENT != null) {
                $emps = Employe::find()->where(["CODEDPT" => Yii::$app->user->identity->DEPARTEMENT])->all();
                foreach ($emps as $emp) {
                    if (!in_array($emp->MATRICULE, $d)) {
                        if (count($d) == 0) $direction .= "'" . $emp->MATRICULE . "'";
                        else $direction .= ",'" . $emp->MATRICULE . "'";
                        $d[] = $emp->MATRICULE;
                    }
                }
            }

            if (Yii::$app->user->identity->SERVICE != null) {
                $emps = Employe::find()->where(["SERVICE" => Yii::$app->user->identity->SERVICE])->all();
                foreach ($emps as $emp) {
                    if (!in_array($emp->MATRICULE, $d)) {
                        if (count($d) == 0) $direction .= "'" . $emp->MATRICULE . "'";
                        else $direction .= ",'" . $emp->MATRICULE . "'";
                        $d[] = $emp->MATRICULE;
                    }
                }
            }

            $direction .= ")";

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
    else echo $form->field($model, 'IDDECISION')->dropDownList(ArrayHelper::map(Decisionconges::find()->where(['STATUT'=>'V'])->andWhere(['!=','EDITION',''])->orderBy(['ID_DECISION'=>SORT_ASC])->all(),"ID_DECISION","REF_DECISION"),['prompt'=>'Choisir','disabled'=>$model->isNewRecord?false:true])  ?>

    <?php //$form->field($model, 'NUMERO')->textInput(['maxlength' => true, 'disabled'=>$model->isNewRecord?false:true])->label("Timbre de jouissance de cong&eacute;s (Ex: 18/ADC/DG/DH/DH.P/DHPP/SAD/ahm)")?>

    <?php

    if($model->isNewRecord) {

  echo $form->field($model, 'TIMBRE')->textInput(['type'=>'text', 'required'=>true]);

        echo $form->field($model, 'SIGNATAIRE')->textInput(['type'=>'text', 'required'=>true]);

        echo $form->field($model, 'RESPONSABLE')->textInput(['type'=>'text', 'required'=>true]);

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

        if(!$model->isNewRecord) {  echo Html::submitButton('ENREGISTRER', ['class' => 'btn btn-success'])."  "; if($model->STATUT == "B") { if ($habilation->ADELETE == 1) echo Html::a('VALIDER ET GENERER LE DOCUMENT D\'AUTORISATION',['jouissance/valider','id'=>$model->IDNATURE], ['class' => 'btn btn-primary']); } else  { echo '<a class="btn btn-primary" href="../web/uploads/'.$model->DOCUMENT.'" target="_blank">AFFICHER LE DOCUMENT D\'AUTORISATION</a>  ';  if ($habilation->ADELETE == 1) echo Html::a('ANNULER LA JOUISSANCE',['jouissance/cancel','id'=>$model->IDNATURE], ['class' => 'btn btn-danger']); } } ?>


    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php

$lien = Yii::$app->getUrlManager()->createUrl('jouissance/checker');

$script = <<< JS


function choix(){

    var types = $("#types").val();
    
    if(types == "04") {
        
        $(".cache").css({'display':'block'});
        
        $("#debut").prop("required",true);
        
    }
    
    else  { $(".cache").css({'display':'none'}); $("#debut").prop("required",false); }
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