<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\controllers\Generator;
use app\models\Exercice;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }

    public function actionGenerate() {

        $decisions = \app\models\Decisionconges::find()->where(["FICHIER" => 0])->orderBy(["ID_DECISION"=>SORT_DESC])->all();

        $zip = new \ZipArchive();   $repertoire = './web/uploads';
        $name = round(microtime(true)).".zip";
        $destination = $repertoire."/".$name;
        if($zip->open($destination,\ZIPARCHIVE::CREATE) !== true) {
            return false;
        }

        foreach ($decisions as $decision) {

            $filename = Generator::decision2($decision->ID_DECISION);

            if(empty($filename)) echo "Model de déicions ".$decision->ID_DECISION." vide : ".$filename." \n";

            else {

                echo "Model de déicions ".$decision->ID_DECISION." généré avec succès : ".$filename. "\n";

                $cheminf = $repertoire."/".$filename;

                $zip->addFile($cheminf,$filename);
            }
        }

        $zip->close();

        $content = '<!Doctype html>

        <html>

        <head>

        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />

        </head>

        <body style="font-family: Calibri; font-size: 14px">

        <div>

            <div style="line-height: 20px">

            <span>Bonjour Monsieur/Madame , <br><br>Bien vouloir trouver ci-joint les modèles d\'éditions de décisions..</span><br><br>

         <br><br>Bien &agrave; vous.</span><br><br>       

            </div>

        </div>

        </body>

        </html>';

        try{

            //"tsumbang@gmail.com",

            \Yii::$app
                ->mailer->compose()
                ->setFrom([\Yii::$app->params['supportEmail'] => 'SYGEC'])
                ->setTo(["emmanuel.gbetkom@adcsa.aero","nathalie.aboug@adcsa.aero","sandra.ayem@adcsa.aero","guillaume.bissek@adcsa.aero","pierrette.atcham@adcsa.aero"])
                ->setSubject("Modèles d'éditions")
                ->setHtmlBody($content)
                ->attach($destination)
                ->send();

            echo "Export ok \n";

        }

        catch(\Swift_SwiftException $exception) {
            echo $exception->getMessage()." \n";
        }

        catch(\Exception $exception){
            echo $exception->getMessage()." \n";
        }

        return ExitCode::OK;
    }

    public function actionPermission() {

        $absens = \app\models\Absenceponctuel::find()->where(["ANNEEEXIGIBLE" => 2023,"STATUT" => "V","DEJA" => 1])->all();

        foreach ($absens as $model) {

            $exos = \app\models\Exercice::find()->where(['ANNEEEXIGIBLE'=>$model->ANNEEEXIGIBLE, 'STATUT'=>'O'])->One();

            if($exos != null) {

                echo "Début avec : ".$model->MATICULE. "\n";

                $employe = \app\models\Employe::findOne($model->MATICULE);

                if($employe != null) {

                    if($model->IMPUTERCONGES == 1) {

                        if($model->TYPE_DEMANDE == 0) {
                            $date1 = strtotime($model->DATEDEBUT); $date2 = strtotime($model->DATEFIN);
                            $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;
                            $employe->SOLDEAVANCE = $employe->SOLDEAVANCE + $nbjour;
                            $employe->save(false);
                        }
                        else {
                            $jourheureconge = (int)($model->DUREE / 8);
                            $employe->SOLDEAVANCE = $employe->SOLDEAVANCE + $jourheureconge;
                            $employe->save(false);
                        }
                    }

                    else if($model->CODEABS == "A003") {
                        $date1 = strtotime($model->DATEDEBUT); $date2 = strtotime($model->DATEFIN);
                        $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;
                        $employe->SOLDECREDIT = $employe->SOLDECREDIT - $nbjour;
                        $employe->save(false);
                    }

                }

            }

        }
    }
}
