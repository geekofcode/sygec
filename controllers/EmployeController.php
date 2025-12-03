<?php

namespace app\controllers;

use app\models\Absenceponctuel;
use app\models\Categorie;
use app\models\Civilite;
use app\models\Contrat;
use app\models\Decisionconges;
use app\models\Departements;
use app\models\Direction;
use app\models\Echellon;
use app\models\Emploi;
use app\models\Etablissement;
use app\models\Exercice;
use app\models\Historique;
use app\models\Parametre;
use app\models\Service;
use app\models\Sitmat;
use kartik\mpdf\Pdf;
use Yii;
use app\models\Employe;
use app\models\EmployeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\filters\AccessControl;

/**
 * EmployeController implements the CRUD actions for Employe model.
 */
class EmployeController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],

            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Employe models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $searchModel = new EmployeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Employe model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Employe model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new Employe();

        if ($model->load(Yii::$app->request->post())) {

            $exist = Employe::findOne($model->MATRICULE);

            if($exist != null) {

                Yii::$app->session->setFlash('error', 'un employé existe déjà avec ce matricule');

                return $this->redirect(['create']);
            }

            else {

                $setting = Parametre::findOne(1);

                $datefin = date('Y-m-d', strtotime($model->DATEEMBAUCHE. ' + '.($setting->DUREESERVICE + 1).' days'));

                $model->SOLDEAVANCE = 0;
                $model->SOLDECREDIT = 0;
                $model->LASTCONGE = null;
                $model->DATECALCUL = $datefin;

                $model->save(false);

                Yii::$app->session->setFlash('success', 'Employé enregistré avec succes.');

                return $this->redirect(['view', 'id' => $model->MATRICULE]);
            }

        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Employe model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            Yii::$app->session->setFlash('success', 'Employé modifié avec succes.');

            return $this->redirect(['view', 'id' => $model->MATRICULE]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Employe model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = $this->findModel($id);

        $decisions = Decisionconges::find()->where(["MATICULE"=>$model->MATRICULE])->all();
        $absences = Absenceponctuel::find()->where(["MATICULE"=>$model->MATRICULE])->all();

        if($decisions != null || $absences != null) {

            Yii::$app->session->setFlash('error', 'Cet employé possède des informations sur la plateforme et ne peut être supprimé');

            return $this->redirect(['index']);
        }

        else {

            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', 'Employé supprimé avec succes.');

            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the Employe model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Employe the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Employe::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionImportation(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        return $this->render('import');
    }

    public function actionImport() {

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $uploadedFile = \yii\web\UploadedFile::getInstanceByName('fichier');

        if ($uploadedFile) {
            // Définir le chemin du fichier temporaire
            $tempFilePath = Yii::getAlias('@runtime/uploads/') . $uploadedFile->name;

            // Créer le répertoire s'il n'existe pas
            if (!is_dir(Yii::getAlias('@runtime/uploads/'))) {
                mkdir(Yii::getAlias('@runtime/uploads/'), 0777, true);
            }

            // Sauvegarder le fichier uploadé
            if ($uploadedFile->saveAs($tempFilePath)) {
                try {

                    // Build the console command
                    $command = sprintf(
                        'php %s/yii employe/import "%s" ',
                        Yii::getAlias('@app'),
                        $tempFilePath
                    );

                    // Execute the command and capture the output
                    $output = [];
                    $returnVar = 0;
                    exec($command, $output, $returnVar);

                    // Delete the temporary file after processing
                    unlink($tempFilePath);

                    if ($returnVar === 0) {
                        return ['success' => true, 'message' => 'Import des employes effectue avec succes', 'output' => $output];
                    } else {
                        return ['success' => false, 'message' => 'Command execution failed ', 'output' => $output, 'returnVar' => $returnVar];
                    }

                } catch (\Exception $e) {
                    // Supprimer le fichier temporaire en cas d'erreur
                    unlink($tempFilePath);

                    return ['success' => false, 'message' => $e->getMessage()];
                }
            } else {
                return ['success' => false, 'message' => 'Failed to save the uploaded file.'];
            }
        }

        return ['success' => false, 'message' => 'No file uploaded.'];

    }

    public function actionImport2(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        if(isset($_REQUEST["imp"])){

            $uploaddir = '../web/uploads/';

            $file_name = $_FILES['fichier']['tmp_name'];

            $uploadfile2 = $uploaddir . basename($_FILES['fichier']['name']);

            move_uploaded_file($_FILES['fichier']['tmp_name'], $uploadfile2);

            // lecture du fichier

            @ini_set("memory_limit","5096M");

            require_once(Yii::getAlias('@vendor/phpexcel/php-excel-reader/excel_reader2.php'));

            require_once(Yii::getAlias('@vendor/phpexcel/SpreadsheetReader.php'));

            $Spreadsheet = new \SpreadsheetReader('../web/uploads/'.$_FILES['fichier']['name']);

            $BaseMem = memory_get_usage(); $errormessage = ""; $error = true;

            $Sheets = $Spreadsheet -> Sheets(); $i = 0; $tab = array();

            $setting = \app\models\Parametre::findOne(1); $p = 0;

            $setting = Parametre::findOne(1); $jour = date("Y-m-d");

            $builder = '<html>
<head><title>Generation projet decisions du '.$jour.' - ADC</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
<table border="1" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:1px solid #000000"><tr><td style="padding: 5px" style="padding: 5px; font-weight: bold">REFERENCE</td><td style="padding: 5px; font-weight: bold" style="padding: 5px; font-weight: bold">MATRICULE</td><td style="padding: 5px; font-weight: bold">EMPLOYE</td><td style="padding: 5px; font-weight: bold">PERIODE DE SERVICE</td><td style="padding: 5px; font-weight: bold">DEBUT CONGE</td><td style="padding: 5px; font-weight: bold">FIN CONGE</td><td style="padding: 5px; font-weight: bold">LIEU D\'EMBAUCHE</td><td style="padding: 5px; font-weight: bold">LIEU D\'AFFECTATION</td><td style="padding: 5px; font-weight: bold">PERMISSIONS DEDUIT (JOUR)</td><td style="padding: 5px; font-weight: bold">CONGES NON PRIS (JOUR)</td></tr>';

            $historique = new Historique();
            $historique->LIBELLE = "Decision de conges ".$jour;
            $historique->save(false);
            $idhistorique = $historique->ID; $nb = 0;


            foreach ($Sheets as $Index => $Name)
            {

                $Spreadsheet -> ChangeSheet($Index);

                foreach ($Spreadsheet as $Key => $Row)
                {

                    if($i != 0) {

                        if(!empty($Row[0])) {

                            $matricule = utf8_encode($Row[0]);
                            $affectation = utf8_encode($Row[10]);
                            $debutService = utf8_encode($Row[13]);
                            $finService = utf8_encode($Row[14]);
                            $debut_jouissance = utf8_encode($Row[15]);
                            $fin_jouissance = utf8_encode($Row[16]);
                            $conge = utf8_encode($Row[17]);
                            $permission = utf8_encode($Row[18]);
                            $reste = utf8_encode($Row[19]);
                            $date_reprise = utf8_encode($Row[20]);
                            $prochain_service = utf8_encode($Row[21]);
                            $prochain_conge = utf8_encode($Row[22]);
                            $deplace = utf8_encode($Row[23]);
                            $embauche = utf8_encode($Row[6]);
                            $emploie = utf8_encode($Row[8]);

                            $us = Employe::find()->where(['MATRICULE'=>$matricule])->one();
                            $excercice = Exercice::findOne("2021");

                            if($us != null) {

                                $decision = new Decisionconges();
                                $decision->MATICULE = $matricule;
                                $decision->ANNEEEXIGIBLE = $excercice->ANNEEEXIGIBLE;
                                $decision->REF_DECISION = "";
                                $decision->DEBUTPERIODE = $excercice->DATEBEDUT;
                                $decision->FINPERIODE = $excercice->DATEFIN;
                                $decision->DEBUTPLANIF = $debut_jouissance;
                                $decision->FINPLANIF = $fin_jouissance;
                                $decision->DEBUTREELL = $debutService;
                                $decision->FINREEL = $finService;
                                $decision->STATUT =  "V";
                                $decision->DATEEMIS = date("Y-m-d");
                                $decision->DATEVAL = date("Y-m-d");
                                $decision->DATEREPRISE = $date_reprise;
                                $decision->NBJOUR = $conge;
                                $decision->HISTORIQUE = $idhistorique;
                                $decision->DEPARTEMENT = $us->CODEDPT;
                                $decision->JOURRESTANT = $reste;
                                $decision->save(false);

                                $numero = $this->getDecisionNumber($excercice->ANNEEEXIGIBLE)." - ".$setting->SUFFIXEREF."".Yii::$app->user->identity->INITIAL;

                                $decision->REF_DECISION = $numero;
                                $decision->IDUSER = Yii::$app->user->identity->IDUSER;
                                $decision->save(false);

                                $us->DATECALCUL = $prochain_conge;
                                $us->save(false);

                                $builder .= '<tr style="border-bottom: 1px solid #ffffff"><td style="padding: 5px">' . $numero . '</td><td style="padding: 5px">' . $us->MATRICULE . '</td><td style="padding: 5px">' . $us->NOM . ' ' . $us->PRENOM . '</td><td style="padding: 5px">Du '.$debutService.' au '.$finService.'</td><td style="padding: 5px">' . $debut_jouissance . '</td><td style="padding: 5px">' . $fin_jouissance . '</td><td style="padding: 5px">' . $embauche . '</td><td style="padding: 5px">' . $emploie . '</td><td style="padding: 5px">' . $permission . '</td><td style="padding: 5px">0</td></tr>';

                            } $nb++;

                        }
                    }

                    $i++;
                }
            }

            $builder .= '</table> <p>Nombre de d&eacute;cisions g&eacute;n&eacute;r&eacute;es : '.$nb.'</p>';

            $today = date("d/m/Y H:i:s");

            $builder.='<p>Date de g&eacute;n&eacute;ration : '.$today.' </p>';


            $builder.='</body></html>';

            $filename = 'Decision-'.time().'.pdf';

            $repertoire = '../web/uploads';

            $pdf = new Pdf([
                // set to use core fonts only
                'mode' => Pdf::MODE_CORE,
                // A4 paper format
                'format' => Pdf::FORMAT_A4,
                // portrait orientation
                'orientation' => Pdf::ORIENT_LANDSCAPE,
                // stream to browser inline
                'destination' => Pdf::DEST_BROWSER,
                // your html content input
                //'content' => $builder,
                // set mPDF properties on the fly
                'options' => ['title' => 'Decision de conges du '.$jour],
            ]);


            $mpdf = $pdf->api;

            $mpdf->WriteHtml($builder);

            $path = $mpdf->Output('', 'S');

            $historique = Historique::findOne($idhistorique);
            $historique->FICHIER = $filename;
            $historique->QUANTITE = $nb;
            $historique->save(false);


            unlink(('../web/uploads/'.$_FILES['fichier']['name']));

            Yii::$app->session->setFlash('success', 'Employés ajoutés avec succès.');

            return $this->redirect(['index']);

        }

        else return $this->render('import2.php');
    }

    public function actionImport1(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        if(isset($_REQUEST["imp"])){

            $uploaddir = '../web/uploads/';

            $file_name = $_FILES['fichier']['tmp_name'];

            $uploadfile2 = $uploaddir . basename($_FILES['fichier']['name']);

            move_uploaded_file($_FILES['fichier']['tmp_name'], $uploadfile2);

            // lecture du fichier

            @ini_set("memory_limit","5096M");

            require_once(Yii::getAlias('@vendor/phpexcel/php-excel-reader/excel_reader2.php'));

            require_once(Yii::getAlias('@vendor/phpexcel/SpreadsheetReader.php'));

            $Spreadsheet = new \SpreadsheetReader('../web/uploads/'.$_FILES['fichier']['name']);

            $BaseMem = memory_get_usage(); $errormessage = ""; $error = true;

            $Sheets = $Spreadsheet -> Sheets(); $i = 0; $tab = array();

            $setting = \app\models\Parametre::findOne(1); $p = 0;

            foreach ($Sheets as $Index => $Name)
            {

                $Spreadsheet -> ChangeSheet($Index);

                foreach ($Spreadsheet as $Key => $Row)
                {

                    if($i != 0) {

                        if(!empty($Row[0])) {

                            $matricule = utf8_encode($Row[0]); $civilite = $Row[1]; $nom = $Row[2];
                            $jeunefille = $Row[3]; $prenom = $Row[4]; $datnaiss = $Row[5];
                            $ville = "";
                            $sitmat = $Row[6]; $enfant = (int)utf8_encode($Row[7]);
                            $embauche = $Row[8]; $etablissement = $Row[9]; $direction = utf8_encode($Row[10]);
                            $departement = utf8_encode($Row[11]); $service = utf8_encode($Row[12]);
                            $fonction = utf8_encode($Row[13]);
                            $categorie = $Row[14];  $contrat = utf8_encode($Row[15]); $nconge = $Row[16];

                            $taille = strlen($matricule); $prefix = "";

                            if($taille < 5) {

                                for($i = 1; $i<= (5 - $taille); $i++) {

                                    $prefix.="0";
                                }
                            }

                            $matricule = $prefix."".$matricule;

                            $us = Employe::find()->where(['MATRICULE'=>$matricule])->one();

                            if($us == null) {

                                // recherche civilite
                                $civ = \app\models\Civilite::find()->where(['LIKE','LIBELLE',$civilite])->one();
                                if($civ == null) $codeciv = "MR"; else $codeciv = $civ->CODECIV;

                                $sit = \app\models\Sitmat::find()->where(['LIKE','LIBELLE',$sitmat])->one();
                                if($sit == null) $sitmat = "001"; else $sitmat = $sit->CODESIT;

                                if(!empty($categorie)) {

                                    $tcat = explode(" ",$categorie);

                                    if(isset($tcat[0])) $vcat = $tcat[0]; else $vcat = null;

                                    if(isset($tcat[1])) $vechel = $tcat[1]; else $vechel = null;

                                }

                                else {

                                    $vcat = null; $vechel = null;
                                }

                                // recherche etablissement

                                if(!empty($contrat)) {

                                    $vcontrat = $contrat;

                                } else $vcontrat = null;

                                // recherche fonction

                                if(!empty($fonction)) {

                                    $f =  utf8_encode($fonction);

                                    $ets = \app\models\Emploi::find()->where(['LIKE','LIBELLE',$f])->one();

                                    if($ets == null) {

                                        $allfonction = \app\models\Emploi::find()->all();
                                        $nb = count($allfonction); $nb++;
                                        $emploi = $this->getEmploiNumber($nb);
                                        $nemploi = new \app\models\Emploi();
                                        $nemploi->CODEEMP = $emploi;
                                        $nemploi->LIBELLE = utf8_encode($fonction);
                                        $nemploi->save(false);

                                    } else $emploi = $ets->CODEEMP;

                                }

                                else $emploi = null;

                                // recherche etablissement

                                if(!empty($etablissement)) {

                                    $ets = \app\models\Etablissement::find()->where(['CODEETS' => $etablissement])->one();

                                    if($ets == null){

                                        $nets = new \app\models\Etablissement();
                                        $nets->CODEETS = $etablissement;
                                        $nets->LIBELLE = $etablissement;
                                        $nets->save(false);
                                        $codeets = $etablissement;
                                    }

                                    else $codeets = $etablissement; } else $codeets = null;

                                if(!empty($departement)) {

                                    $ets = \app\models\Departements::find()->where(['LIKE','LIBELLE',$departement])->one();

                                    if($ets == null){

                                        $nets = new \app\models\Departements();
                                        $nets->LIBELLE = $departement;
                                        $nets->save(false);

                                        $codedept = $nets->CODEDPT;
                                    }

                                    else $codedept = $ets->CODEDPT; } else $codedept = null;

                                // recherche categorie

                                if($vcat != null) {

                                    $cat = \app\models\Categorie::find()->where(['LIKE','CODECAT',$vcat])->one();

                                    if($cat == null){

                                        $ncat = new \app\models\Categorie();
                                        $ncat->CODECAT = $vcat;
                                        $ncat->LIBELLE = $vcat;
                                        $ncat->save(false);
                                    }

                                }

                                if($vechel != null) {

                                    $echel = \app\models\Echellon::find()->where(['LIKE','CODEECH',$vechel])->one();

                                    if($echel == null){

                                        $ncat = new \app\models\Echellon();
                                        $ncat->CODEECH = $vechel;
                                        $ncat->LIBELLE = $vechel;
                                        $ncat->save(false);
                                    }

                                }

                                $lastconge = null;

                                if(!empty($nconge)) {
                                    $datec = date("Y-m-d",strtotime($nconge));
                                    if($datec != $nconge) $nconge = null;
                                } else $nconge = null;

                                if(!empty($datnaiss)) {
                                    $datec = date("Y-m-d",strtotime($datnaiss));
                                    if($datec != $datnaiss) $datnaiss = null;
                                } else $datnaiss = null;

                                if(!empty($embauche)) {
                                    $datec = date("Y-m-d",strtotime($embauche));
                                    if($datec != $embauche) $embauche = null;
                                } else $embauche = null;

                                if(!empty($nconge)) {

                                    $lastconge = date('Y-m-d',strtotime($nconge.' -'.($setting->DUREESERVICE).' day'));
                                }

                                if(!empty($direction)) {

                                    $ets = \app\models\Direction::find()->where(['LIKE','LIBELLE',$direction])->one();

                                    if($ets == null){

                                        $nets = new \app\models\Direction();
                                        $nets->LIBELLE = $direction;
                                        $nets->save(false);

                                        $codedir = $nets->ID;
                                    }

                                    else $codedir = $ets->ID; } else $codedir = null;

                                if(!empty($service)) {

                                    $ets = \app\models\Service::find()->where(['LIKE','LIBELLE',$service])->one();

                                    if($ets == null){

                                        $nets = new \app\models\Service();
                                        $nets->LIBELLE = $service;
                                        $nets->save(false);

                                        $codesev = $nets->ID;
                                    }

                                    else $codesev = $ets->ID; } else $codesev = null;

                                $tab[$p]["matricule"] = $matricule;
                                $tab[$p]["cat"] = $vcat;
                                $tab[$p]["ech"] = $vechel;
                                $tab[$p]["emp"] = $emploi;
                                $tab[$p]["ets"] = $codeets;
                                $tab[$p]["datnaiss"] = $datnaiss;
                                $tab[$p]["civ"] = $codeciv;
                                $tab[$p]["contrat"] = $vcontrat;
                                $tab[$p]["emb"] = $codeets;
                                $tab[$p]["nom"] = utf8_encode($nom);
                                $tab[$p]["prenom"] = utf8_encode($prenom);
                                $tab[$p]["dateemb"] = $embauche;
                                $tab[$p]["datcalcul"] = $nconge;
                                $tab[$p]["codedpt"] = $codedept;
                                $tab[$p]["lastconge"] = $lastconge;
                                $tab[$p]["deplace"] = 0;
                                $tab[$p]["statut"] = empty($nconge)?0:1;
                                $tab[$p]["direction"] = $codedir;
                                $tab[$p]["rh"] = (strpos($direction, 'Ressources Humaine') !== false)?1:0;
                                $tab[$p]["enfant"] = $enfant;
                                $tab[$p]["service"] = $codesev;
                                $tab[$p]["jeune"] = $jeunefille;
                                $tab[$p]["sitmat"] = $sitmat;
                                $tab[$p]["ville"] = "";

                                $user = new Employe();
                                $user->MATRICULE = $tab[$p]["matricule"];
                                $user->CODECAT = $tab[$p]["cat"];
                                $user->CODEECH = $tab[$p]["ech"];
                                $user->CODEEMP = $tab[$p]["emp"];
                                $user->CODEETS = $tab[$p]["ets"];
                                $user->DATNAISS = $tab[$p]["datnaiss"];
                                $user->CODECIV = $tab[$p]["civ"];
                                $user->CODECONT = $tab[$p]["contrat"];
                                $user->CODEETS_EMB = $tab[$p]["emb"];
                                $user->NOM = $tab[$p]["nom"];
                                $user->PRENOM = $tab[$p]["prenom"];
                                $user->DATEEMBAUCHE = $tab[$p]["dateemb"];
                                $user->DATECALCUL = $tab[$p]["datcalcul"];
                                $user->CODEDPT = $tab[$p]["codedpt"];
                                $user->LASTCONGE = $tab[$p]["lastconge"];
                                $user->DEPLACE = $tab[$p]["deplace"];
                                $user->STATUT = $tab[$p]["statut"];
                                $user->DIRECTION = $tab[$p]["direction"];
                                $user->RH = $tab[$p]["rh"];
                                $user->ENFANT = $tab[$p]["enfant"];
                                $user->SERVICE = $tab[$p]["service"];
                                $user->JEUNEFILLE = $tab[$p]["jeune"];
                                $user->SITMAT = $tab[$p]["sitmat"];
                                $user->VILLE = $tab[$p]["ville"];
                                $user->save(false);

                                $p++;

                            }

                        }
                    }

                    $i++;
                }
            }

            unlink(('../web/uploads/'.$_FILES['fichier']['name']));

            /* for($j = 0; $j < count($tab); $j++){

                 $user = new Employe();
                 $user->MATRICULE = $tab[$j]["matricule"];
                 $user->CODECAT = $tab[$j]["cat"];
                 $user->CODEECH = $tab[$j]["ech"];
                 $user->CODEEMP = $tab[$j]["emp"];
                 $user->CODEETS = $tab[$j]["ets"];
                 $user->DATNAISS = $tab[$j]["datnaiss"];
                 $user->CODECIV = $tab[$j]["civ"];
                 $user->CODECONT = $tab[$j]["contrat"];
                 $user->CODEETS_EMB = $tab[$j]["emb"];
                 $user->NOM = $tab[$j]["nom"];
                 $user->PRENOM = $tab[$j]["prenom"];
                 $user->DATEEMBAUCHE = $tab[$j]["dateemb"];
                 $user->DATECALCUL = $tab[$j]["datcalcul"];
                 $user->CODEDPT = $tab[$j]["codedpt"];
                 $user->LASTCONGE = $tab[$j]["lastconge"];
                 $user->DEPLACE = $tab[$j]["deplace"];
                 $user->STATUT = $tab[$j]["statut"];
                 $user->DIRECTION = $tab[$j]["direction"];
                 $user->RH = $tab[$j]["rh"];
                 $user->ENFANT = $tab[$j]["enfant"];
                 $user->SERVICE = $tab[$j]["service"];
                 $user->JEUNEFILLE = $tab[$j]["jeune"];
                 $user->SITMAT = $tab[$j]["sitmat"];
                 $user->VILLE = $tab[$j]["ville"];
                 $user->save(false);
             } */

            Yii::$app->session->setFlash('success', 'Employés ajoutés avec succès.');

            return $this->redirect(['index']);

        }

        else return $this->render('import');
    }

    function getEmploiNumber($nombre){

        $next = $nombre; $prefix = "E"; $position = 4 - strlen($next);

        $retour = "";

        for($i=1; $i<=$position; $i++){

            $retour.="0";
        }

        return $prefix."".$retour."".$next;
    }

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

    /**
     * Export employees reaching retirement age
     */
    public function actionExport1(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $setting = Parametre::findOne(1); $retraite = $setting->RETRAITE;
        $exercice = Exercice::find()->where(['STATUT'=>'O'])->one(); $datefin = $exercice->DATEFIN;
        $naissance_retraite = date('Y-m-d', strtotime($datefin. ' - '.$retraite.' years'));

        $csvfile = "employe_retraite-".$exercice->ANNEEEXIGIBLE.".csv";

        //output header
        header("Content-type: text/x-csv");
        header("Content-type: text/csv");
        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=".$csvfile."");
        header("Pragma: no-cache");
        header("Expires: 0");

        // create file pointer
        $output = fopen("php://output", "w");

        //output the column headings
        fputcsv($output, array('MATRICULE','LIEU EMBAUCHE','LIEU AFFECTATION','TYPE CONTRAT','CIVILITE', 'NOM','PRENOM','DATE EMBAUCHE','DATE RETRAITE','DATE NAISSANCE', 'DATE FIN DERNIER CONGE'),",");


        $employes = Employe::findBySql("SELECT * FROM employe WHERE DATNAISS <= '$naissance_retraite' ")->all();

        foreach($employes as $employe){

            $tab2 = array();
            $tab2[] = $employe->MATRICULE;  $tab2[] = $employe->CODEETS_EMB; $tab2[] = $employe->CODEETS;
            $tab2[] = $employe->CODECONT; $tab2[] = $employe->CODECIV; $tab2[] = $employe->NOM;
            $tab2[] = $employe->PRENOM; $tab2[] = $employe->DATEEMBAUCHE; $tab2[] = date('Y-m-d', strtotime($employe->DATNAISS. ' + '.$retraite.' years'));
            $tab2[] = $employe->DATNAISS; $tab2[] = $employe->LASTCONGE;

            fputcsv($output, $tab2, ",");

        }

        fclose($output);

        exit;

    }

    /**
     * Export employees in CDD with less than 1 year of service
     */
    public function actionExport2(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $exercice = Exercice::find()->where(['STATUT'=>'O'])->one();
        $datedebut = $exercice->DATEBEDUT;

        $csvfile = "employe_cdd_moins_1an-".$exercice->ANNEEEXIGIBLE.".csv";
        //output header
        header("Content-type: text/x-csv");
        header("Content-type: text/csv");
        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=".$csvfile."");
        header("Pragma: no-cache");
        header("Expires: 0");

        // create file pointer
        $output = fopen("php://output", "w");

        //output the column headings

        fputcsv($output, array('MATRICULE','LIEU EMBAUCHE','LIEU AFFECTATION','TYPE CONTRAT','CIVILITE', 'NOM','PRENOM','DATE EMBAUCHE'),",");

        $employes = Employe::findBySql("SELECT * FROM employe WHERE CODECONT = 'C.D.D' AND DATEEMBAUCHE >= '$datedebut' ")->all();

        foreach($employes as $employe){
            $tab2 = array();
            $tab2[] = $employe->MATRICULE;  $tab2[] = $employe->CODEETS_EMB; $tab2[] = $employe->CODEETS;
            $tab2[] = $employe->CODECONT; $tab2[] = $employe->CODECIV; $tab2[] = $employe->NOM;
            $tab2[] = $employe->PRENOM; $tab2[] = $employe->DATEEMBAUCHE;
            fputcsv($output, $tab2, ",");
        }
        fclose($output);
        exit;
    }

}
