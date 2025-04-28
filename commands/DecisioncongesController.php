<?php

namespace app\commands;

use app\models\Categorie;
use app\models\Civilite;
use app\models\Contrat;
use app\models\Direction;
use app\models\Echellon;
use app\models\Emploi;
use app\models\Etablissement;
use app\models\Loges;
use app\models\Service;
use app\models\Sitmat;
use yii\console\Controller;
use yii\console\ExitCode;
use Yii;
use app\models\Decisionconges;
use app\models\Parametre;
use app\models\Employe;
use app\models\Departements;
use app\models\Jouissance;

class DecisioncongesController extends Controller
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

    /*
    public function actionImportOld() {

        $exercice = 2025;

        $setting = Parametre::findOne(1);

        $uploaddir = '../web/uploads/'; $file_name = "sygec2.xls";

        $uploadfile2 = $uploaddir . basename($_FILES['fichier']['name']);


        // lecture du fichier

        @ini_set("memory_limit","5096M");

        require_once(Yii::getAlias('@vendor/phpexcel/php-excel-reader/excel_reader2.php'));

        require_once(Yii::getAlias('@vendor/phpexcel/SpreadsheetReader.php'));

        $Spreadsheet = new \SpreadsheetReader('../web/uploads/'.$_FILES['fichier']['name']);

        $BaseMem = memory_get_usage(); $errormessage = ""; $error = true;

        $Sheets = $Spreadsheet -> Sheets(); $i = 0; $tab = array();

        foreach ($Sheets as $Index => $Name)
        {

            $Spreadsheet -> ChangeSheet($Index);

            foreach ($Spreadsheet as $Key => $Row)
            {

                if($i != 0) {

                    if(!empty($Row[0])) {

                        $matricule = utf8_encode($Row[0]);
                        $civilite = isset($Row[1]) ? utf8_encode($Row[1]) : '';
                        $nom = isset($Row[2]) ? utf8_encode($Row[2]) : '';
                        $jeune = isset($Row[3]) ? utf8_encode($Row[3]) : '';
                        $prenom = isset($Row[4]) ? utf8_encode($Row[4]) : '';
                        $datnaiss = isset($Row[5]) ? utf8_encode($Row[5]) : '';
                        $statut = isset($Row[6]) ? utf8_encode($Row[6]) : '';
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
                        $debutService = isset($Row[18]) ? utf8_encode($Row[18]) : '';
                        $finService = isset($Row[19]) ? utf8_encode($Row[19]) : '';
                        $debutConge = isset($Row[20]) ? utf8_encode($Row[20]) : '';
                        $finConge = isset($Row[21]) ? utf8_encode($Row[21]) : '';

                        $ex_cat = Categorie::findOne($categorie);
                        $ex_echellon = Echellon::findOne($echelon);
                        $civ = Civilite::find()->where(["LIKE","LIBELLE",$civilite])->one();
                        $cont = Contrat::find()->where(["LIKE","CODECONT",$contrat])->one();
                        $sit = Sitmat::find()->where(["LIKE","LIBELLE",$statut])->one();
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

                        if($debutConge == null || empty(trim($debutConge))) {
                            $debutConge = null;
                        }

                        // creation ou mise a jour de l'employe

                        $user = Employe::findOne(["MATRICULE"=>$matricule]);

                        if($user != null) {

                            $user->CODECAT = $ca;
                            $user->CODEECH = $ec;
                            $user->CODEEMP = $jo;
                            $user->CODEETS = ($et1 != null) ? $et1->CODEETS : null;
                            $user->CODECIV = ($civ != null) ? $civ->CODECIV : null;
                            $user->CODECONT = ($cont != null) ? $cont->CODECONT : null;
                            $user->CODEETS_EMB = ($et2 != null) ? $et2->CODEETS : null;
                            $user->SITMAT = ($sit != null) ? $sit->CODESIT: null;
                            if(!empty($nom)) $user->NOM = $nom;
                            if(!empty($prenom)) $user->PRENOM = $prenom;
                            $user->CODEDPT = $de;
                            $user->DIRECTION = $di;
                            $user->SERVICE = $se;
                            $user->ENFANT = $enfant;
                            $user->DATNAISS = $datnaiss;
                            $user->DEPLACE = ($affectation != $embauche) ? 1 : 0;
                            $user->STATUT = 1;
                            if(!empty($jeune)) $user->JEUNEFILLE = $jeune;
                            $user->save(false);
                        }

                        else if($user == null && !empty($nom) && !empty($categorie) && !empty($echelon) && !empty($fonction) && !empty($contrat) && !empty($statut) && !empty($date_embauche) && !empty($affectation) && !empty($embauche) && !empty($direction) && !empty($departement) && !empty($service)) {

                            $emp = new Employe();

                            $emp->MATRICULE = $matricule;
                            $emp->CODECAT = ($ex_cat != null) ? $ex_cat->CODECAT : null;
                            $emp->CODEECH = ($ex_echellon != null) ? $ex_echellon->CODEECH : null;
                            $emp->CODEEMP = $jo;
                            $emp->CODEETS = ($et1 != null) ? $et1->CODEETS : null;
                            $emp->CODECIV = ($civ != null) ? $civ->CODECIV : null;
                            $emp->CODECONT = ($cont != null) ? $cont->CODECONT : null;
                            $emp->CODEETS_EMB = ($et2 != null) ? $et2->CODEETS : null;
                            $emp->NOM = $nom;
                            $emp->PRENOM = $prenom;
                            $emp->DATEEMBAUCHE = $date_embauche;
                            $emp->SOLDECREDIT = 0;
                            $emp->SOLDEAVANCE = 0;
                            $emp->CODEDPT = $de;
                            $emp->LASTCONGE = null;
                            $emp->DEPLACE = ($affectation != $embauche) ? 1 : 0;
                            $emp->DATNAISS = $datnaiss;
                            $emp->STATUT = 1;
                            $emp->DIRECTION = $di;
                            $emp->RH = 0;
                            $emp->ENFANT = $enfant;
                            $emp->SITMAT = ($sit != null) ? $sit->CODESIT: null;
                            $emp->JEUNEFILLE = $jeune;
                            $emp->VILLE = null;
                            $emp->SERVICE = $se;
                            $emp->SOLDEAVANCE2 = 0;
                            $emp->save(false);

                        }

                        // creation ou mise a jour de la decision

                        $decision = Decisionconges::find(["MATICULE" => $matricule,  "ANNEEEXIGIBLE" => $exercice])->one();

                        if($decision != null) {
                            Jouissance::deleteAll(["IDDECISION" => $decision->ID_DECISION]);
                            $decision->delete();
                        }

                        $numero = $this->getDecisionNumber($exercice) . " - " . $setting->SUFFIXEREF . "" . Yii::$app->user->identity->INITIAL;
                        $date_reprise = date('Y-m-d', strtotime($finConge. ' + 1 days'));

                        $date1 = strtotime($debutConge); $date2 = strtotime($finConge);
                        $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

                        $decision = new Decisionconges();
                        $decision->MATICULE = $matricule;
                        $decision->ANNEEEXIGIBLE = $exercice;
                        $decision->REF_DECISION = $numero;
                        $decision->DEBUTPERIODE = $debutService;
                        $decision->FINPERIODE = $finService;
                        $decision->DEBUTPLANIF = $debutConge;
                        $decision->FINPLANIF = $finConge;
                        $decision->DEBUTREELL = $debutConge;
                        $decision->FINREEL = $finConge;
                        $decision->STATUT = "V";
                        $decision->DATEEMIS = date("Y-m-d H:i:s");
                        $decision->DATEVAL = date("Y-m-d H:i:s");
                        $decision->DATEREPRISE = $date_reprise;
                        $decision->NBJOUR = $nbjour;
                        $decision->IDUSER =  Yii::$app->user->identity->IDUSER;
                        $decision->save(false);

                        echo  "Generation de la decision ".$decision->ID_DECISION." \n";

                    }
                }

                $i++;
            }
        }

        return ExitCode::OK;
    } */



    function getDecisionNumber($exercice){

        $decision = Decisionconges::find()->where(['ANNEEEXIGIBLE'=>$exercice])->all();

        $total = count($decision); $next = $total + 1;

        $position = 4 - strlen($next);

        $retour = "";

        for($i=1; $i<=$position; $i++){

            $retour.="0";
        }

        return $retour."".$next;
    }

    public function actionImport($filePath, $exercice) {

        echo "Debut du traitement\n\r";

        if (!file_exists($filePath)) {
            return "File not found: $filePath";
        }

        echo "Lecture parametre \n\r";

        $setting = Parametre::findOne(1);

        echo "Ouverture du fichier\n\r";

        $fileHandler=fopen($filePath,'r');

        if($fileHandler) {

            echo "Debut du lecture\n\r";

            $i = 0;

            while (($data = fgetcsv($fileHandler, 1000, ";")) !== FALSE) {

                if ($i != 0) {

                    if (!empty($data[0])) {

                        echo "ligne " . $data[0] . "\n\r\n\r";

                        $matricule = $data[0];
                        $civilite = isset($data[1]) ? $data[1] : '';
                        $nom = isset($data[2]) ? $data[2] : '';
                        $jeune = isset($data[3]) ? $data[3] : '';
                        $prenom = isset($data[4]) ?  $data[4] : '';
                        $datnaiss = isset($data[5]) ? $data[5] : '';
                        $sitmat = isset($data[6]) ? $data[6] : '';
                        $enfant = isset($data[7]) ? $data[7] : '';
                        $date_embauche = isset($data[8]) ? $data[8] : '';
                        $affectation = isset($data[9]) ? $data[9] : '';
                        $embauche = isset($data[10]) ? $data[10] : '';
                        $direction = isset($data[11]) ? $data[11] : '';
                        $departement = isset($data[12]) ? $data[12] : '';
                        $service = isset($data[13]) ? $data[13] : '';
                        $fonction = isset($data[14]) ? $data[14] : '';
                        $categorie = isset($data[15]) ? $data[15] : '';
                        $echelon = isset($data[16]) ? $data[16] : '';
                        $contrat = isset($data[17]) ? $data[17] : '';
                        $debutService = isset($data[18]) ? $data[18] : '';
                        $finService = isset($data[19]) ? $data[19] : '';
                        $debutConge = isset($data[20]) ? $data[20] : '';
                        $finConge = isset($data[21]) ? $data[21] : '';

                        $ex_cat = Categorie::findOne($categorie);
                        $ex_echellon = Echellon::findOne($echelon);
                        //$civ = Civilite::findOne($civilite);
                        $cont = Contrat::find()->where(["LIKE", "CODECONT", $contrat])->one();
                        //$sit = Sitmat::findOne($statut);
                        $et1 = Etablissement::find()->where(["CODEETS" => $affectation])->one();
                        $et2 = Etablissement::find()->where(["CODEETS" => $embauche])->one();
                        $job = Emploi::find()->where(["LIKE", "LIBELLE", $fonction])->one();
                        $dir = Direction::find()->where(["LIKE", "LIBELLE", $direction])->one();
                        $sev = Service::find()->where(["LIKE", "LIBELLE", $service])->one();
                        $dep = Departements::find()->where(["LIKE", "LIBELLE", $departement])->one();

                        $de = null;
                        $di = null;
                        $se = null;
                        $jo = null;
                        $ec = null;
                        $ca = null;

                        if ($ex_cat == null && !empty($categorie)) {
                            $ex_cat = new Categorie();
                            $ex_cat->CODECAT = $categorie;
                            $ex_cat->LIBELLE = $categorie;
                            $ex_cat->save(false);
                            $ca = $ex_cat->CODECAT;
                        } else if ($ex_cat != null) $ca = $ex_cat->CODECAT;

                        if ($job == null && !empty(trim($fonction))) {
                            $job = new Emploi();
                            $job->LIBELLE = $fonction;
                            $job->save(false);
                            $jo = $job->CODEEMP;
                        } else if ($job != null) $jo = $job->CODEEMP;

                        if ($ex_echellon == null && !empty($echelon)) {
                            $ex_echellon = new Echellon();
                            $ex_echellon->CODEECH = $echelon;
                            $ex_echellon->LIBELLE = $echelon;
                            $ex_echellon->save(false);
                            $ec = $ex_echellon->CODEECH;
                        } else if ($ex_echellon != null) $ec = $ex_echellon->CODEECH;

                        if ($dir == null && !empty(trim($direction))) {
                            $dir = new Direction();
                            $dir->LIBELLE = $direction;
                            $dir->save(false);
                            $di = $dir->ID;
                        } else if ($dir != null) $di = $dir->ID;

                        if ($dep == null && !empty(trim($departement))) {
                            $dep = new Departements();
                            $dep->LIBELLE = $departement;
                            $dep->save(false);
                            $de = $dep->CODEDPT;
                        } else if ($dep != null) $de = $dep->CODEDPT;

                        if ($sev == null && !empty($service)) {
                            $sev = new Service();
                            $sev->LIBELLE = $service;
                            $sev->save(false);
                            $se = $sev->ID;
                        } else if ($sev != null) $se = $sev->ID;

                        if (strlen($matricule) < 5) {
                            $prefix = "";
                            for ($i = 1; $i <= (5 - strlen($matricule)); $i++) {
                                $prefix .= "0";
                            }
                            $matricule = $prefix . "" . $matricule;
                        }

                        if ($debutConge == null || empty(trim($debutConge))) {
                            $debutConge = null;
                        }

                        // creation ou mise a jour de l'employe

                        echo "Debut creation du conges \n\r";

                        if($debutConge != null && !empty(trim($debutConge)) && $finConge != null && !empty(trim($finConge)) && ($debutConge != "*") && ($finConge != "*")) {

                            echo "Condition conges ok \n\r";

                            $user = Employe::findOne(["MATRICULE" => $matricule]);

                            if ($user != null) {

                                echo "Employe existant ".$nom." ".$prenom."  \n\r";

                                $user->CODECAT = $ca;
                                $user->CODEECH = $ec;
                                $user->CODEEMP = $jo;
                                $user->CODEETS = ($et1 != null) ? $et1->CODEETS : null;
                                $user->CODECIV = $civilite;
                                $user->CODECONT = ($cont != null) ? $cont->CODECONT : null;
                                $user->CODEETS_EMB = ($et2 != null) ? $et2->CODEETS : null;
                                $user->SITMAT = $sitmat;
                                if (!empty($nom)) $user->NOM = $nom;
                                if (!empty($prenom)) $user->PRENOM = $prenom;
                                $user->CODEDPT = $de;
                                $user->DIRECTION = $di;
                                $user->SERVICE = $se;
                                $user->ENFANT = $enfant;
                                $user->DATNAISS = $datnaiss;
                                $user->DEPLACE = ($affectation != $embauche) ? 1 : 0;
                                $user->STATUT = 1;
                                if (!empty($jeune)) $user->JEUNEFILLE = $jeune;
                                $user->save(false);
                            } else {

                                echo "Nouvel employe ".$nom." ".$prenom." \n\r";

                                $emp = new Employe();

                                $emp->MATRICULE = $matricule;
                                $emp->CODECAT = ($ex_cat != null) ? $ex_cat->CODECAT : null;
                                $emp->CODEECH = ($ex_echellon != null) ? $ex_echellon->CODEECH : null;
                                $emp->CODEEMP = $jo;
                                $emp->CODEETS = ($et1 != null) ? $et1->CODEETS : null;
                                $emp->CODECIV = $civilite;
                                $emp->CODECONT = ($cont != null) ? $cont->CODECONT : null;
                                $emp->CODEETS_EMB = ($et2 != null) ? $et2->CODEETS : null;
                                $emp->NOM = $nom;
                                $emp->PRENOM = $prenom;
                                $emp->DATEEMBAUCHE = $date_embauche;
                                $emp->SOLDECREDIT = 0;
                                $emp->SOLDEAVANCE = 0;
                                $emp->CODEDPT = $de;
                                $emp->LASTCONGE = null;
                                $emp->DEPLACE = ($affectation != $embauche) ? 1 : 0;
                                $emp->DATNAISS = $datnaiss;
                                $emp->STATUT = 1;
                                $emp->DIRECTION = $di;
                                $emp->RH = 0;
                                $emp->ENFANT = $enfant;
                                $emp->SITMAT = $sitmat;
                                $emp->JEUNEFILLE = $jeune;
                                $emp->VILLE = null;
                                $emp->SERVICE = $se;
                                $emp->SOLDEAVANCE2 = 0;
                                $emp->save(false);

                            }

                            // creation ou mise a jour de la decision

                            echo "Recherche decision \n\r";

                            $decision1 = Decisionconges::find()->where(["MATICULE" => $matricule, "ANNEEEXIGIBLE" => $exercice])->all();

                            if ($decision1 != null) {

                                echo "Debut suppression decision existante \n\r";

                                foreach($decision1 as $dec) {
                                    $jouissance = Jouissance::find()->where(['IDDECISION' => $dec->ID_DECISION])->all();
                                    if ($jouissance != null) {
                                        foreach ($jouissance as $j) {
                                            echo "Suppression jouissance existante \n\r";
                                            $j->delete();
                                        }
                                    }
                                    echo "Suppression decision existante \n\r";
                                    $dec->delete();
                                }
                            }

                            $numero = $this->getDecisionNumber($exercice) . " - " . $setting->SUFFIXEREF . "adm";
                            $date_reprise = date('Y-m-d', strtotime($finConge . ' + 1 days'));

                            $date1 = strtotime($debutConge);
                            $date2 = strtotime($finConge);
                            $diff = $date2 - $date1;
                            $nbjour = abs(round($diff / 86400)) + 1;


                            echo "Creation nouvelle decision \n\r";

                            $decision = new Decisionconges();
                            $decision->MATICULE = $matricule;
                            $decision->ANNEEEXIGIBLE = $exercice;
                            $decision->REF_DECISION = $numero;
                            $decision->DEBUTPERIODE = $debutService;
                            $decision->FINPERIODE = $finService;
                            $decision->DEBUTPLANIF = $debutConge;
                            $decision->FINPLANIF = $finConge;
                            $decision->DEBUTREELL = $debutConge;
                            $decision->FINREEL = $finConge;
                            $decision->STATUT = "V";
                            $decision->DATEEMIS = date("Y-m-d H:i:s");
                            $decision->DATEVAL = date("Y-m-d H:i:s");
                            $decision->DATEREPRISE = $date_reprise;
                            $decision->NBJOUR = $nbjour;
                            $decision->IDUSER = 1;
                            $decision->FICHIER = 2;

                            if($decision->save(false)) {
                                echo "Generation de la decision " . $decision->ID_DECISION . " \n";
                            }
                            else {
                                echo "Erreur " . $decision->getErrors() . " \n";
                            }

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
                    ->setSubject("Import des decisions de conges")
                    ->setHtmlBody("Import des decisions de conges effectue avec succes")
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
}