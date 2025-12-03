<?php

use app\models\Decisionconges;
use app\models\Habilitation;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\ArrayHelper;
use app\models\Roles;
use app\models\Menus;

/* @var $this yii\web\View */
/* @var $model app\models\Employe */

$this->title = 'Détail de l\'employé';
$this->params['breadcrumbs'][] = ['label' => 'Employés', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$compte = \app\models\USER::findIdentity(Yii::$app->user->identity->IDUSER); $menu = "M3";

$habilation = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE,'CODEMENU'=>$menu])->one();

?>
<div class="employe-view">

    <p>
        <?php if ($habilation->AUPDATE == 1) echo Html::a('Modifier', ['update', 'id' => $model->MATRICULE], ['class' => 'btn btn-primary']) ?>
        <?php if ($habilation->ADELETE == 1) echo Html::a('Supprimer', ['delete', 'id' => $model->MATRICULE], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Confirmez-vous la suppression ?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'MATRICULE',
            'CODECAT' => [
                'label' => 'Categorie',
                'value' => $model->getCategorie()
            ],
            'CODEECH' => [
                'label' => 'Echellon',
                'value' => $model->getEchellon()
            ],
            'CODEEMP' => [
                'label' => 'Poste',
                'value' => $model->getEmploi()
            ],
            'CODEETS' => [
                'label' => 'Lieu d\'embauche',
                'value' => $model->getEtablissement()
            ],
            'CODECIV' => [
                'label' => 'Civilite',
                'value' => $model->getCivilite()
            ],
            'CODECONT' => [
                'label' => 'Contrat',
                'value' => $model->getContrat()
            ],
            'CODEETS_EMB'=> [
                'label' => 'Lieu d\'affectation',
                'value' => $model->getEtablissement2()
            ],
            'NOM',
            'PRENOM',
            'DATEEMBAUCHE'=> [
                'label' => 'Date embauche',
                'value' => $model->getConvertDate($model->DATEEMBAUCHE)
            ],
            'SOLDECREDIT',
            'SOLDEAVANCE',
            'DATECALCUL'=> [
                'label' => 'Date de prochain congé',
                'value' => $model->getConvertDate($model->DATECALCUL)
            ],
            'DIRECTION'=> [
                'label' => 'Direction',
                'value' => $model->getDirection()
            ],
            'CODEDPT'=> [
                'label' => 'Departement',
                'value' => $model->getDepartement()
            ],
            'SERVICE'=> [
                'label' => 'Service',
                'value' => $model->getService()
            ],
            'COMMENTAIRE',
            'STATUT'=> [
                'label' => 'Statut',
                'value' => $model->getStatut()
            ],
            'DEPLACE'=> [
                'label' => 'Déplacé',
                'value' => $model->getDeplace()
            ],
           'RH'=> [
                        'label' => 'Personnel RH',
                        'value' => $model->getRH()
           ],
        ],
    ]) ?>
</div>

<h3>Décisions de congés</h3>

<div style="overflow: auto;overflow-y: hidden; Height:?">

    <table width="140%" border="1" cellpadding="5" cellspacing="5">

        <tr>
            <th style="padding: 5px">EXERCICE</th>
            <th style="padding: 5px">NUMERO DE DECISION</th>
            <th style="padding: 5px">STATUT</th>
            <th style="padding: 5px">DEBUT</th>
            <th style="padding: 5px">FIN</th>
            <th style="padding: 5px">DUREE DE CONGES</th>
            <th style="padding: 5px">DUREE RESTANTE</th>
            <th style="padding: 5px">DETAILS</th>
        </tr>

        <?php

        $decisions = \app\models\Decisionconges::find()->where(['MATICULE'=>$model->MATRICULE])->orderBy(['ANNEEEXIGIBLE'=>SORT_DESC])->all();

        $i = 0;

        foreach($decisions as $decision) {

            $date1 = strtotime($decision->DEBUTPLANIF); $date2 = strtotime($decision->FINPLANIF);

            $diff = $date2 - $date1;

            $nbjourabs = abs(round($diff/86400)) + 1;

            echo ' <tr>
            <td style="padding: 5px">'.$decision->ANNEEEXIGIBLE.'</td>
            <td style="padding: 5px">'.$decision->REF_DECISION.'</td>
            <td style="padding: 5px">'.$decision->getStatut($decision->STATUT).'</td>
            <td style="padding: 5px">'.\app\controllers\Generator::trueDate4($decision->DEBUTPLANIF).'</td>
            <td style="padding: 5px">'.\app\controllers\Generator::trueDate4($decision->FINPLANIF).'</td>
            <td style="padding: 5px">'.$nbjourabs.'</td>
            <td style="padding: 5px">'.$decision->NBJOUR.'</td>
            <td style="padding: 5px">'.Html::a('DETAILS',['decisionconges/update','id'=>$decision->ID_DECISION], ['class' => 'btn btn-success']).'</td>
        </tr>';

            $i++;
        }

        ?>

    </table> <br>

    <b>Total d&eacute;cisions : <?= $i; ?></b>

</div>

<h3>Jouissance de congés</h3>

<div style="overflow: auto;overflow-y: hidden; Height:?">

    <table width="140%" border="1" cellpadding="5" cellspacing="5">

        <tr>
            <th style="padding: 5px">EXERCICE</th>
            <th style="padding: 5px">NUMERO DE JOUISSANCE</th>
            <th style="padding: 5px">NUMERO DE DECISION</th>
            <th style="padding: 5px">TYPE JOUISSANCE</th>
            <th style="padding: 5px">STATUT</th>
            <th style="padding: 5px">DEBUT</th>
            <th style="padding: 5px">FIN</th>
            <th style="padding: 5px">DUREE JOUISSANCE</th>
            <th style="padding: 5px">DETAILS</th>
        </tr>

        <?php

        $jouissances = \app\models\Jouissance::find()->where(['IN','IDDECISION',Decisionconges::find()->select('ID_DECISION')->where(['MATICULE'=>$model->MATRICULE])])->orderBy(['EXERCICE'=>SORT_DESC])->all();

        $j = 0;

        foreach($jouissances as $jouissance) {

            $decision = \app\models\Decisionconges::find()->where(['ID_DECISION'=>$jouissance->IDDECISION])->one();

            $date1 = strtotime($jouissance->DEBUT); $date2 = strtotime($jouissance->FIN);

            $diff = $date2 - $date1;

            $nbjourabs = abs(round($diff/86400)) + 1;

            echo ' <tr>
            <td style="padding: 5px">'.$jouissance->EXERCICE.'</td>
            <td style="padding: 5px">'.$jouissance->NUMERO.'</td>
            <td style="padding: 5px">'.$decision->REF_DECISION.'</td>
            <td style="padding: 5px">'.$jouissance->getType().'</td>
            <td style="padding: 5px">'.$jouissance->getStatut($jouissance->STATUT).'</td>
            <td style="padding: 5px">'.\app\controllers\Generator::trueDate4($jouissance->DEBUT).'</td>
            <td style="padding: 5px">'.\app\controllers\Generator::trueDate4($jouissance->FIN).'</td>
            <td style="padding: 5px">'.$nbjourabs.'</td>
            <td style="padding: 5px">'.Html::a('DETAILS',['jouissance/update','id'=>$jouissance->IDNATURE], ['class' => 'btn btn-success']).'</td>
        </tr>';

            $j++;

        }

        ?>

    </table><br>

    <b>Total jouissances : <?= $j; ?></b>

</div>

<h3>Permissions d'absences</h3>

<div style="overflow: auto;overflow-y: hidden; Height:?">

    <table width="140%" border="1" cellpadding="5" cellspacing="5">

        <tr>
            <th style="padding: 5px">EXERCICE</th>
            <th style="padding: 5px">NATURE</th>
            <th style="padding: 5px">STATUT</th>
            <th style="padding: 5px">IMPUTER SUR CONGES</th>
            <th style="padding: 5px">DEBUT</th>
            <th style="padding: 5px">FIN</th>
            <th style="padding: 5px">DUREE PERMISSION</th>
            <th style="padding: 5px">DETAILS</th>
        </tr>

        <?php

        $permissions = \app\models\Absenceponctuel::find()->where(['MATICULE'=>$model->MATRICULE])->orderBy(['ANNEEEXIGIBLE'=>SORT_DESC])->all();

        $k = 0;

        foreach($permissions as $permission) {

            $nature = \app\models\Typeabsence::find()->where(['CODEABS'=>$permission->CODEABS])->one();

            $date1 = strtotime($permission->DATEDEBUT); $date2 = strtotime($permission->DATEFIN);

            $diff = $date2 - $date1;

            $nbjourabs = abs(round($diff/86400)) + 1;

            if($permission->IMPUTERCONGES == 1) $text = "OUI"; else $text = "NON";

            echo ' <tr>
            <td style="padding: 5px">'.$permission->ANNEEEXIGIBLE.'</td>
            <td style="padding: 5px">'.$nature->LIBELLE.'</td>
            <td style="padding: 5px">'.$permission->getStatut().'</td>
            <td style="padding: 5px">'.$text.'</td>
            <td style="padding: 5px">'.\app\controllers\Generator::trueDate4($permission->DATEDEBUT).'</td>
            <td style="padding: 5px">'.\app\controllers\Generator::trueDate4($permission->DATEFIN).'</td>
            <td style="padding: 5px">'.$nbjourabs.'</td>
            <td style="padding: 5px">'.Html::a('DETAILS',['absenceponctue/update','id'=>$permission->ID_ABSENCE], ['class' => 'btn btn-success']).'</td>
        </tr>';

            $k++;
        }

        ?>

    </table> <br>

    <b>Total permissions : <?= $k; ?></b>

</div>