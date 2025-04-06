<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\widgets\Alert;
use app\models\Employe;
use app\models\Exercice;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;
use yii\jui\AutoComplete;
use yii\web\View;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Decisionconges */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="decisionconges-form">


<?= Alert::widget() ?>

    <?php $form = ActiveForm::begin(); ?>

    <?php if($model->isNewRecord) {  ?>
    <div class="row" style="color: darkred; padding: 20px; background-color: white; margin: 5px">
        Attention : Vous êtes entrain de vouloir créer une décision de congés manuellement, il faut savoir que ni les permissions cumulées, ni les suspensions ne seront prises en compte. Pour cela bien vouloir depuis l'accueil du module de décision de congés, choisir l'option de génération des décisions de congés.
    </div><br> <?php } ?>

    <?php if($model->isNewRecord) { ?>

       <?php

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

        foreach ($emps as $emp){
            $data[$emp->MATRICULE] = $emp->NOM." ".$emp->PRENOM;
        }

        echo $form->field($model, 'MATICULE')->widget(Select2::classname(), [
            'data' => $data,
            'language' => 'fr',
            'options' => ['placeholder' => 'Choisir une personne ...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])

        ?>



<?= $form->field($model, 'ANNEEEXIGIBLE')->dropDownList(ArrayHelper::map(Exercice::find()->where(["STATUT"=>"O"])->all(),"ANNEEEXIGIBLE","ANNEEEXIGIBLE"),['prompt'=>'choisir','id'=>'exercice']) ?>


        <?php

       /* echo '<label class="control-label">Début planifié</label>';
        echo DatePicker::widget([
            'model' => $model,
            'value' => $model->DEBUTPLANIF,
            'attribute' => 'DEBUTPLANIF',
            'language' => 'fr',
            'dateFormat' => 'yyyy-MM-dd',
            'options' => ['class' => 'form-control input-sm','placeholder' => 'Choisir une date','required'=>true],

        ]);*/

        echo $form->field($model, 'DEBUTPLANIF')->textInput(['required' => true,'type'=>'date']);


        ?><br>

        <?php

     /*   echo '<label class="control-label">Fin planifié</label>';
        echo DatePicker::widget([
            'model' => $model,
            'value' => $model->FINPLANIF,
            'attribute' => 'FINPLANIF',
            'language' => 'fr',
            'dateFormat' => 'yyyy-MM-dd',
            'options' => ['class' => 'form-control input-sm','placeholder' => 'Choisir une date','required'=>true],

        ]); */

        echo $form->field($model, 'FINPLANIF')->textInput(['required' => true,'type'=>'date']);


        ?><br>

<?php  //$form->field($model, 'FINPLANIF')->textInput(['type'=>'date','required'=>true,'id'=>'jour2']) ?>

<?php  echo Html::submitButton('ENREGISTRER', ['class' => 'btn btn-success'])."&nbsp;&nbsp;"; ?>


    <?php } else  { ?>


    <?= $form->field($model, 'MATICULE')->dropDownList(ArrayHelper::map(Employe::find()->all(),"MATRICULE","fullname"),['disabled'=>'disabled']) ?>

    <?= $form->field($model, 'ANNEEEXIGIBLE')->dropDownList(ArrayHelper::map(Exercice::find()->all(),"ANNEEEXIGIBLE","ANNEEEXIGIBLE"),['disabled'=>'disabled']) ?>


    <?= $form->field($model, 'REF_DECISION')->textInput(['maxlength' => true,'disabled'=>'disabled']) ?>

    <?= $form->field($model, 'DEBUTPERIODE')->textInput(['maxlength' => true,'disabled'=>'disabled','disabled'=>'disabled','type'=>'date']) ?>

    <?= $form->field($model, 'FINPERIODE')->textInput(['maxlength' => true,'disabled'=>'disabled','disabled'=>'disabled','type'=>'date']) ?>


    <?= $form->field($model, 'DEBUTPLANIF')->textInput(['maxlength' => true,'disabled'=>'disabled','type'=>'date']) ?>

<?= $form->field($model, 'FINPLANIF')->textInput(['maxlength' => true,'disabled'=>'disabled','type'=>'date']) ?>



    <?= $form->field($model, 'COMMENTAIRE')->textarea() ?>


    <div class="form-group">
        <?php

        echo Html::submitButton('ENREGISTRER', ['class' => 'btn btn-success'])."&nbsp;&nbsp;";

        //echo Html::a('GENERER LE MODELE D\'EDITION',['decisionconges/generer','id'=>$model->ID_DECISION], ['class' => 'btn btn-primary']);

        // echo Html::a('REGENERER LE MODELE D\'EDITION',['decisionconges/generer1','id'=>$model->ID_DECISION], ['class' => 'btn btn-default']);
        
       if(!$model->isNewRecord) { if(!empty($model->EDITION)) { echo '<a class="btn btn-primary" href="../web/uploads/'.$model->EDITION.'" target="_blank">AFFICHER LE MODELE D\'EDITION</a>&nbsp;&nbsp;';  } }  ?>

        <?php } ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

$lien2 = Yii::$app->getUrlManager()->createUrl('exercice/check');

$script = <<< JS

 
function myexo(){

    var exo = $("#exercice").val();
    
    
      $.ajax({
     url: "$lien2",
     data: {'exercice':exo},
        success: function(data) {
            var tab = data.split(":");

            console.log(tab[0]+" : "+tab[1]);
            
            $('#decisionconges-debutplanif').kvDatepicker({"format": "yyyy-mm-dd", "startDate" : tab[0],"endDate" : tab[1] });
            
            $('#decisionconges-finplanif').kvDatepicker({"format": "yyyy-mm-dd", "startDate" : tab[0],"endDate" : tab[1] });
            
         }
        });

}

JS;

$this->registerJs($script,View::POS_END);

?>
