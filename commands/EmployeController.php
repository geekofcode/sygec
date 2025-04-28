<?php

namespace app\commands;

use app\models\Categorie;
use app\models\Contrat;
use app\models\Departements;
use app\models\Direction;
use app\models\Echellon;
use app\models\Emploi;
use app\models\Employe;
use app\models\Etablissement;
use app\models\Service;
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

    public function actionImport($filePath) {

        if (!file_exists($filePath)) {
            return "File not found: $filePath";
        }

        $fileHandler=fopen($filePath,'r');

        if($fileHandler) {

            $i = 0;

            while (($Row = fgetcsv($fileHandler, 1000, ";")) !== FALSE) {

                if($i != 0) {

                    if(!empty($Row[0])) {

                        $matricule = utf8_encode($Row[0]);
                        $civ = isset($Row[1]) ? utf8_encode($Row[1]) : null;
                        $nom = isset($Row[2]) ? utf8_encode($Row[2]) : '';
                        $jeune = isset($Row[3]) ? utf8_encode($Row[3]) : '';
                        $prenom = isset($Row[4]) ? utf8_encode($Row[4]) : '';
                        $datnaiss = isset($Row[5]) ? utf8_encode($Row[5]) : '';
                        $sit = isset($Row[6]) ? utf8_encode($Row[6]) : null;
                        $enfant = isset($Row[7]) ? utf8_encode($Row[7]) : '';
                        $date_embauche = isset($Row[8]) ? utf8_encode($Row[8]) : '';
                        $affectation = isset($Row[9]) ? utf8_encode($Row[9]) : '';
                        $embauche = isset($Row[10]) ? utf8_encode($Row[10]) : '';
                        $direction = isset($Row[11]) ? utf8_encode($Row[11]) : '';
                        $departement = isset($Row[12]) ? utf8_encode($Row[12]) : '';
                        $service = isset($Row[13]) ? utf8_encode($Row[13]) : '';
                        $fonction = isset($Row[14]) ? utf8_encode($Row[14]) : '';
                        $categorie = isset($Row[15]) ? utf8_encode($Row[15]) : '';
                        $echelon = isset($Row[16]) ? utf8_encode($Row[16]) : '';
                        $contrat = isset($Row[17]) ? utf8_encode($Row[17]) : '';
                        $calcul = isset($Row[18]) ? utf8_encode($Row[18]) : '';

                        $ex_cat = Categorie::findOne($categorie);
                        $ex_echellon = Echellon::findOne($echelon);
                        //$civ = Civilite::find()->where(["LIKE","LIBELLE",$civilite])->one();
                        $cont = Contrat::find()->where(["LIKE","CODECONT",$contrat])->one();
                        //$sit = Sitmat::find()->where(["LIKE","LIBELLE",$statut])->one();
                        $et1 = Etablissement::find()->where(["CODEETS" => $affectation])->one();
                        $et2 = Etablissement::find()->where(["CODEETS" => $embauche])->one();
                        $job = Emploi::find()->where(["LIKE","LIBELLE",$fonction])->one();
                        $dir = Direction::findOne($direction);
                        $sev = Service::findOne($service);
                        $dep = Departements::findOne($departement);

                        $de = null; $di = null; $se = null; $jo = null; $ec = null; $ca = null;


                        if($ex_cat == null && !empty($categorie)) {
                            $ex_cat = new Categorie();
                            $ex_cat->CODECAT = $categorie;
                            $ex_cat->LIBELLE = $categorie;
                            $ex_cat->save(false);
                            $ca = $ex_cat->CODECAT;
                        } else if($ex_cat != null) $ca = $ex_cat->CODECAT;

                        if($job == null && !empty(trim($fonction))) {
                            $job = new Emploi();
                            $job->LIBELLE = $fonction;
                            $job->save(false);
                            $jo = $job->CODEEMP;
                        } else if($job != null) $jo = $job->CODEEMP;

                        if($ex_echellon == null && !empty($echelon)) {
                            $ex_echellon = new Echellon();
                            $ex_echellon->CODEECH = $echelon;
                            $ex_echellon->LIBELLE = $echelon;
                            $ex_echellon->save(false);
                            $ec = $ex_echellon->CODEECH;
                        } else if($ex_echellon != null) $ec = $ex_echellon->CODEECH;

                        if($dir == null && !empty(trim($direction))) {
                            $dir = new Direction();
                            $dir->LIBELLE = $direction;
                            $dir->save(false);
                            $di = $dir->ID;
                        } else if($dir != null) $di = $dir->ID;

                        if($dep == null && !empty(trim($departement))) {
                            $dep = new Departements();
                            $dep->LIBELLE = $departement;
                            $dep->save(false);
                            $de = $dep->CODEDPT;
                        } else if($dep != null) $de = $dep->CODEDPT;

                        if($sev == null && !empty($service)) {
                            $sev = new Service();
                            $sev->LIBELLE = $service;
                            $sev->save(false);
                            $se = $sev->ID;
                        } else if($sev != null) $se = $sev->ID;

                        if(strlen($matricule) < 5) {
                            $prefix = "";
                            for($i = 1; $i<= (5 - strlen($matricule)); $i++) {
                                $prefix.="0";
                            }
                            $matricule = $prefix."".$matricule;
                        }

                        if($calcul == null || empty(trim($calcul))) {
                            $calcul = null;
                        }

                        $user = Employe::findOne(["MATRICULE"=>$matricule]);

                        if($user != null) {

                            $user->CODECAT = $ca;
                            $user->CODEECH = $ec;
                            $user->CODEEMP = $jo;
                            $user->CODEETS = ($et1 != null) ? $et1->CODEETS : null;
                            $user->CODECIV = $civ;
                            $user->CODECONT = ($cont != null) ? $cont->CODECONT : null;
                            $user->CODEETS_EMB = ($et2 != null) ? $et2->CODEETS : null;
                            $user->SITMAT = $sit;
                            if(!empty($nom)) $user->NOM = $nom;
                            if(!empty($prenom)) $user->PRENOM = $prenom;
                            $user->CODEDPT = $de;
                            $user->DIRECTION = $di;
                            $user->SERVICE = $se;
                            $user->ENFANT = $enfant;
                            $user->DATECALCUL = $calcul;
                            $user->DATNAISS = $datnaiss;
                            $user->DEPLACE = ($affectation != $embauche) ? 1 : 0;
                            $user->STATUT = 1;
                            if(!empty($jeune)) $user->JEUNEFILLE = $jeune;
                            $user->save(false);
                        }

                        else if($user == null && !empty($nom) && !empty($categorie) && !empty($echelon) && !empty($fonction) && !empty($contrat) && !empty($date_embauche) && !empty($affectation) && !empty($embauche) && !empty($direction) && !empty($departement) && !empty($service)) {

                            $emp = new Employe();

                            $emp->MATRICULE = $matricule;
                            $emp->CODECAT = ($ex_cat != null) ? $ex_cat->CODECAT : null;
                            $emp->CODEECH = ($ex_echellon != null) ? $ex_echellon->CODEECH : null;
                            $emp->CODEEMP = $jo;
                            $emp->CODEETS = ($et1 != null) ? $et1->CODEETS : null;
                            $emp->CODECIV = $civ;
                            $emp->CODECONT = ($cont != null) ? $cont->CODECONT : null;
                            $emp->CODEETS_EMB = ($et2 != null) ? $et2->CODEETS : null;
                            $emp->NOM = $nom;
                            $emp->PRENOM = $prenom;
                            $emp->DATEEMBAUCHE = $date_embauche;
                            $emp->SOLDECREDIT = 0;
                            $emp->SOLDEAVANCE = 0;
                            $emp->DATECALCUL = $calcul;
                            $emp->CODEDPT = $de;
                            $emp->LASTCONGE = null;
                            $emp->DEPLACE = ($affectation != $embauche) ? 1 : 0;
                            $emp->DATNAISS = $datnaiss;
                            $emp->STATUT = 1;
                            $emp->DIRECTION = $di;
                            $emp->RH = 0;
                            $emp->ENFANT = $enfant;
                            $emp->SITMAT = $sit;
                            $emp->JEUNEFILLE = $jeune;
                            $emp->VILLE = null;
                            $emp->SERVICE = $se;
                            $emp->SOLDEAVANCE2 = 0;
                            $emp->save(false);

                        }
                    }
                }

                $i++;
            }


            try{

                //"tsumbang@gmail.com",

                \Yii::$app
                    ->mailer->compose()
                    ->setFrom([\Yii::$app->params['supportEmail'] => 'SYGEC'])
                    ->setTo(["emmanuel.gbetkom@adcsa.aero","nathalie.aboug@adcsa.aero","sandra.ayem@adcsa.aero","guillaume.bissek@adcsa.aero","pierrette.atcham@adcsa.aero"])
                    ->setSubject("Import des employes")
                    ->setHtmlBody("Import des employes effectue avec succes")
                    ->send();

                echo "Export ok \n";

            }

            catch(\Swift_SwiftException $exception) {
                echo $exception->getMessage()." \n";
            }

            catch(\Exception $exception){
                echo $exception->getMessage()." \n";
            }
        } else {
            echo "Erreur d'ouverture du fichier \n";
        }

        return ExitCode::OK;
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