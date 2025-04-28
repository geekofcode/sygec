<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\widgets\Alert;
use app\models\Exercice;
use yii\web\View;


/* @var $this yii\web\View */
/* @var $model app\models\Exercice */

$this->title = 'Import de décisions de congés';
$this->params['breadcrumbs'][] = ['label' => 'Décisions de congés', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="produits-create">

    <?= Alert::widget() ?>

    <div id="wait" class="alert-info" style="padding:10px; font-size:12px; display:none"></div>

    <?= Html::beginForm(['import'],'post',['id'=>'formsoumission','enctype' => 'multipart/form-data']);?>

    <div>
    <label>Choisir l'exercice</label>
    <select name="exercice" id="exercice" class="form-control" required >
        <option value="" disabled selected>Faire un choix</option>
        <?php

        $exercices = Exercice::find()->orderBy(['ANNEEEXIGIBLE'=>SORT_DESC])->all();

        foreach($exercices as $exercice) {

            echo '<option value="'.$exercice->ANNEEEXIGIBLE.'">'.$exercice->ANNEEEXIGIBLE.'</option>';
        }

        ?>
    </select></div> <br />

        <label>Fichier de donn&eacute;es &agrave; importer</label><br>

        <input type="file" name="fichier" id="fichier" required accept=".csv" /> <br><br/>

        <p style="color: red">
        <ul>
            <li>
                Le Modele de fichier de donn&eacute;es &agrave; importer : <a href="../web/tmp/tmp_decision.xlsx" target="_blank">Ici</a>, vous devez importer un fichier au format csv espacé par des points virgules, et non pas un fichier excel
            </li> <br/>
            <li>
                NB : Le fichier doit contenir les colonnes suivantes : MATRICULE, CIVILITE, NOM, NOM DE JEUNE FILLE, PRENOM, DATE DE NAISSANCE, SITUATION MATRIMONIALE, NOMBRE D'ENFANT, DATE D'EMBAUCHE, LIEU D'AFFECTATION, LIEU D'EMBAUCHE, DIRECTION, DEPARTEMENT, SERVICE, FONCTION, CATEGORIE, ECHELLON, CONTRAT, DEBUT DE SERVICE, FIN DE SERVICE, DEBUT DE CONGE, FIN DE CONGE
            </li> <br/>
            <li>Toutes les colonnes de date doivent être de type Date avec le format (YYYY-MM-DD), ex: 2025-04-25, aucune d'elle ne doit être vide ou null</li> <br/>
            <li>Pour les civilités vous devez remplacer, Monsieur par MR, Madame par MME et Mademoiselle par MLLE</li> <br/>
            <li>Pour les situations matrimoniales vous devez remplacer, Célibataire par 001, Marié par 002, Divorcé par 004 et veuf par 003</li> <br/>
            <li>Pour ce qui est des lieux d'embauche et d'affectation, vous devez remplacer les lieux par leur numero d'ampliations (A regarder dans le menu Ampliations), par ex : Douala sera remplacé par DLA, et Bamenda par BDA,..</li><br/>
            <li>Les colonnes départements, services ne doivent pas être vide, si aucune valeur, mettre * </li> <br/>
        </ul>
        </p>
    </div>

    <input type="hidden" name="imp" id="imp" value="1" />

    <div class="form-group" id="zone_bt">
    <button type="submit" style="float: left" class="btn btn-success">Importer le fichier</button><br><br>
    </div>
    <br><br>

    <?= Html::endForm();?>

</div>


<?php

$lien = Yii::$app->getUrlManager()->createUrl('decisionconges/import');

$script = <<< JS

$(document).ready(function() {
    
    $("#wait").hide(); 
    
     $("#formsoumission").on('submit', function(event){
         
         event.preventDefault(); $("#zone_bt").hide();
         
         $("#wait").html("Traitement en cours, veuillez patienter...");
         
         $("#wait").show();
         
         // Create FormData object
         var formData = new FormData(this);
         
         $.ajax({
            url: "$lien",
            type: 'POST',
            data: formData,
            processData: false, // Empêche jQuery de traiter les données
            contentType: false, // Empêche jQuery de définir un en-tête Content-Type
            success: function(response) {
                if (response.success) {
                    console.log(response.message);
                } else {
                    console.error(response.message);
                }
                $("#wait").html(response.message);
                $("#zone_bt").show();
            },
            error: function() {
                console.error('An error occurred');
                $("#wait").html('Une erreur est survenue');
                $("#zone_bt").show();
            }
        });
         
         
     });
    
});

JS;

$this->registerJs($script,View::POS_END);

?>