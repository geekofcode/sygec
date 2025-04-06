<?php

namespace app\commands;

use app\models\Direction;
use app\models\Emploi;
use app\models\Employe;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class EmployeController extends Controller
{

    public function dates($data) {
        $d = explode("/",$data);
        return $d[2]."-".$d[1]."-".$d[0];
    }

    public function lieu($place) {
        if($place == "Douala") return "DLA";
        else if($place == "Nsimalen") return "NSI";
        else if($place == "Garoua") return "GOU";
        else if($place == "Douala") return "DLA";
        else if($place == "Ngaoundéré") return "NGE";
        else if($place == "Bamenda") return "BDA";
        else if($place == "Maroua") return "MVR";
        else if($place == "Bertoua") return "BTA";

    }

    public function convertDate($jour) {
        $tab = explode(" ",$jour);
        return date('Y-m-d',strtotime($tab[0]));
    }

    public function actionIndex()
    {

        $fileHandler=fopen(Yii::getAlias('@webfile'). DIRECTORY_SEPARATOR."conges.csv",'r');

        if($fileHandler){

           // echo "Ligne \n\r";

            while($line=fgetcsv($fileHandler,1000)){

                echo "ligne ".$line[0]."\n\r\n\r";

                $tab = explode(";",$line[0]);

                $matricule = $tab[0];

                if(strlen($matricule) < 5) {
                    for($i = 0; $i < (5 - strlen($tab[0])); $i++) {
                        $matricule = "0".$matricule;
                    }
                }

               /// echo "0".$matricule."\n\r";

                $tab_exclus = array("02148","00662","02258","01156","02497","02505","00577","02344","00347","01747","02175","02017","00817","01866","01504","01769","00190","01579","00383","02440","02180","00946","00956","01961","02032","02031","01585","01563","00872","00387");

                //if(in_array($matricule,$tab_exclus)) {

                   $exist = Employe::findOne(["MATRICULE" => $matricule]);

                    if($exist == null) {

                      //  echo "Debut ".$matricule."\n\r";

                      //  echo "ligne ".$line[0]."\n\r\n\r";

                        if (array_key_exists(13,$tab)) {

                            $tab_categorie = explode(" ",$tab[13]);
                            $categorie = $tab_categorie[0]; $echellon = $tab_categorie[1];

                            $emploi = utf8_encode($tab[9]); $direction = $tab[10];
                            $direction = utf8_encode($direction);

                            $job = Emploi::find()->where(["LIKE","LIBELLE",$emploi])->one();
                            if($job == null) {
                                $job = new Emploi();
                                $job->LIBELLE = $emploi;
                                $job->save(false);
                            }

                            $direct = Direction::find()->where(["LIKE","LIBELLE",$direction])->one();
                            if($direct == null) {
                                $direct = new Direction();
                                $direct->LIBELLE = $direction;
                                $direct->save(false);
                            }

                            $employe = new Employe();
                            $employe->MATRICULE = $matricule;
                            $employe->CODECAT = $categorie;
                            $employe->CODEECH = $echellon;
                            $employe->CODEEMP = $job->CODEEMP;
                            $employe->CODEETS = $tab[8];
                            $employe->CODECIV = $tab[2];
                            $employe->CODECONT = $tab[14];
                            $employe->CODEETS_EMB  = $tab[12];
                            $employe->NOM = utf8_encode($tab[3]);
                            $employe->PRENOM = utf8_encode($tab[4]);
                            $employe->DATEEMBAUCHE = $tab[6];
                            $employe->SOLDEAVANCE = 0.0;
                            $employe->SITMAT = "00".$tab[7];
                            $employe->SOLDECREDIT = 0.0;
                            $employe->DATECALCUL = date('Y-m-d',strtotime($tab[24]));
                            $employe->LASTCONGE =  date('Y-m-d',strtotime($tab[23].' -1 day'));
                            $employe->DEPLACE = ($tab[8] != $tab[12]) ? 1 : 0;
                            $employe->DATNAISS = date('Y-m-d',strtotime($tab[4]));
                            $employe->STATUT = 1;
                            $employe->DIRECTION = $direct->ID;
                            $employe->save(false);

                            echo "Enregistrement ".$employe->MATRICULE." - ".$employe->NOM." ".$employe->PRENOM."\n\r";

                        }

                        else {
                            echo "non enregistrement ".$matricule." ".count($tab)."\n\r";
                        }
                    }

            //    }

            }
        }

        return ExitCode::OK;

    }

    public function actionIndex2()
    {

        $fileHandler=fopen(Yii::getAlias('@webfile'). DIRECTORY_SEPARATOR."conges3.csv",'r');

        if($fileHandler){

            // echo "Ligne \n\r";

            while($tab = fgetcsv($fileHandler,1000)){

                echo "ligne ".$tab[10]."\n\r\n\r";

              //  $tab = $ligne;

                $matricule = $tab[0];

                if(strlen($matricule) < 5) {
                    for($i = 0; $i < (5 - strlen($tab[0])); $i++) {
                        $matricule = "0".$matricule;
                    }
                }

                /// echo "0".$matricule."\n\r";

                $tab_exclus = array("02148","00662","02258","01156","02497","02505","00577","02344","00347","01747","02175","02017","00817","01866","01504","01769","00190","01579","00383","02440","02180","00946","00956","01961","02032","02031","01585","01563","00872","00387");

                //if(in_array($matricule,$tab_exclus)) {

                $exist = Employe::findOne(["MATRICULE" => $matricule]);

                if($exist == null) {

                    //  echo "Debut ".$matricule."\n\r";

                    //  echo "ligne ".$line[0]."\n\r\n\r";

                    if (array_key_exists(13,$tab)) {

                        $tab_categorie = explode(" ",$tab[13]);
                        $categorie = $tab_categorie[0]; $echellon = $tab_categorie[1];

                        $emploi = utf8_encode($tab[9]); $direction = $tab[10];
                        $direction = utf8_encode($direction);

                        $job = Emploi::find()->where(["LIKE","LIBELLE",$emploi])->one();
                        if($job == null) {
                            $job = new Emploi();
                            $job->LIBELLE = $emploi;
                            $job->save(false);
                        }

                        $direct = Direction::find()->where(["LIKE","LIBELLE",$direction])->one();
                        if($direct == null) {
                            $direct = new Direction();
                            $direct->LIBELLE = $direction;
                            $direct->save(false);
                        }

                        $employe = new Employe();
                        $employe->MATRICULE = $matricule;
                        $employe->CODECAT = $categorie;
                        $employe->CODEECH = $echellon;
                        $employe->CODEEMP = $job->CODEEMP;
                        $employe->CODEETS = $tab[8];
                        $employe->CODECIV = $tab[2];
                        $employe->CODECONT = $tab[14];
                        $employe->CODEETS_EMB  = $tab[12];
                        $employe->NOM = $tab[3];
                        $employe->PRENOM = $tab[4];
                        $employe->DATEEMBAUCHE = $tab[6];
                        $employe->SOLDEAVANCE = 0.0;
                        $employe->SITMAT = $tab[7];
                        $employe->SOLDECREDIT = 0.0;
                        $employe->DATECALCUL = date('Y-m-d',strtotime($tab[24]));
                        $employe->LASTCONGE =  date('Y-m-d',strtotime($tab[23].' -1 day'));
                        $employe->DEPLACE = ($tab[8] != $tab[12]) ? 1 : 0;
                        $employe->DATNAISS = date('Y-m-d',strtotime($tab[5]));
                        $employe->STATUT = 1;
                        $employe->DIRECTION = $direct->ID;
                        $employe->save(false);

                        echo "Enregistrement ".$employe->MATRICULE." - ".$employe->NOM." ".$employe->PRENOM."\n\r";

                    }

                    else {
                        echo "non enregistrement ".$matricule." ".count($tab)."\n\r";
                    }
                }

                //    }

            }
        }

        return ExitCode::OK;

    }

    public function actionImport() {

        require_once(Yii::getAlias('@vendor/phpexcel/php-excel-reader/excel_reader2.php'));

        require_once(Yii::getAlias('@vendor/phpexcel/SpreadsheetReader.php'));

        $Spreadsheet = new \SpreadsheetReader(Yii::getAlias('@webfile'). DIRECTORY_SEPARATOR."conges.xls");

        @ini_set("memory_limit","5096M");

        $BaseMem = memory_get_usage(); $errormessage = ""; $error = true;

        $Sheets = $Spreadsheet -> Sheets(); $i = 0; $tab = array();

        foreach ($Sheets as $Index => $Name)
        {
            $Spreadsheet -> ChangeSheet($Index);

            foreach ($Spreadsheet as $Key => $Row)
            {
                if($i > 0) {

                    $matricule = $Row[0];

                    $exist = Employe::findOne(["MATRICULE" => $matricule]);

                    if($exist == null) {

                     ///   $tab_categorie = explode(" ",$Row[13]);
                        $categorie = substr($Row[13],0,1); $echellon = substr($Row[13],1,1);

                        $emploi = utf8_encode($Row[9]); $direction = $Row[10];
                        $direction = utf8_encode($direction);

                        $job = Emploi::find()->where(["LIKE","LIBELLE",$emploi])->one();
                        if($job == null) {
                            $job = new Emploi();
                            $job->LIBELLE = $emploi;
                            $job->save(false);
                        }

                        $direct = Direction::find()->where(["LIKE","LIBELLE",$direction])->one();
                        if($direct == null) {
                            $direct = new Direction();
                            $direct->LIBELLE = $direction;
                            $direct->save(false);
                        }

                        if($Row[2] == "Monsieur") $civ = "MR";
                        else if($Row[2] == "Mademoiselle") $civ = "MLLE";
                        else if($Row[2] == "Madame") $civ = "MME";

                        if($Row[7] == "Célibataire") $sit = "1";
                        else if($Row[7] == "Marié(e)") $sit = "2";
                        else if($Row[7] == "Veuf(ve)") $sit = "3";
                        else if($Row[7] == "Divorcé(e)") $sit = "4";

                        $emp = $this->lieu($Row[8]);
                        $emb = $this->lieu($Row[12]);

                        $employe = new Employe();
                        $employe->MATRICULE = $matricule;
                        $employe->CODECAT = $categorie;
                        $employe->CODEECH = $echellon;
                        $employe->CODEEMP = $job->CODEEMP;
                        $employe->CODEETS = $emp;
                        $employe->CODECIV = $civ;
                        $employe->CODECONT = $Row[14];
                        $employe->CODEETS_EMB  = $emb;
                        $employe->NOM = utf8_encode($Row[3]);
                        $employe->PRENOM = utf8_encode($Row[4]);
                        $employe->DATEEMBAUCHE = $this->convertDate($Row[6]);
                        $employe->SOLDEAVANCE = 0.0;
                        $employe->SITMAT = "00".$sit;
                        $employe->SOLDECREDIT = 0.0;
                        $employe->DATECALCUL = $this->convertDate($Row[24]);
                        $employe->LASTCONGE =  date('Y-m-d',strtotime($Row[18]));
                        $employe->DEPLACE = ($emb != $emp) ? 1 : 0;
                        $employe->DATNAISS = $this->convertDate($Row[5]);
                        $employe->STATUT = 1;
                        $employe->DIRECTION = $direct->ID;
                        $employe->save(false);

                        echo "Enregistrement ".$employe->MATRICULE." - ".$employe->NOM." ".$employe->PRENOM." ".$Row[5]."\n\r";

                    }


                }

                $i++;
            }

        }

    }

    public function actionMailer() {
        try {

            \Yii::$app
                ->mailer->compose()
                ->setFrom(['noreply@sygec.cm' => 'SYGEC'])
                ->setTo("tsumbang@gmail.com")
                ->setSubject("test")
                ->setHtmlBody("Hello Wolrd")
                ->send();
        } catch (\Swift_SwiftException $exception) {
        } catch (\Exception $exception) {
        }
    }

}