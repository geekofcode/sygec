<?php

namespace app\commands;

use app\controllers\Generator;
use yii\console\Controller;
use yii\console\ExitCode;
use Yii;
use app\models\Decisionconges;
use app\models\Parametre;
use app\models\Employe;
use app\models\Departements;
use app\models\Jouissance;

class DecisioncongesController extends \yii\web\Controller
{

    public function actionIndex()
    {

        $tab = array(); $setting = Parametre::findOne(1);

        $decisions = Decisionconges::find()->where(['STATUT'=>'V'])->all();

        foreach($decisions as $decision){

            if(!empty($decision->EDITION)) {

                $date_start = ($decision->DATEVAL);
                $date_stop = date("Y-m-d H:i:s");

                $date_start = new \DateTime($date_start);
                $date_stop = new \DateTime($date_stop);

                $diff = $date_stop->diff($date_start);

                if($diff->days > $setting->DUREERAPPEL) {

                    $jouissance = Jouissance::find()->where(['IDDECISION' => $decision->ID_DECISION, 'STATUT' => 'V'])->all();

                    $nb = count($jouissance);

                    if($nb == 0) $tab[$decision->ID_DECISION];

                }
            }
        }


        // creation du rappel des projets de decisions non traitees

        $message = "Bonjour, <br><br>Les decisions de conges suivantes n'ont pas encore donne lieu a des jouissances et/ou non jouissance de conges totale et/ou partielle.<br><br>";

        foreach($tab as $val){

            $decision = Decisionconges::findOne($val);

            $employe = Employe::findOne($decision->MATICULE);

            $departement = Departements::findOne($employe->CODEDPT);

            if($employe != null) $nom = $employe->NOM." ".$employe->PRENOM." (".$employe->MATRICULE.")"; else $nom = "";

            if($departement != null) $dpt = $departement->LIBELLE; else $dpt = "";

            $message.="- Décision numéro ".$decision->REF_DECISION." concernant ".$nom.". Département : ".$dpt."<br><br>";

        }

        \Yii::$app
            ->mailer->compose()
            ->setTo("emmanuel.gbetkom@adcsa.aero")
            ->setFrom([\Yii::$app->params['supportEmail'] => 'ADC Conges'])
            ->setSubject("Decision de congés non validées")
            ->setHtmlBody($message)
            ->send();

        echo  "mail send \n";

        return ExitCode::OK;
    }


}