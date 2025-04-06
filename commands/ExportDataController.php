<?php
namespace app\commands;

use app\controllers\Generator;
use app\models\Absenceponctuel;
use app\models\Decisionconges;
use app\models\Departements;
use app\models\Direction;
use app\models\Emploi;
use app\models\Employe;
use app\models\Exercice;
use app\models\Service;
use app\models\Typeabsence;
use yii\console\Controller;
use yii\console\ExitCode;

class ExportDataController extends Controller
{
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";
        return ExitCode::OK;
    }

    public function actionDecision() {

        $jour1 = date("Y-m-d H:i:s");
        $csv2file = "./web/export-decision-" . $jour1 . ".csv";

        // create file pointer
        $output = fopen($csv2file, "w");

        //output the column headings
        fputcsv($output, array('REFERENCE DECISION','ANNEE','MATRICULE','EMPLOYE','DEBUT SERVICE','FIN SERVICE', 'DEBUT CONGE','FIN CONGE','NOMBRE DE JOURS','DATE EMISSION','DATE REPRISE', 'PROCHAIN CONGE', 'DATE DE NAISSANCE',  'COMMENTAIRE', 'STATUT','DIRECTION','DEPARTEMENT','SERVICE'),",");

        $decisions =  Decisionconges::find()->all();

        foreach ($decisions as $absence) {

            $employe = Employe::findOne($absence->MATICULE);

            if($employe != null) {

                if($employe->CODEDPT != null) {
                    $mdpt = Departements::findOne($employe->CODEDPT); $dpt = $mdpt->LIBELLE;

                } else $dpt = "";

                $date1 = strtotime($absence->DEBUTPLANIF); $date2 = strtotime($absence->FINPLANIF);

                $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

                if($absence->EDITION != null) $edition = "EN COURS"; else $edition = "BROUILLON";

                $direction = Direction::findOne($employe->DIRECTION);
                $service = Service::findOne($employe->SERVICE);

                $tab2 = array();

                $tab2[] = $absence->REF_DECISION; $tab2[] = $absence->ANNEEEXIGIBLE;  $tab2[] = $employe->MATRICULE;
                $tab2[] = $employe->getFullname(); $tab2[] = $absence->DEBUTREELL;
                $tab2[] = $absence->FINREEL; $tab2[] = $absence->DEBUTPLANIF; $tab2[] = $absence->FINPLANIF; $tab2[] = $nbjour;
                $tab2[] = $absence->DATEEMIS; $tab2[] = $absence->DATEREPRISE;
                $tab2[] = $absence->DATECLOTURE; $tab2[] = $employe->DATNAISS;
                $tab2[] = $absence->COMMENTAIRE; $tab2[] = $edition;
                $tab2[] = ($direction != null)?$direction->LIBELLE:""; $tab2[] = $dpt;
                $tab2[] = ($service != null)?$service->LIBELLE:"";

                fputcsv($output, $tab2, ",");
            }

        }

        fclose($output);

        $path = "./web/export-decision-" . $jour1 . ".csv";

        $content = '<!Doctype html>

        <html>

        <head>

        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />

        </head>

        <body style="font-family: Calibri; font-size: 14px">

        <div>

            <div style="line-height: 20px">

            <span>Bonjour Monsieur/Madame , <br><br>Bien vouloir trouver ci-joints l\'export des décisions de congés.</span><br>
            </div>

        </div>

        </body>

        </html>';

        try {

            \Yii::$app
                ->mailer->compose()
                ->setFrom(['noreply@sygec.cm' => 'SYGEC'])
                ->setTo(["tsumbang@gmail.com"])
                // ->setCc(["eddy.fanga@karbura.com"])
                ->setSubject("Export des décisions de congés")
                ->setHtmlBody($content)
                ->attach($path)
                ->send();

            echo "Export réalisé avec succès \n\r";

        } catch (\Swift_SwiftException $exception) {

            echo "L\'envoi par email de l\'exportation du listing des encaissements n\'a pas pu être effectué : ".$exception->getMessage()."\n\r";

        } catch (\Exception $exception) {

            echo "L\'envoi par email de l\'exportation du listing des encaissements n\'a pas pu être effectué 1 : " .$exception->getMessage()."\n\r";

        }

        return ExitCode::OK;

    }

    public function actionAbsence() {

        $jour1 = date("Y-m-d H:i:s");
        $csv2file = "./web/export-abscence-" . $jour1 . ".csv";

        // create file pointer
        $output = fopen($csv2file, "w");

        //output the column headings
        fputcsv($output, array('TYPE ABSENCE','MATRICULE','EMPLOYE','POSTE','DIRECTION','DEPARTEMENT','SERVICE','TYPE DE PERMISSION','DEBUT','FIN', 'DUREE','IMPUTER CONGES','DATE EMISSION','DATE VALIDATION','DATE ANNULATION', 'COMMENTAIRE', 'STATUT',),",");

        $decisions =  Absenceponctuel::find()->all();

        foreach ($decisions as $absence) {

            $employe = Employe::findOne($absence->MATICULE);
            $typeabs = Typeabsence::findOne($absence->CODEABS);
            if($employe->CODEDPT != null) {

                $mdpt = Departements::findOne($employe->CODEDPT);
                if($mdpt !=null) $dpt = $mdpt->LIBELLE; else $dpt = "";

            } else $dpt = "";

            $imput = $absence->IMPUTERCONGES == 1?"Oui":"Non";

            $date1 = strtotime($absence->DATEDEBUT); $date2 = strtotime($absence->DATEFIN);

            $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

            $direction = Direction::findOne($employe->DIRECTION);
            $service = Service::findOne($employe->SERVICE);
            $job = Emploi::findOne($employe->CODEEMP);
            $tab2 = array();
            $tab2[] = $absence->TYPE_DEMANDE == 0?"JOUR":"HEURE";
            $tab2[] = $employe->MATRICULE;
            $tab2[] = $employe->getFullname();
            $tab2[] = ($job != null)?$job->LIBELLE:"";
            $tab2[] = ($direction != null)?$direction->LIBELLE:"";
            $tab2[] = $dpt;
            $tab2[] = ($service != null)?$service->LIBELLE:"";
            $tab2[] = $typeabs->LIBELLE;
            $tab2[] = $absence->TYPE_DEMANDE == 0?$absence->DATEDEBUT:"";
            $tab2[] = $absence->TYPE_DEMANDE == 0?$absence->DATEFIN:"";
            $tab2[] = $absence->TYPE_DEMANDE == 0?$nbjour:$absence->DUREE;
            $tab2[] = $imput; $tab2[] = $absence->DATEEMIS;
            $tab2[] = $absence->DATEVAL; $tab2[] = $absence->DATEANN;
            $tab2[] = $absence->COMMENTAIRE; $tab2[] = $absence->getStatut();


            fputcsv($output, $tab2, ",");

        }

        fclose($output);

        $path = "./web/export-abscence-" . $jour1 . ".csv";

        $content = '<!Doctype html>

        <html>

        <head>

        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />

        </head>

        <body style="font-family: Calibri; font-size: 14px">

        <div>

            <div style="line-height: 20px">

            <span>Bonjour Monsieur/Madame , <br><br>Bien vouloir trouver ci-joints l\'export des demandes de permissions.</span><br>
            </div>

        </div>

        </body>

        </html>';

        try {

            \Yii::$app
                ->mailer->compose()
                ->setFrom(['noreply@sygec.cm' => 'SYGEC'])
                ->setTo(["tsumbang@gmail.com"])
                // ->setCc(["eddy.fanga@karbura.com"])
                ->setSubject("Export des demandes de permissions")
                ->setHtmlBody($content)
                ->attach($path)
                ->send();

            echo "Export réalisé avec succès \n\r";

        } catch (\Swift_SwiftException $exception) {

            echo "L\'envoi par email de l\'exportation du listing des encaissements n\'a pas pu être effectué : ".$exception->getMessage()."\n\r";

        } catch (\Exception $exception) {

            echo "L\'envoi par email de l\'exportation du listing des encaissements n\'a pas pu être effectué 1 : " .$exception->getMessage()."\n\r";

        }

        return ExitCode::OK;

    }
}