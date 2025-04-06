<?php

namespace app\controllers;

use app\models\Departements;
use app\models\Direction;
use app\models\Emploi;
use app\models\Loges;
use app\models\Parametre;
use app\models\Service;
use app\models\Typeabsence;
use kartik\mpdf\Pdf;
use Yii;
use app\models\Absenceponctuel;
use app\models\AbsenceponctuelSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Exercice;
use app\models\Employe;
use yii\web\UploadedFile;
use yii\filters\AccessControl;


/**
 * AbsenceponctuelController implements the CRUD actions for Absenceponctuel model.
 */
class AbsenceponctuelController extends Controller
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
     * Lists all Absenceponctuel models.
     * @return mixed
     */
    public function actionIndex()
    {

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $searchModel = new AbsenceponctuelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Absenceponctuel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        return $this->redirect(['update','id'=>$id]);
    }

    /**
     * Creates a new Absenceponctuel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new Absenceponctuel();

        $model->scenario = 'create';

        if ($model->load(Yii::$app->request->post())) {

            $exo = Exercice::findOne($model->ANNEEEXIGIBLE);

            if($model->CODEABS == "A003"){

                if($model->TYPE_DEMANDE == 1) {

                    Yii::$app->session->setFlash('error', 'La régularisation des congés doit être en jour.');

                    return $this->redirect(['create']);
                }

                else {

                    $employe = Employe::findOne($model->MATICULE);

                    $date1 = strtotime($model->DATEDEBUT); $date2 = strtotime($model->DATEFIN);

                    $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

                    if($employe->SOLDECREDIT == 0) {

                        Yii::$app->session->setFlash('error', 'Cet employé n\'est pas éligible aux régulations congés. ');

                        return $this->redirect(['create']);
                    }

                    else if($employe->SOLDECREDIT < $nbjour) {

                        Yii::$app->session->setFlash('error', 'Votre régularisation congés ne peut excéder '.$employe->SOLDECREDIT.' jour(s)');

                        return $this->redirect(['create']);

                    }

                    else {

                        $e1 = new \DateTime($exo->DATEBEDUT);  $e2 = new \DateTime($exo->DATEFIN);

                        $c1 = new \DateTime($model->DATEDEBUT);  $c2 = new \DateTime($model->DATEFIN);

                        $model->STATUT = "E"; $model->DATEEMIS = date("Y-m-d");

                        if(($c1 < $e1) || ($c2 > $e2)) {

                            Yii::$app->session->setFlash('error', 'La période doit être dans l\'intervalle de l\'exercie');

                            return $this->redirect(['create']);

                        }

                        else if($c1 >= $c2) {

                            Yii::$app->session->setFlash('error', 'La date de début doit être inférieure à la date de fin');

                            return $this->redirect(['create']);

                        }

                        else {

                            $model->DOCUMENTFILE = UploadedFile::getInstance($model, 'DOCUMENTFILE');

                            if ($model->DOCUMENTFILE != null) {

                                $model->DOCUMENT = $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension;
                            }

                            $model->IDUSER = Yii::$app->user->identity->IDUSER;

                            $model->IMPUTERCONGES = 2;

                            $model->save(false);

                            if ($model->DOCUMENTFILE != null) $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);


                            $logs = new Loges();
                            $logs->DATEOP = date("Y-m-d H:i:s");
                            $logs->USERID = Yii::$app->user->identity->IDUSER;
                            $logs->USERNAME = Yii::$app->user->identity->NOM;
                            $logs->OPERATION = "Création demande de permission numero ".$model->ID_ABSENCE;
                            $logs->save(false);

                            return $this->redirect(['valider', 'id' => $model->ID_ABSENCE]);
                        }

                    }

                }

            }

            else {

                if($model->TYPE_DEMANDE == 1) {

                    $model->STATUT = "E"; $model->DATEEMIS = date("Y-m-d");

                    $model->DOCUMENTFILE = UploadedFile::getInstance($model, 'DOCUMENTFILE');

                    if ($model->DOCUMENTFILE != null) {

                        $model->DOCUMENT = $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension;
                    }

                    $model->IDUSER = Yii::$app->user->identity->IDUSER;

                    $model->DATEDEBUT = date("Y-m-d"); $model->DATEFIN = date("Y-m-d");

                    $model->save(false);

                    if ($model->DOCUMENTFILE != null) $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                    $logs = new Loges();
                    $logs->DATEOP = date("Y-m-d H:i:s");
                    $logs->USERID = Yii::$app->user->identity->IDUSER;
                    $logs->USERNAME = Yii::$app->user->identity->NOM;
                    $logs->OPERATION = "Création demande de permission numero ".$model->ID_ABSENCE;
                    $logs->save(false);

                  //  Yii::$app->session->setFlash('success', 'Permission enregistrée avec succès');

                    return $this->redirect(['valider', 'id' => $model->ID_ABSENCE]);

                }

                else {

                    $e1 = new \DateTime($exo->DATEBEDUT);  $e2 = new \DateTime($exo->DATEFIN);

                    $c1 = new \DateTime($model->DATEDEBUT);  $c2 = new \DateTime($model->DATEFIN);

                    $model->STATUT = "E"; $model->DATEEMIS = date("Y-m-d");

                    $setting = Parametre::findOne(1);

                    if(($c1 < $e1) || ($c2 > $e2)) {

                        Yii::$app->session->setFlash('error', 'La période doit être dans l\'intervalle de l\'exercie');

                        return $this->redirect(['create']);

                    }

                    else if($c1 >= $c2) {

                        Yii::$app->session->setFlash('error', 'La date de début doit être inférieure à la date de fin');

                        return $this->redirect(['create']);

                    }

                    else {

                        $model->DOCUMENTFILE = UploadedFile::getInstance($model, 'DOCUMENTFILE');

                        if ($model->DOCUMENTFILE != null) {

                            $model->DOCUMENT = $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension;
                        }

                        $model->IDUSER = Yii::$app->user->identity->IDUSER;

                        $model->save(false);

                        if ($model->DOCUMENTFILE != null) $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                        $logs = new Loges();
                        $logs->DATEOP = date("Y-m-d H:i:s");
                        $logs->USERID = Yii::$app->user->identity->IDUSER;
                        $logs->USERNAME = Yii::$app->user->identity->NOM;
                        $logs->OPERATION = "Création demande de permission numero ".$model->ID_ABSENCE;
                        $logs->save(false);

                      //  Yii::$app->session->setFlash('success', 'Permission enregistrée avec succès');

                        return $this->redirect(['valider', 'id' => $model->ID_ABSENCE]);
                    }

                }
            }

        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Absenceponctuel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = $this->findModel($id);

        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post())) {

            $exo = Exercice::findOne($model->ANNEEEXIGIBLE);

            if($model->TYPE_DEMANDE == 1) {

                $model->DOCUMENTFILE = UploadedFile::getInstance($model, 'DOCUMENTFILE');

                if($model->DOCUMENTFILE != null) {

                    $model->DOCUMENT = $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension;

                    $model->save(false);

                    $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                } else { $model->save(false); }

                Yii::$app->session->setFlash('success', 'Permission modifiée avec succès');

                return $this->redirect(['view', 'id' => $model->ID_ABSENCE]);
            }

            else {

                $e1 = new \DateTime($exo->DATEBEDUT);  $e2 = new \DateTime($exo->DATEFIN);

                $c1 = new \DateTime($model->DATEDEBUT);  $c2 = new \DateTime($model->DATEFIN);

                if(($c1 < $e1) || ($c2 > $e2)) {

                    Yii::$app->session->setFlash('error', 'La période doit être dans l\'intervalle de l\'exercie');

                    return $this->redirect(['create']);

                }

                else {

                    $model->DOCUMENTFILE = UploadedFile::getInstance($model, 'DOCUMENTFILE');

                    if($model->DOCUMENTFILE != null) {

                        $model->DOCUMENT = $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension;

                        $model->save(false);

                        $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                    } else { $model->save(false); }

                    Yii::$app->session->setFlash('success', 'Permission modifiée avec succès');

                    return $this->redirect(['view', 'id' => $model->ID_ABSENCE]);

                }

            }

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Absenceponctuel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = $this->findModel($id);

        $nodelete = array('V','A');

        if(in_array($model->STATUT,$nodelete)) {

            Yii::$app->session->setFlash('error', 'Vous ne pouvez pas supprimer une absence de congés déjà opérationnelle');

        }

        else {

            $logs = new Loges();
            $logs->DATEOP = date("Y-m-d H:i:s");
            $logs->USERID = Yii::$app->user->identity->IDUSER;
            $logs->USERNAME = Yii::$app->user->identity->NOM;
            $logs->OPERATION = "Suppression demande de permission numero ".$id;
            $logs->save(false);

            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', 'Permission supprimée avec succès');

        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Absenceponctuel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Absenceponctuel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Absenceponctuel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionValider(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $id = $_REQUEST["id"];  $model = $this->findModel($id); $model->DATEVAL = date("Y-m-d");

        $model->STATUT = "V"; 

        $exos = Exercice::find()->where(['ANNEEEXIGIBLE'=>$model->ANNEEEXIGIBLE, 'STATUT'=>'O'])->One();

                if($exos == null) {

                    Yii::$app->session->setFlash('error', 'Impossible de faire une action sur un exercice cloture');

                    return $this->redirect(['update', 'id' => $model->ID_ABSENCE]);
                }
                else {

                   $employe = Employe::findOne($model->MATICULE);
                   
                   $date1 = strtotime($model->DATEDEBUT); $date2 = strtotime($model->DATEFIN);

                   $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

                   $abscences = \app\models\Absenceponctuel::find()->where(['MATICULE'=>$employe->MATRICULE,'ANNEEEXIGIBLE'=>$model->ANNEEEXIGIBLE,'STATUT'=>'V','IMPUTERCONGES'=>1])->all();

                    $nbpermission = 0;

                    foreach($abscences as $abscence) {

                        $date1 = strtotime($abscence->DATEDEBUT); $date2 = strtotime($abscence->DATEFIN);

                        $diff = $date2 - $date1;

                        $nbjourabs = abs(round($diff/86400)) + 1;

                        $nbpermission+=$nbjourabs;

                    }

                   if($model->IMPUTERCONGES == 1){

                       $setting = \app\models\Parametre::findOne(1);

                       $total = $nbpermission + $nbjour;

                     if($total > $setting->DUREECONGES)  {

                         Yii::$app->session->setFlash('error', 'Impossible de prendre plus de '.$setting->DUREECONGES.' jours de permission sur un exercice. Vous avez déjà pris '.$nbpermission.' jours.');

                         return $this->redirect(['update', 'id' => $model->ID_ABSENCE]);
                     }

                     else {

                         if($model->TYPE_DEMANDE == 0) {

                             $employe = Employe::findOne($model->MATICULE);

                             $date1 = strtotime($model->DATEDEBUT); $date2 = strtotime($model->DATEFIN);

                             $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

                             $employe->SOLDEAVANCE = $employe->SOLDEAVANCE + $nbjour;

                             $employe->save(false);

                             $model->DEJA = 1;

                         }

                         else {

                             $abscencs = \app\models\Absenceponctuel::find()->where(['MATICULE'=>$employe->MATRICULE,'STATUT'=>'V','IMPUTERCONGES'=>1,'TYPE_DEMANDE'=>1,'DEJA'=>0])->all();

                             $nbpermission2 = 0;

                             foreach($abscencs as $abscenc) {

                                 $nbpermission2+= $abscenc->DUREE;
                             }

                             $nbpermission2+= $model->DUREE;

                             $jourheureconge = (int)($nbpermission2 / 8);
                             $restes = $nbpermission2 % 8;

                             if($restes == 0) {

                                 foreach ($abscencs as $abscenc) {
                                     $abscenc->DEJA = 1;
                                     $abscenc->save(false);
                                 }

                                 $employe = Employe::findOne($model->MATICULE);

                                 $employe->SOLDEAVANCE = $employe->SOLDEAVANCE + $jourheureconge;

                                 $employe->save(false);

                                 $model->DEJA = 1;

                             }

                             else if($restes != $nbpermission2) {

                                 foreach ($abscencs as $abscenc) {
                                     $abscenc->DEJA = 1;
                                     $abscenc->save(false);
                                 }

                                 $employe = Employe::findOne($model->MATICULE);

                                 $employe->SOLDEAVANCE = $employe->SOLDEAVANCE + $jourheureconge;

                                 $employe->save(false);

                                 $model->DEJA = 0;
                                 $model->DUREE = $restes;

                             }


                         }

                         $model->save(false);

                         Yii::$app->session->setFlash('success', 'Permission validée avec succès');

                         return $this->redirect(['view', 'id' => $model->ID_ABSENCE]);

                     }
                    
                   }

                   else {

                       $model->save(false);

                       if($model->CODEABS == "A003") {

                           $employe = Employe::findOne($model->MATICULE);

                           $date1 = strtotime($model->DATEDEBUT); $date2 = strtotime($model->DATEFIN);

                           $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

                           $employe->SOLDECREDIT = $employe->SOLDECREDIT - $nbjour;

                           $employe->save(false);

                       }

                       Yii::$app->session->setFlash('success', 'Permission validée avec succès');

                       return $this->redirect(['view', 'id' => $model->ID_ABSENCE]);
                   }

         }
    }

    public function actionAnnuler(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $id = $_REQUEST["id"];  $model = $this->findModel($id); $model->DATEANN = date("Y-m-d");

        $model->STATUT = "A"; 

        $exos = Exercice::find()->where(['ANNEEEXIGIBLE'=>$model->ANNEEEXIGIBLE, 'STATUT'=>'O'])->One();

                if($exos == null) {

                    Yii::$app->session->setFlash('error', 'Impossible de faire une action sur un exercice cloturé');

                    return $this->redirect(['update', 'id' => $model->ID_ABSENCE]);
                }

                else {

        $model->save(false);

        Yii::$app->session->setFlash('success', 'Permission annulée avec succès');

        return $this->redirect(['view', 'id' => $model->ID_ABSENCE]);
                }
    }

    public function actionExport(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new Absenceponctuel();

        $model->scenario = 'export';

        if ($model->load(Yii::$app->request->post())) {

            $absences = Absenceponctuel::find()->where(['ANNEEEXIGIBLE'=>$model->ANNEEEXIGIBLE])->all();

            $jour = date("Y-m-d");

            $csvfile = "absence-".$model->ANNEEEXIGIBLE.".csv";

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
            fputcsv($output, array('TYPE ABSENCE','MATRICULE','EMPLOYE','POSTE','DIRECTION','DEPARTEMENT','SERVICE','TYPE DE PERMISSION','DEBUT','FIN', 'DUREE','IMPUTER CONGES','DATE EMISSION','DATE VALIDATION','DATE ANNULATION', 'COMMENTAIRE', 'STATUT',),",");

            // creation du fichier d'export

            foreach ($absences as $absence) {

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

            $today = date("d/m/Y H:i:s");

            fclose($output);


            exit;

        }

        return $this->render('export', [
            'model' => $model,
        ]);

    }
}
