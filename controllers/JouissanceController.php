<?php

namespace app\controllers;

use app\models\Absenceponctuel;
use app\models\Ampliation;
use app\models\Cancelation;
use app\models\Departements;
use app\models\Employe;
use app\models\Etablissement;
use app\models\Exercice;
use app\models\Parametre;
use Yii;
use app\models\Loges;
use app\models\Jouissance;
use app\models\JouissanceSearch;
use yii\base\BaseObject;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf;
use app\models\Decisionconges;
use yii\web\UploadedFile;
use yii\filters\AccessControl;

/**
 * JouissanceController implements the CRUD actions for Jouissance model.
 */
class JouissanceController extends Controller
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
     * Lists all Jouissance models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $searchModel = new JouissanceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Jouissance model.
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
     * Creates a new Jouissance model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new Jouissance();

        $model->STATUT = 'B';

        if ($model->load(Yii::$app->request->post())) {

            $decision = Decisionconges::findOne($model->IDDECISION);

            if($decision == null) {

                Yii::$app->session->setFlash('error', 'Aucune correspondance pour votre décision de congés.');

                return $this->redirect(['create']);

            }

            else {

                $currentyear  = date("Y");

                $past = $currentyear - $decision->ANNEEEXIGIBLE;

                if($past > 2) {

                    Yii::$app->session->setFlash('error', 'La décision choisie a plus de 2 ans d\'existance.');

                    return $this->redirect(['create']);

                }

                else {

                    if($model->TYPES == "04") $exist = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION, 'TYPES'=>['04','01']])->all();

                   else  $exist = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION, 'TYPES'=>['01','02']])->all();

                    if(count($exist) != 0) {

                        Yii::$app->session->setFlash('error', 'Vous avez déjà crée des jouissances ou non jouissances pour cette décision.');

                        return $this->redirect(['create']);
                    }

                    else {

                        $model->DATECREATION = date("Y-m-d");

                        $model->USERCREATE = Yii::$app->user->identity->IDUSER;

                        $model->EXERCICE = $decision->ANNEEEXIGIBLE;

                        $model->DOCUMENTFILE = UploadedFile::getInstance($model, 'DOCUMENTFILE');

                        if ($model->DOCUMENTFILE != null) {

                            $model->DOCUMENT2 = $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension;
                        }

                        $decision = \app\models\Decisionconges::findOne($model->IDDECISION);

                        if($decision == null) {

                            Yii::$app->session->setFlash('error', 'Aucune occurence pour cette décision.');

                            return $this->redirect(['create']);
                        }

                        else {

                            $setting = Parametre::findOne(1);

                            $timbre = "";

                            if($model->TIMBRE != null && !empty($model->TIMBRE)) {
                                $timbre = $model->TIMBRE;
                            } else {

                                $employe = \app\models\Employe::findOne($decision->MATICULE);

                                if ($employe->CODEETS_EMB == "DLA") {
                                    $di = \app\models\Direction::findOne($employe->DIRECTION);
                                    if (($di != null) && strpos($di->LIBELLE, 'Exploitation') !== false) {
                                        $ampliation = \app\models\Ampliation::findOne(8);
                                    } else {
                                        $ampliation = \app\models\Ampliation::findOne(3);
                                    }

                                } else if ($employe->CODEETS_EMB == "NSI") {
                                    $di = \app\models\Direction::findOne($employe->DIRECTION);
                                    if (($di != null) && strpos($di->LIBELLE, 'International') !== false) {
                                        $ampliation = \app\models\Ampliation::findOne(7);
                                    } else {
                                        $ampliation = \app\models\Ampliation::findOne(9);
                                    }
                                } else  $ampliation = \app\models\Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);

                                if(($ampliation->TIMBRE != null) && !empty($ampliation->TIMBRE))  $timbre = $ampliation->TIMBRE;
                                else $timbre = $setting->TIMBREJOUISSANCE;

                                $timbre = $timbre."".Yii::$app->user->identity->INITIAL;
                            }

                            $numero = $this->getDecisionNumber($decision->ANNEEEXIGIBLE)." - ".$timbre;

                            $model->NUMERO = $numero;

                            // jouissance partielle

                            if($model->TYPES == "02"){

                                $debut = $model->debutconge;

                                $fin =  date('Y-m-d', strtotime($model->debutconge. ' + '.($model->nbjour - 1).' days'));

                                $exist = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION,'TYPES'=>['02','01']])->all();

                                if(count($exist) != 0) {

                                    Yii::$app->session->setFlash('error', 'Vous avez déjà crée des jouissances pour cette décision.');

                                    return $this->redirect(['create']);
                                }

                                else if($model->nbjour > $decision->NBJOUR) {

                                    Yii::$app->session->setFlash('error', 'Impossible de valider cette jouissance de congé, il vous reste '.$decision->NBJOUR.' jour(s) de disponible sur la décision de congé '.$decision->REF_DECISION.' ');

                                    return $this->redirect(['create', 'table' => 'T6']);

                                }

                                else if($model->nbjour < 12) {

                                    Yii::$app->session->setFlash('error', 'Le nombre de jours de congé minimal pour une jouissance partielle est de 12 ');

                                    return $this->redirect(['create', 'table' => 'T6']);

                                }

                                else {

                                    $model->DEBUT = $debut;  $model->FIN = $fin;

                                    $model->TITRE = 'AUTORISATION DE JOUISSANCE PARTIELLE DE CONGE';

                                        $model->IDUSER = Yii::$app->user->identity->IDUSER;

                                    $model->DOCUMENT4 = $model->signataire;
                                    $model->DOCUMENT3 = $model->timbre;
                                    $model->JOUR = $model->nbjour;

                                        $model->save(false);

                                        $model->NUMERO = $numero; $model->save(false);

                                        if ($model->DOCUMENTFILE != null) $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                                    $logs = new Loges();
                                    $logs->DATEOP = date("Y-m-d H:i:s");
                                    $logs->USERID = Yii::$app->user->identity->IDUSER;
                                    $logs->USERNAME = Yii::$app->user->identity->NOM;
                                    $logs->OPERATION = "Création jouissance partielle numero ".$model->IDNATURE;
                                    $logs->save(false);


                                        return $this->redirect(['valider', 'id' => $model->IDNATURE]);

                                }

                            }

                            // jouissance reliquat conges

                            else if($model->TYPES == "04"){

                                $exist = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION,'TYPES'=>'02'])->one();

                                $exist2 = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION,'TYPES'=>'04'])->all();

                                if($exist == null) {

                                    Yii::$app->session->setFlash('error', 'Vous  ne pouvez pas créer de jouissance de réliquat sans avoir crée de jouissance partielle.');

                                    return $this->redirect(['create']);
                                }

                                else if(count($exist2) != 0) {

                                    Yii::$app->session->setFlash('error', 'Vous  ne pouvez pas créer plus d\'un reliquat congés par jouissance');
                                    return $this->redirect(['create']);
                                }

                                else {

                                    // $datedebut = date('Y-m-d', strtotime($exist->FIN. ' + 1 days'));

                                    $datedebut = $model->debutconge;

                                    $reste = $decision->NBJOUR - 1;

                                    $datefin = date('Y-m-d', strtotime($datedebut. ' + '.$reste.' days'));

                                    $model->DEBUT = $datedebut; $model->FIN = $datefin;

                                    $model->TITRE = 'AUTORISATION DE JOUISSANCE DU RELIQUAT DE CONGE';

                                    $model->IDUSER = Yii::$app->user->identity->IDUSER;

                                    $model->DOCUMENT4 = $model->signataire;
                                    $model->DOCUMENT3 = $model->timbre;

                                    $model->save(false);

                                    $model->NUMERO = $numero; $model->save(false);

                                    if ($model->DOCUMENTFILE != null) $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                                    Yii::$app->session->setFlash('success', 'Reliquat de jouissance enregistré avec succès');

                                    $logs = new Loges();
                                    $logs->DATEOP = date("Y-m-d H:i:s");
                                    $logs->USERID = Yii::$app->user->identity->IDUSER;
                                    $logs->USERNAME = Yii::$app->user->identity->NOM;
                                    $logs->OPERATION = "Création reliquat jouissance numero ".$model->IDNATURE;
                                    $logs->save(false);

                                    return $this->redirect(['valider', 'id' => $model->IDNATURE]);

                                }

                            }

                            // jouissance totale

                            else {

                                $exist = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION,'TYPES'=>['02','01']])->all();

                                if(count($exist) != 0) {

                                    Yii::$app->session->setFlash('error', 'Vous avez déja crée des jouissances pour cette décision.');

                                    return $this->redirect(['create']);
                                }

                                else {

                                    $debut = $model->debutconge;

                                    $fin =  date('Y-m-d', strtotime($model->debutconge. ' + '.($decision->NBJOUR - 1).' days'));

                                    $model->DEBUT = $debut;  $model->FIN = $fin;

                                    $model->TITRE = 'AUTORISATION DE JOUISSANCE DE CONGE';

                                    $model->IDUSER = Yii::$app->user->identity->IDUSER;

                                    $model->DOCUMENT4 = $model->signataire;
                                    $model->DOCUMENT3 = $model->timbre;

                                    $model->save(false);

                                    $model->NUMERO = $numero;

                                    $model->save(false);

                                    if ($model->DOCUMENTFILE != null) $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                                    Yii::$app->session->setFlash('success', 'Jouissance totale enregistrée avec succès');

                                    $logs = new Loges();
                                    $logs->DATEOP = date("Y-m-d H:i:s");
                                    $logs->USERID = Yii::$app->user->identity->IDUSER;
                                    $logs->USERNAME = Yii::$app->user->identity->NOM;
                                    $logs->OPERATION = "Création jouissance totale numero ".$model->IDNATURE;
                                    $logs->save(false);

                                    return $this->redirect(['valider', 'id' => $model->IDNATURE]);
                                }

                            }
                        }

                    }

                }
            }

        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Jouissance model.
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

        if ($model->load(Yii::$app->request->post())) {

            $model->DOCUMENTFILE = UploadedFile::getInstance($model, 'DOCUMENTFILE');

            $model->DOCUMENT4 = $model->signataire;

            if($model->DOCUMENTFILE != null) {

                $model->DOCUMENT2 = $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension;

                $model->save(false);

                $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

            }

            $timbre = "";

            $setting = \app\models\Parametre::findOne(1);
            $decision = Decisionconges::findOne($model->IDDECISION);

            if($model->TIMBRE != null && !empty($model->TIMBRE)) {
                $timbre = $model->TIMBRE;
            }

            else {

                $employe = \app\models\Employe::findOne($decision->MATICULE);

                if ($employe->CODEETS_EMB == "DLA") {
                    $di = \app\models\Direction::findOne($employe->DIRECTION);
                    if (($di != null) && strpos($di->LIBELLE, 'Exploitation') !== false) {
                        $ampliation = \app\models\Ampliation::findOne(8);
                    } else {
                        $ampliation = \app\models\Ampliation::findOne(3);
                    }

                } else if ($employe->CODEETS_EMB == "NSI") {
                    $di = \app\models\Direction::findOne($employe->DIRECTION);
                    if (($di != null) && strpos($di->LIBELLE, 'International') !== false) {
                        $ampliation = \app\models\Ampliation::findOne(7);
                    } else {
                        $ampliation = \app\models\Ampliation::findOne(9);
                    }
                } else  $ampliation = \app\models\Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);

                if(($ampliation->TIMBRE != null) && !empty($ampliation->TIMBRE) ) $timbre = $ampliation->TIMBRE;

                else $timbre = $setting->TIMBREJOUISSANCE;

                $timbre = $timbre."".Yii::$app->user->identity->INITIAL;;
            }

            $numero = $this->getDecisionNumber($decision->ANNEEEXIGIBLE)." - ".$timbre;

            $model->NUMERO = $numero;

            // jouissance partielle

            if($model->TYPES == "02"){

                $debut = $model->debutconge;

                $fin =  date('Y-m-d', strtotime($model->debutconge. ' + '.($model->nbjour - 1).' days'));

                $exist = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION,'TYPES'=>['02','01']])->all();

                if($model->nbjour > $decision->NBJOUR) {

                    Yii::$app->session->setFlash('error', 'Impossible de valider cette jouissance de congé, il vous reste '.$decision->NBJOUR.' jour(s) de disponible sur la décision de congé '.$decision->REF_DECISION.' ');

                    return $this->redirect(['update','id'=>$id]);

                }

                else if($model->nbjour < 12) {

                    Yii::$app->session->setFlash('error', 'Le nombre de jours de congé minimal pour une jouissance partielle est de 12 ');

                    return $this->redirect(['update','id'=>$id]);

                }

                else {

                    $model->DEBUT = $debut;  $model->FIN = $fin;

                    $model->TITRE = 'AUTORISATION DE JOUISSANCE PARTIELLE DE CONGE';

                    $model->IDUSER = Yii::$app->user->identity->IDUSER;

                    $model->DOCUMENT4 = $model->signataire;
                    $model->DOCUMENT3 = $model->timbre;
                    $model->JOUR = $model->nbjour;

                    $model->save(false);

                    $model->NUMERO = $numero; $model->save(false);

                    if ($model->DOCUMENTFILE != null) $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                    $logs = new Loges();
                    $logs->DATEOP = date("Y-m-d H:i:s");
                    $logs->USERID = Yii::$app->user->identity->IDUSER;
                    $logs->USERNAME = Yii::$app->user->identity->NOM;
                    $logs->OPERATION = "Création jouissance partielle numero ".$model->IDNATURE;
                    $logs->save(false);

                }

            }

            // jouissance reliquat conges

            else if($model->TYPES == "04"){

                $exist = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION,'TYPES'=>'02'])->one();

                $exist2 = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION,'TYPES'=>'04'])->all();

                if($exist == null) {

                    Yii::$app->session->setFlash('error', 'Vous  ne pouvez pas créer de jouissance de réliquat sans avoir crée de jouissance partielle.');

                    return $this->redirect(['update','id'=>$id]);
                }

                else if(count($exist2) != 0) {

                    Yii::$app->session->setFlash('error', 'Vous  ne pouvez pas créer plus d\'un reliquat congés par jouissance');
                    return $this->redirect(['update','id'=>$id]);
                }

                else {

                    // $datedebut = date('Y-m-d', strtotime($exist->FIN. ' + 1 days'));

                    $datedebut = $model->debutconge;

                    $reste = $decision->NBJOUR - 1;

                    $datefin = date('Y-m-d', strtotime($datedebut. ' + '.$reste.' days'));

                    $model->DEBUT = $datedebut; $model->FIN = $datefin;

                    $model->TITRE = 'AUTORISATION DE JOUISSANCE DU RELIQUAT DE CONGE';

                    $model->IDUSER = Yii::$app->user->identity->IDUSER;

                    $model->DOCUMENT4 = $model->signataire;
                    $model->DOCUMENT3 = $model->timbre;

                    $model->save(false);

                    $model->NUMERO = $numero; $model->save(false);

                    if ($model->DOCUMENTFILE != null) $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                    Yii::$app->session->setFlash('success', 'Reliquat de jouissance enregistré avec succès');

                    $logs = new Loges();
                    $logs->DATEOP = date("Y-m-d H:i:s");
                    $logs->USERID = Yii::$app->user->identity->IDUSER;
                    $logs->USERNAME = Yii::$app->user->identity->NOM;
                    $logs->OPERATION = "Création reliquat jouissance numero ".$model->IDNATURE;
                    $logs->save(false);

                }

            }

            // jouissance totale

            else {

                $exist = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION,'TYPES'=>['02','01']])->all();

                if(count($exist) != 0) {

                    Yii::$app->session->setFlash('error', 'Vous avez déja crée des jouissances pour cette décision.');

                    return $this->redirect(['update','id'=>$id]);
                }

                else {

                    $debut = $model->debutconge;

                    $fin =  date('Y-m-d', strtotime($model->debutconge. ' + '.($decision->NBJOUR - 1).' days'));

                    $model->DEBUT = $debut;  $model->FIN = $fin;

                    $model->TITRE = 'AUTORISATION DE JOUISSANCE DE CONGE';

                    $model->IDUSER = Yii::$app->user->identity->IDUSER;

                    $model->DOCUMENT4 = $model->signataire;
                    $model->DOCUMENT3 = $model->timbre;

                    $model->save(false);

                    $model->NUMERO = $numero;

                    $model->save(false);

                    if ($model->DOCUMENTFILE != null) $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                    Yii::$app->session->setFlash('success', 'Jouissance totale enregistrée avec succès');

                    $logs = new Loges();
                    $logs->DATEOP = date("Y-m-d H:i:s");
                    $logs->USERID = Yii::$app->user->identity->IDUSER;
                    $logs->USERNAME = Yii::$app->user->identity->NOM;
                    $logs->OPERATION = "Création jouissance totale numero ".$model->IDNATURE;
                    $logs->save(false);

                }

            }

            Yii::$app->session->setFlash('success', 'Jouissance enregistrée avec succes');

            return $this->redirect(['view', 'id' => $model->IDNATURE]);
        }

        else {

            $model->timbre = $model->DOCUMENT3;
            $model->signataire = $model->DOCUMENT4;
            $model->debutconge = $model->DEBUT;

            $decision = Decisionconges::findOne($model->IDDECISION);
            $employe = Employe::findOne($decision->MATICULE);

            $model->employe = $employe->getFullname();

            return $this->render('update', [
                'model' => $model,
                'matricule'=>$decision->MATICULE,
            ]);

        }
    }

    /**
     * Deletes an existing Jouissance model.
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

        $nodelete = array('V','R','S');

        if(in_array($model->STATUT,$nodelete)) {

            Yii::$app->session->setFlash('error', 'Vous ne pouvez pas supprimer une jouissance de congés déja opérationnel');

            return $this->redirect(['index']);
        }

        else {

            $logs = new Loges();
            $logs->DATEOP = date("Y-m-d H:i:s");
            $logs->USERID = Yii::$app->user->identity->IDUSER;
            $logs->USERNAME = Yii::$app->user->identity->NOM;
            $logs->OPERATION = "Suppression jouissance numero ".$id;
            $logs->save(false);

            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', 'Jouissance supprimmée avec succès');

            return $this->redirect(['index']);

        }
    }

    public function actionDelete2($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = $this->findModel($id);

        $nodelete = array('V','R','S');

        if(in_array($model->STATUT,$nodelete)) {

            Yii::$app->session->setFlash('error', 'Vous ne pouvez pas supprimer une jouissance de congés déja opérationnel');

            return $this->redirect(['index']);
        }

        else {

            $logs = new Loges();
            $logs->DATEOP = date("Y-m-d H:i:s");
            $logs->USERID = Yii::$app->user->identity->IDUSER;
            $logs->USERNAME = Yii::$app->user->identity->NOM;
            $logs->OPERATION = "Suppression jouissance numero ".$id;
            $logs->save(false);

            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', 'Jouissance supprimmée avec succès');

            return $this->redirect(['index']);

        }
    }


    /**
     * Finds the Jouissance model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Jouissance the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Jouissance::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAnnuler($id){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = $this->findModel($id);

        if($model->STATUT != "V") {

            Yii::$app->session->setFlash('error', 'Avant d\'annuler, bien vouloir valider la jouissance');

            return $this->redirect(['jouissance/update', 'id' => $model->IDNATURE, 'table' => 'T6']);
        }

        else {

            $model->DATECANCEL = date("Y-m-d");

            $model->STATUT = "A"; $model->save(false);

            $logs = new Loges();
            $logs->DATEOP = date("Y-m-d H:i:s");
            $logs->USERID = Yii::$app->user->identity->IDUSER;
            $logs->USERNAME = Yii::$app->user->identity->NOM;
            $logs->OPERATION = "Annulation jouissance numero ".$model->IDNATURE;
            $logs->save(false);

            Yii::$app->session->setFlash('success', 'Jouissance annulée avec succès');

            return $this->redirect(['jouissance/update', 'id' => $model->IDNATURE, 'table' => 'T6']);

        }

    }

    public function actionRappeler($id) {

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = $this->findModel($id);

        $setting = \app\models\Parametre::findOne(1);

        $decision = \app\models\Decisionconges::findOne($model->IDDECISION);

        $employe = \app\models\Employe::findOne($decision->MATICULE);

        if($model->TYPES == "01" || $model->TYPES == "02" || $model->TYPES == "04") {

            $date1 = strtotime($model->DEBUT); $date2 = strtotime($model->FIN);

            $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

            $reste = $decision->NBJOUR + $nbjour;

            $decision->NBJOUR = $reste;

            $decision->save(false);

            // au cas au credit congé existant, diminuer cela
            $nj = Jouissance::find()->where(["TYPES"=>"03","IDDECISION"=>$decision->ID_DECISION,"STATUT"=>"V"])->one();
            if($nj != null){
                $employe->SOLDECREDIT = $employe->SOLDECREDIT + $nbjour;
                $employe->save(false);
            }
        }

        $model->DOCUMENT = NULL;

        $model->STATUT = 'B';

        $model->TYPES = NULL;

        $model->save(false);

        // envoi du mail aux responsables et au RH

        Yii::$app->session->setFlash('success', 'Document rappelé avec succès, vous pouvez le modifier et le valider à nouveau');

        return $this->redirect(['jouissance/update', 'id' => $model->IDNATURE, 'table' => 'T6']);

    }

    public function actionValider($id){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = $this->findModel($id); $titre = ''; $texte = '';

        $setting = \app\models\Parametre::findOne(1);

        $decision = \app\models\Decisionconges::findOne($model->IDDECISION);

        $employe = \app\models\Employe::findOne($decision->MATICULE);

        $emploi = \app\models\Emploi::findOne($employe->CODEEMP);

        $date1 = strtotime($decision->DEBUTPLANIF); $date2 = strtotime($decision->FINPLANIF);

        $plateforme = Etablissement::findOne($model->PLATEFORME);

        $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

        $nom = $employe->NOM.' '.$employe->PRENOM;


        $departements = \app\models\Departements::findOne($employe->CODEDPT);

        $etablissement = \app\models\Etablissement::findOne($employe->CODEETS_EMB);

        $directions = \app\models\Direction::findOne($employe->DIRECTION);

        $services = \app\models\Service::findOne($employe->SERVICE);

        if($departements != null) $departement = $departements->LIBELLE; else $departement = '';

        if($directions != null) $direction = $directions->LIBELLE; else $direction = '';

        if($services != null) $service = $services->LIBELLE; else $service = '';

        if($etablissement != null) $lieu = $etablissement->LIBELLE; else $lieu = '';

        $reprise = date('Y-m-d',strtotime($model->FIN.' +1 day'));

        $today = date("Y-m-d");

        $jour_date = date("d M Y", strtotime($today));

        // recherche et confirmation de la validation

        if($model->TYPES == "01") {
            
            $titre = $model->TITRE;

            $texte = $setting->JOUISSANCE1;

            $civile = \app\models\Civilite::findOne($employe->CODECIV);
        
            $texte = str_replace('{nbjour}',$nbjour,$texte); 
            $texte = str_replace('{civilite}',$civile->LIBELLE,$texte);
            $texte = str_replace('{nom}',$nom,$texte); 
            $texte = str_replace('{matricule}',$employe->MATRICULE,$texte); 
            $texte = str_replace('{poste}',$emploi->LIBELLE,$texte); 
            $texte = str_replace('{departement}',$departement,$texte);
            $texte = str_replace('{direction}',$direction,$texte);
            $texte = str_replace('{plateforme}',$lieu,$texte);
            $texte = str_replace('{service}',$service,$texte);
            $texte = str_replace('{signataire}',$model->SIGNATAIRE,$texte);
            $texte = str_replace('{decision}',$decision->REF_DECISION,$texte); 
            $texte = str_replace('{datevalid}',$this->trueDate($decision->DATEVAL),$texte); 
            $texte = str_replace('{datedebut}',$this->trueDate2($model->DEBUT),$texte);
            $texte = str_replace('{datefin}',$this->trueDate2($model->FIN),$texte);
            $texte = str_replace('{datereprise}',$this->trueDate($reprise),$texte);

            $date1 = strtotime($model->DEBUT); $date2 = strtotime($model->FIN);

            $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

            $reste = $decision->NBJOUR - $nbjour;

            $decision->NBJOUR = $reste;

            $decision->save(false);

            // au cas au credit congé existant, diminuer cela
            $nj = Jouissance::find()->where(["TYPES"=>"03","IDDECISION"=>$decision->ID_DECISION,"STATUT"=>"V"])->one();
            if($nj != null){
                $employe->SOLDECREDIT = $employe->SOLDECREDIT - $nbjour;
                $employe->save(false);
            }
        }

        else if($model->TYPES == "02")  {
            
            $titre = $model->TITRE;

            $texte = $setting->JOUISSANCE2;

            $d1 = strtotime($model->DEBUT); $d2 = strtotime($model->FIN);

            $diff2 = $d2 - $d1; $nbjour1 = abs(round($diff2/86400)) + 1;

            $civile = \app\models\Civilite::findOne($employe->CODECIV);

            $texte = str_replace('{nbjour}',$nbjour,$texte); 
            $texte = str_replace('{nbpartiel}',$nbjour1,$texte); 
            $texte = str_replace('{civilite}',$civile->LIBELLE,$texte);
            $texte = str_replace('{nom}',$nom,$texte); 
            $texte = str_replace('{matricule}',$employe->MATRICULE,$texte); 
            $texte = str_replace('{poste}',$emploi->LIBELLE,$texte);
            $texte = str_replace('{departement}',$departement,$texte);
            $texte = str_replace('{direction}',$direction,$texte);
            $texte = str_replace('{plateforme}',$lieu,$texte);
            $texte = str_replace('{service}',$service,$texte);
            $texte = str_replace('{signataire}',$model->SIGNATAIRE,$texte);
            $texte = str_replace('{decision}',$decision->REF_DECISION,$texte); 
            $texte = str_replace('{datevalid}',$this->trueDate($decision->DATEVAL),$texte); 
            $texte = str_replace('{datedebut}',$this->trueDate2($model->DEBUT),$texte); 
            $texte = str_replace('{datefin}',$this->trueDate2($model->FIN),$texte); 
            $texte = str_replace('{datereprise}',$this->trueDate($reprise),$texte); 

            $reste = $decision->NBJOUR - $nbjour1;

            $decision->NBJOUR = $reste; $decision->save(false);

            // au cas au credit congé existant, diminuer cela
            $nj = Jouissance::find()->where(["TYPES"=>"03","IDDECISION"=>$decision->ID_DECISION,"STATUT"=>"V"])->one();
            if($nj != null){
                $employe->SOLDECREDIT = $employe->SOLDECREDIT - $nbjour1;
                $employe->save(false);
            }

        }

        else if($model->TYPES == "03") {
            
            $titre = $model->TITRE;

            $texte = $setting->JOUISSANCE3;

            $civile = \app\models\Civilite::findOne($employe->CODECIV);

            $texte = str_replace('{nbjour}',$nbjour,$texte); 
            $texte = str_replace('{civilite}',$civile->LIBELLE,$texte);
            $texte = str_replace('{nom}',$nom,$texte); 
            $texte = str_replace('{matricule}',$employe->MATRICULE,$texte); 
            $texte = str_replace('{poste}',$emploi->LIBELLE,$texte);
            $texte = str_replace('{departement}',$departement,$texte);
            $texte = str_replace('{direction}',$direction,$texte);
            $texte = str_replace('{plateforme}',$lieu,$texte);
            $texte = str_replace('{service}',$service,$texte);
            $texte = str_replace('{signataire}',$model->SIGNATAIRE,$texte);
            $texte = str_replace('{decision}',$decision->REF_DECISION,$texte); 
            $texte = str_replace('{datevalid}',$this->trueDate($decision->DATEVAL),$texte); 
            $texte = str_replace('{datedebut}',$this->trueDate2($decision->DEBUTPLANIF),$texte); 
            $texte = str_replace('{datefin}',$this->trueDate2($decision->FINPLANIF),$texte); 
            $texte = str_replace('{datereprise}',$this->trueDate($decision->DATEREPRISE),$texte); 

            $reste = $decision->NBJOUR - 0;

            $decision->NBJOUR = $reste; $decision->save(false);

            $employe->SOLDECREDIT = $employe->SOLDECREDIT + $reste;
            $employe->save(false);

        }

        else if($model->TYPES == "04") {

            $previous = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION,'TYPES'=>'02'])->one();

            $date1 = strtotime($previous->DEBUT); $date2 = strtotime($previous->FIN);

            $diff = $date2 - $date1; $nbjour1 = abs(round($diff/86400)) + 1;

            $titre = $model->TITRE; $reste = $nbjour - $nbjour1;

            $texte = $setting->JOUISSANCE4;

            $civile = \app\models\Civilite::findOne($employe->CODECIV);

            $texte = str_replace('{nbjour}',$nbjour,$texte);
            $texte = str_replace('{reste}',$reste,$texte);
            $texte = str_replace('{civilite}',$civile->LIBELLE,$texte);
            $texte = str_replace('{nom}',$nom,$texte);
            $texte = str_replace('{matricule}',$employe->MATRICULE,$texte);
            $texte = str_replace('{poste}',$emploi->LIBELLE,$texte);
            $texte = str_replace('{departement}',$departement,$texte);
            $texte = str_replace('{direction}',$direction,$texte);
            $texte = str_replace('{plateforme}',$lieu,$texte);
            $texte = str_replace('{service}',$service,$texte);
            $texte = str_replace('{signataire}',$model->SIGNATAIRE,$texte);
            $texte = str_replace('{decision}',$decision->REF_DECISION,$texte);
            $texte = str_replace('{datevalid}',$this->trueDate($decision->DATEVAL),$texte);
            $texte = str_replace('{datedebut}',$this->trueDate2($model->DEBUT),$texte);
            $texte = str_replace('{datefin}',$this->trueDate2($model->FIN),$texte);
            $texte = str_replace('{datereprise}',$this->trueDate($reprise),$texte);

            $reste1 = $decision->NBJOUR - $reste;

            $decision->NBJOUR = $reste1; $decision->save(false);

            // au cas au credit congé existant, diminuer cela
            $nj = Jouissance::find()->where(["TYPES"=>"03","IDDECISION"=>$decision->ID_DECISION,"STATUT"=>"V"])->one();
            if($nj != null){
                $employe->SOLDECREDIT = $employe->SOLDECREDIT - $reste;
                $employe->save(false);
            }
        }

        else if($model->TYPES == "05") {

            $previous = Jouissance::find()->where(['INATURE'=>$model->NUMERO])->one();

            $date1 = strtotime($previous->DEBUT); $date2 = strtotime($previous->FIN);

            $diff = $date2 - $date1; $nbjour1 = abs(round($diff/86400)) + 1;

            $datereprise = date('Y-m-d',strtotime($model->FINREPORT.' +1 day'));

            if($previous->TYPES == "01") $text = "jouissance";

            else $text = "jouissance partielle";

            $civile = \app\models\Civilite::findOne($employe->CODECIV);

            $titre = $model->TITRE;

            $texte = $setting->JOUISSANCE5;

            $texte = str_replace('{nbjour}',$nbjour1,$texte);
            $texte = str_replace('{jouissance}',$text,$texte);
            $texte = str_replace('{civilite}',$civile->LIBELLE,$texte);
            $texte = str_replace('{nom}',$nom,$texte);
            $texte = str_replace('{matricule}',$employe->MATRICULE,$texte);
            $texte = str_replace('{poste}',$emploi->LIBELLE,$texte);
            $texte = str_replace('{departement}',$departement,$texte);
            $texte = str_replace('{direction}',$direction,$texte);
            $texte = str_replace('{plateforme}',$lieu,$texte);
            $texte = str_replace('{service}',$service,$texte);
            $texte = str_replace('{signataire}',$model->SIGNATAIRE,$texte);
            $texte = str_replace('{decision}',$decision->REF_DECISION,$texte);
            $texte = str_replace('{datevalid}',$this->trueDate($decision->DATEVAL),$texte);
            $texte = str_replace('{datedebut}',$this->trueDate2($model->DEBUTREPORT),$texte);
            $texte = str_replace('{datefin}',$this->trueDate2($model->FINREPORT),$texte);
            $texte = str_replace('{datedebut1}',$this->trueDate2($previous->DEBUT),$texte);
            $texte = str_replace('{datefin1}',$this->trueDate2($previous->FIN),$texte);
            $texte = str_replace('{datereprise}',$this->trueDate($datereprise),$texte);

            $reste = $decision->NBJOUR - 0;

            $decision->NBJOUR = $reste; $decision->save(false);
        }

        $builder = '<html>
        <head><title>'.$titre.' -  '.$model->NUMERO.'</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">';

        $builder.='<tr><td colspan="2" height="140"></td></tr>';

        $builder.='<tr><td colspan="2" height="30px" style="text-align: center; padding-top: 10px; padding-left: 10px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px; font-size:21px; border:1px solid #000000">'.$model->TITRE.'</td></tr>';

        $builder.='<tr><td colspan="2" height="20px" style="text-align: center; padding-top: 10px; font-size:14px; line-height: 20px">N° '.$model->NUMERO.'</td></tr> <tr><td colspan="2" height="20px"></td></tr>';

        $builder.='<tr><td colspan="2" height="60px" style="text-align: justify; padding-top: 10px; padding-left: 10px; padding-bottom: 10px; padding-right: 10px; font-size:16px;  line-height: 28px">'.nl2br($texte).'</td></tr> <tr><td colspan="2" height="30px"></td></tr>';

        $builder.='<tr><td colspan="2" height="20px"></td></tr><tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal; font-style: italic; text-align: left" >Fait &agrave; '.$plateforme->LIBELLE.' le  '.self::trueDate2($today).'</td></tr>';

        $builder.='<tr><td colspan="2" height="20px"></td></tr><tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: bold; text-transform: uppercase; text-align:left">'.$model->SIGNATAIRE.'</td></tr>';


        if($employe->CODEETS == "DLA") {

            if (strpos($employe->DIRECTION, 'Exploitation') !== false) {

                $ampliation = Ampliation::findOne(8);
            }

            else {
                $ampliation = Ampliation::findOne(3);
            }

        }

        else if($employe->CODEETS == "NSI") {

            if (strpos($employe->DIRECTION, 'International') !== false) {

                $ampliation = Ampliation::findOne(7);
            }

            else {
                $ampliation = Ampliation::findOne(9);
            }
        }

        else  $ampliation = Ampliation::findOne(['VILLE'=>$employe->CODEETS]);

        if($ampliation != null) $tamp = $ampliation->CONTENU; else $tamp = "";

        $builder.='<tr>
        <td colspan="2" height="30px" style="text-align: left; padding-left: 200px"> <b>'.$model->DOCUMENT4.'</b><br>   </td></tr>
        
        <tr><td height="50px" style="text-align: left; padding: 10px;">
        <u><b>Copies</b></u><br>
        <div style="margin-left: 30px; font-size: 11px">'.nl2br($tamp).'</div>
        </td>
        <td style="text-align: left"></td></tr>
        ';

        $builder.='</table></body></html>';

        $filename = 'jouissance-'.$model->IDNATURE.'.pdf';

        $repertoire = '../web/uploads';

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            //'content' => $builder,
            // set mPDF properties on the fly
            'options' => ['title' => 'Jouissance numero '.$model->IDNATURE],
            'methods' => [
                'SetTitle' => 'Jouissance de congés',
                'SetSubject' => '',
                'SetAuthor' => 'ADC',
                'SetCreator' => 'ADC',
                'SetKeywords' => 'ADC',
                'SetHeader'=>['<div style="text-align: left;  height: 100px"><img src="../web/img/logo1.png" width="220px" height="auto" /></div>'],
                'SetFooter'=>['<div style="text-align: center; color:#000000;  border-top:1px solid #000000;   font-size: 12px"><br>Siège Social : Aéroport International de Yaoundé – Nsimalen – BP 13615 Yaoundé<br>Tél. : (237) 222 23 36 02 / 222 23 45 21 – Fax (237) 222 23 45 20</div>'],
            ],
        ]);

        $mpdf = $pdf->api;

        $mpdf->SetHTMLHeader('<div style="text-align: left;  height: 100px"><img src="../web/img/logo1.png" width="220px" height="auto" /></div>',true);

        $mpdf->SetHTMLFooter('<div style="text-align: center; color:#000000; border-top:1px solid #000000;  font-size: 12px"><br>Siège Social : Aéroport International de Yaoundé – Nsimalen – BP 13615 Yaoundé<br>Tél. : (237) 222 23 36 02 / 222 23 45 21 – Fax (237) 222 23 45 20</div>',true);

        $mpdf->WriteHtml($builder);

        $path = $mpdf->Output('', 'S');

        $model->DOCUMENT = $filename;

        $model->STATUT = 'V';

        $model->save(false); 

        echo $mpdf->Output($repertoire."/".$filename, 'F');

        // envoi du mail aux responsables et au RH

        Yii::$app->session->setFlash('success', 'Document crée avec succès : <a href="../web/uploads/'.$filename.'" target="_blank">Ouvrir</a>');

        return $this->redirect(['jouissance/update', 'id' => $model->IDNATURE, 'table' => 'T6']);


    }


    public function trueDate($jour){

        $tab = explode(" ",$jour);

        $day = $tab[0]; $tab2 = explode("-",$day);

        $mois = array("01"=>"Janvier","02"=>"Fevrier","03"=>"Mars","04"=>"Avril","05"=>"Mai","06"=>"Juin","07"=>"Juillet","08"=>"Aout","09"=>"Septembre","10"=>"Octobre","11"=>"Novembre","12"=>"Decembre");

        $finalj = $tab2[2]." ".$mois[$tab2[1]]." ".$tab2[0];

        return $finalj;
    }

    public function trueDate2($jour){

         $tab2 = explode("-",$jour);

        $mois = array("01"=>"Janvier","02"=>"Fevrier","03"=>"Mars","04"=>"Avril","05"=>"Mai","06"=>"Juin","07"=>"Juillet","08"=>"Aout","09"=>"Septembre","10"=>"Octobre","11"=>"Novembre","12"=>"Decembre");

        $finalj = $tab2[2]." ".$mois[$tab2[1]]." ".$tab2[0];

        return $finalj;
    }

    function getJouissanceNumber($exercice){

        $decision = Jouissance::find()->where(['EXERCICE'=>$exercice])->all();

        $total = count($decision); $next = $total + 1;

        $position = 4 - strlen($next);

        $retour = "";

        for($i=1; $i<=$position; $i++){

            $retour.="0";
        }

        return $retour."".$next;
    }

    // non jouissance

    public function actionCreate1(){

        $model = new Jouissance(); $model->STATUT = 'B';

        if ($model->load(Yii::$app->request->post())) {

            $decision = Decisionconges::findOne($model->IDDECISION);

            $decision = Decisionconges::findOne($model->IDDECISION);

            if($decision == null) {

                Yii::$app->session->setFlash('error', 'Aucune correspondance pour votre décision de congés.');

                return $this->redirect(['create1']);

            }

            else {

                $currentyear  = date("Y");

                $past = $currentyear - $decision->ANNEEEXIGIBLE;

                if($past > 3) {

                    Yii::$app->session->setFlash('error', 'La décision choisie a plus de 3 ans d\'existance.');

                    return $this->redirect(['create']);

                }

                else {

                    $model->DATECREATION = date("Y-m-d");

                    $model->USERCREATE = Yii::$app->user->identity->IDUSER;

                    $model->EXERCICE = $decision->ANNEEEXIGIBLE;

                    $model->DOCUMENTFILE = UploadedFile::getInstance($model, 'DOCUMENTFILE');

                    if ($model->DOCUMENTFILE != null) {

                        $model->DOCUMENT2 = $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension;
                    }

                    $decision = \app\models\Decisionconges::findOne($model->IDDECISION);

                    if($decision == null) {

                        Yii::$app->session->setFlash('error', 'Aucune occurence pour cette décision.');

                        return $this->redirect(['create1']);
                    }

                    else {

                        $date1d = strtotime($model->DEBUT); $date2d = strtotime($model->FIN);

                        $diffd = $date2d - $date1d; $nbjourd = abs(round($diffd/86400)) + 1;

                        $model->TYPES = "03";

                        $exist = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION, 'TYPES'=>['03','01','02']])->all();

                        if(count($exist) != 0) {

                            Yii::$app->session->setFlash('error', 'Vous avez déjà crée des jouissances ou non jouissances pour cette décision.');

                            return $this->redirect(['create1']);
                        }

                        else {

                            $model->TITRE = 'ATTESTATION DE NON JOUISSANCE DE CONGE';

                            $setting = Parametre::findOne(1);

                            $timbre = "";

                            if($model->TIMBRE != null && !empty($model->TIMBRE)) {
                                $timbre = $model->TIMBRE;
                            }

                            else {
                                $employe = \app\models\Employe::findOne($decision->MATICULE);

                                if ($employe->CODEETS_EMB == "DLA") {
                                    $di = \app\models\Direction::findOne($employe->DIRECTION);
                                    if (($di != null) && strpos($di->LIBELLE, 'Exploitation') !== false) {
                                        $ampliation = \app\models\Ampliation::findOne(8);
                                    } else {
                                        $ampliation = \app\models\Ampliation::findOne(3);
                                    }

                                } else if ($employe->CODEETS_EMB == "NSI") {
                                    $di = \app\models\Direction::findOne($employe->DIRECTION);
                                    if (($di != null) && strpos($di->LIBELLE, 'International') !== false) {
                                        $ampliation = \app\models\Ampliation::findOne(7);
                                    } else {
                                        $ampliation = \app\models\Ampliation::findOne(9);
                                    }
                                } else  $ampliation = \app\models\Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);

                                if(($ampliation->TIMBRE != null) && !empty($ampliation->TIMBRE) ) $timbre = $ampliation->TIMBRE;

                                else $timbre = $setting->TIMBREJOUISSANCE;

                                $timbre = $timbre."".Yii::$app->user->identity->INITIAL;;
                            }

                            $numero = $this->getDecisionNumber($decision->ANNEEEXIGIBLE)." - ".$timbre;

                            $model->NUMERO = $numero;

                            $model->save(false);

                          //  $numero = $this->getJouissanceNumber($model->EXERCICE);

                            $model->IDUSER = Yii::$app->user->identity->IDUSER;

                            $model->DOCUMENT3 = $model->timbre;
                            $model->DOCUMENT4 = $model->signataire;

                            //$model->NUMERO = $numero." - ".$model->NUMERO;


                            $model->save(false);

                            if ($model->DOCUMENTFILE != null) $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                            Yii::$app->session->setFlash('success', 'Non Jouissance enregistrée avec succès');

                            $logs = new Loges();
                            $logs->DATEOP = date("Y-m-d H:i:s");
                            $logs->USERID = Yii::$app->user->identity->IDUSER;
                            $logs->USERNAME = Yii::$app->user->identity->NOM;
                            $logs->OPERATION = "Création non jouissance numero ".$model->IDNATURE;
                            $logs->save(false);

                            return $this->redirect(['valider', 'id' => $model->IDNATURE]);

                        }


                    }
                }

            }

        }

        return $this->render('create1', [
            'model' => $model,
        ]);
    }

    public function actionCreate2($id){

        $model = $this->findModel($id);

        //$model = new Jouissance();
        if($model->STATUT == "V") {

            $today = date("Y-m-d");

            $date1 = strtotime($model->DEBUT); $date2 = strtotime($today);

            $diff = $date2 - $date1;

            if($diff > 0){

                Yii::$app->session->setFlash('error', 'Vous ne pouvez reporter une jouissance déjà en cours.');

                return $this->redirect(['update','id'=>$id]);
            }

        }


        if ($model->load(Yii::$app->request->post())) {

            $decision = Decisionconges::findOne($model->IDDECISION);

            $model->DOCUMENTFILE2 = UploadedFile::getInstance($model, 'DOCUMENTFILE2');

            if ($model->DOCUMENTFILE2 != null) {

                $model->DOCUMENT3 = $model->DOCUMENTFILE2->baseName . '.' . $model->DOCUMENTFILE2->extension;
            }

            $model->STATUT = "R";

            $exist = Jouissance::find()->where(['IDDECISION'=>$model->IDDECISION,'STATUT'=> 'R'])->all();

            if(count($exist) != 0) {

                Yii::$app->session->setFlash('error', 'Vous avez déja crée des reports pour cette décision.');

                return $this->redirect(['create2']);
            }

            else {

                $date1= strtotime($model->DEBUT); $date2 = strtotime($model->FIN);

                $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

                $datefin = date('Y-m-d', strtotime($model->DEBUTREPORT. ' + '.$nbjour.' days'));

                $model->FINREPORT = $datefin;

                    $model->TITRE = 'CERTIFICAT DE REPORT DE CONGES';

                    $model->save(false);

                if ($model->DOCUMENTFILE2 != null) $model->DOCUMENTFILE2->saveAs('../web/uploads/' . $model->DOCUMENTFILE2->baseName . '.' . $model->DOCUMENTFILE2->extension);

                    if($model->TYPES == "01") $text = "jouissance";

                    else $text = "jouissance partielle";

                $employe = \app\models\Employe::findOne($decision->MATICULE);

                    $civile = \app\models\Civilite::findOne($employe->CODECIV);

                $setting = \app\models\Parametre::findOne(1);

                $emploi = \app\models\Emploi::findOne($employe->CODEEMP);

                $service = \app\models\Departements::findOne($employe->CODEDPT);

                $etablissement = \app\models\Etablissement::findOne($employe->CODEETS_EMB);

                $date1 = strtotime($decision->DEBUTPLANIF); $date2 = strtotime($decision->FINPLANIF);

                $diff = $date2 - $date1; $nbjour1 = abs(round($diff/86400)) + 1;

                $nom = $employe->NOM.' '.$employe->PRENOM;

                if($service != null) $departement = $service->LIBELLE; else $departement = '';

                if($etablissement != null) $lieu = $etablissement->LIBELLE; else $lieu = '';

                $datereprise = date('Y-m-d', strtotime($model->FINREPORT. ' + 1 days'));

                    $titre = "CERTIFICAT DE REPORT DE CONGES";

                    $texte = $setting->JOUISSANCE5;

                    $texte = str_replace('{nbjour}',$nbjour,$texte);
                    $texte = str_replace('{jouissance}',$text,$texte);
                    $texte = str_replace('{jouissance1}',$model->NUMERO,$texte);
                    $texte = str_replace('{jouissance1}',$model->NUMERO,$texte);
                    $texte = str_replace('{datejouissance}',$this->trueDate($model->DATECREATION),$texte);
                    $texte = str_replace('{nom}',$nom,$texte);
                     $texte = str_replace('{civilite}',$civile->LIBELLE,$texte);
                    $texte = str_replace('{matricule}',$employe->MATRICULE,$texte);
                    $texte = str_replace('{poste}',$emploi->LIBELLE,$texte);
                    $texte = str_replace('{service}',$departement,$texte);
                    $texte = str_replace('{numero}',$decision->REF_DECISION,$texte);
                    $texte = str_replace('{datevalid}',$this->trueDate($decision->DATEVAL),$texte);
                    $texte = str_replace('{datedebut}',$this->trueDate2($model->DEBUTREPORT),$texte);
                    $texte = str_replace('{datefin}',$this->trueDate2($model->FINREPORT),$texte);
                    $texte = str_replace('{datedebut1}',$this->trueDate2($model->DEBUT),$texte);
                    $texte = str_replace('{datefin1}',$this->trueDate2($model->FIN),$texte);
                    $texte = str_replace('{datereprise}',$this->trueDate($datereprise),$texte);



                $builder = '<html>
        <head><title>'.$titre.' -  '.$model->NUMERO.'</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">';

                $builder.='<tr><td height="80" style="padding: 5px" style="padding: 5px;" align="left"><img src="../web/img/logo.png" width="200px" height="auto" /></td><td style="padding: 5px" style="padding: 5px; font-weight: bold"></td></tr> <tr><td colspan="2" height="20px"></td></tr>';

                $builder.='<tr><td colspan="2" height="30px" style="text-align: center; padding-top: 10px; padding-left: 10px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px; font-size:21px"><u>'.$model->TITRE.'</u></td></tr> <tr><td colspan="2" height="20px"></td></tr>';

                $builder.='<tr><td colspan="2" height="40px" style="text-align: left; padding-top: 10px; padding-left: 120px; padding-bottom: 10px; padding-right: 10px; font-size:18px; line-height: 20px">N '.$model->NUMERO.'</td></tr> <tr><td colspan="2" height="20px"></td></tr>';

                $builder.='<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 10px; padding-bottom: 10px; padding-right: 10px; font-size:16px;  line-height: 28px">'.nl2br($texte).'</td></tr> <tr><td colspan="2" height="30px"></td></tr>';

               // $ampliation = Ampliation::findOne(['VILLE'=>$employe->CODEETS]);

                if($employe->CODEETS == "DLA") {

                    if (strpos($employe->DIRECTION, 'Exploitation') !== false) {

                        $ampliation = Ampliation::findOne(8);
                    }

                    else {
                        $ampliation = Ampliation::findOne(3);
                    }

                }

                else if($employe->CODEETS == "NSI") {

                    if (strpos($employe->DIRECTION, 'International') !== false) {

                        $ampliation = Ampliation::findOne(7);
                    }

                    else {
                        $ampliation = Ampliation::findOne(9);
                    }
                }

                else  $ampliation = Ampliation::findOne(['VILLE'=>$employe->CODEETS]);

                if($ampliation != null) $tamp = $ampliation->CONTENU; else $tamp = "";

                $builder.='<tr>
        <td colspan="2" height="30px" style="text-align: left; padding-left: 200px"> <b>'.$model->signataire.'</b><br>   </td></tr>
        
        <tr><td height="50px" style="text-align: left; padding: 10px;">
        <u><b>Ampliations</b></u><br>
        <div style="margin-left: 30px; font-size: 11px">'.nl2br($tamp).'</div>
        </td>
        <td style="text-align: left"></td></tr>
        ';

                $builder.='</table></body></html>';

                $filename = 'jouissance-report-'.$model->IDNATURE.'.pdf';

                $repertoire = '../web/uploads';

                $pdf = new Pdf([
                    // set to use core fonts only
                    'mode' => Pdf::MODE_CORE,
                    // A4 paper format
                    'format' => Pdf::FORMAT_A4,
                    // portrait orientation
                    'orientation' => Pdf::ORIENT_PORTRAIT,
                    // stream to browser inline
                    'destination' => Pdf::DEST_BROWSER,
                    // your html content input
                    //'content' => $builder,
                    // set mPDF properties on the fly
                    'options' => ['title' => 'Jouissance report numero '.$model->IDNATURE],
                ]);

                $mpdf = $pdf->api;

                $mpdf->WriteHtml($builder);

                $path = $mpdf->Output('', 'S');

                $model->NUMERO2 = $filename;

                $model->save(false);

                echo $mpdf->Output($repertoire."/".$filename, 'F');

                // envoi du mail aux responsables et au RH

                Yii::$app->session->setFlash('success', 'Report crée avec succes : <a href="../web/uploads/'.$filename.'" target="_blank">Ouvrir</a>');

                $logs = new Loges();
                $logs->DATEOP = date("Y-m-d H:i:s");
                $logs->USERID = Yii::$app->user->identity->IDUSER;
                $logs->USERNAME = Yii::$app->user->identity->NOM;
                $logs->OPERATION = "Création certificat report numero ".$model->IDNATURE;
                $logs->save(false);

                    return $this->redirect(['view', 'id' => $model->IDNATURE]);

            }

        }

        else {

            $joui = Jouissance::findOne($_REQUEST["id"]);
            $dec = Decisionconges::findOne($joui->IDDECISION);
            $emp = Employe::findOne($dec->MATICULE);

            $model->employe = $emp->getFullname(); $model->decision = $dec->getName2();

            $model->jouissances = $joui->getName();

            return $this->render('create2', [
                'model' => $model,
            ]);

        }

    }

    function getDecisionNumber($exercice){

        $decision = Jouissance::find()->where(['EXERCICE'=>$exercice])->all();

        $total = count($decision); $next = $total + 1;

        $position = 4 - strlen($next);

        $retour = "";

        for($i=1; $i<=$position; $i++){

            $retour.="0";
        }

        return $retour."".$next;
    }

    public function actionExport(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new Jouissance();

        $model->scenario = 'export';

        if ($model->load(Yii::$app->request->post())) {

            $absences = Jouissance::find()->where(['EXERCICE'=>$model->EXERCICE])->all();

            $jour = date("Y-m-d");

            $csvfile = "jouissance-".$model->EXERCICE.".csv";

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
            fputcsv($output, array('REFERENCE JOUISSANCE','REFERENCE DECISION','EMPLOYE','DEBUT JOUISANCE','FIN JOUISSANCE', 'TYPE JOUISSANCE','NOMBRE DE JOURS','NOMBRE DE JOURS SUSPENDU','DEBUT REPORT', 'FIN REPORT', 'COMMENTAIRE', 'STATUT','DEPARTEMENT','DIRECTION'),",");

            // creation du fichier d'export

            foreach ($absences as $absence) {

                $decision = \app\models\Decisionconges::findOne($absence->IDDECISION);

                $employe = Employe::findOne($decision->MATICULE);

                // $typeabs = Typeabsence::findOne($absence->CODEABS);

                if($employe->CODEDPT != null) {

                    $mdpt = Departements::findOne($employe->CODEDPT); $dpt = $mdpt->LIBELLE;

                } else $dpt = "";

                $date1 = strtotime($absence->DEBUT); $date2 = strtotime($absence->FIN);

                $diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1; $nbsuspendu = 0;

                $cancel = \app\models\Cancelation::find()->where(['JOUISSANCE'=>$absence->IDNATURE])->all();

                foreach($cancel as $elt) { $nbsuspendu+= $elt->PERIODE; }

              ///  if(empty($absence->EDITION)) $edition = "EN COURS"; else $edition = "BROUILLON";

                $tab2 = array();

                $tab2[] = $absence->NUMERO; $tab2[] = $decision->REF_DECISION; $tab2[] = $employe->getFullname();
                $tab2[] = $absence->DEBUT; $tab2[] = $absence->FIN; $tab2[] = $absence->getType();
                $tab2[] = $nbjour; $tab2[] = $nbsuspendu;
                $tab2[] = $absence->DEBUTREPORT; $tab2[] = $absence->FINREPORT; $tab2[] = $absence->COMMENTAIRE;
                $tab2[] = $absence->getStatut($absence->STATUT);
                $tab2[] = $dpt; $tab2[] = $employe->DIRECTION;

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

    public function actionChecker() {

        $personne = $_REQUEST["personne"]; $tab = array();

        $exo = Exercice::find()->where(["STATUT"=>"O"])->orderBy(["ANNEEEXIGIBLE"=>SORT_DESC])->one();

        if($exo != null) {

            $decisions = Decisionconges::find()->where(["MATICULE"=>$personne,"FICHIER"=>1])->all();

            foreach($decisions as $decision) {

                if($decision->NBJOUR > 0) {

                $tmp = array();
                $tmp["id"] = $decision->ID_DECISION;
                $tmp["libelle"] = $decision->REF_DECISION;
                $tmp["duree"] = $decision->NBJOUR;
                $tmp["debut"] = date("d-m-Y",strtotime($decision->DEBUTPLANIF));
                $tmp["fin"] = date("d-m-Y", strtotime($decision->FINPLANIF));

                $tab[] = $tmp; }
            }

            return json_encode($tab);
        }

        else return "";

    }

    public function actionChecker2() {

        $personne = $_REQUEST["personne"]; $tab = array();

        $exo = Exercice::find()->where(["STATUT"=>"O"])->orderBy(["ANNEEEXIGIBLE"=>SORT_DESC])->one();

        if($exo != null) {

            $decisions = Decisionconges::find()->where(["MATICULE"=>$personne,"FICHIER"=>1])->all();

            $cancels = Cancelation::find()->where(["STATUT"=>0])->all(); $t = array();

            foreach($cancels as $cancel) $t[] = $cancel->JOUISSANCE; $stat = array("V","R");

            foreach($decisions as $decision) {

                    $jouissances = Jouissance::find()->where(["IDDECISION"=>$decision->ID_DECISION,"STATUT"=>$stat])->andWhere(["IDNATURE"=>$t])->all();

                    foreach($jouissances as $jouissance) {

                        $suspensions = Cancelation::find()->where(["JOUISSANCE"=>$jouissance->IDNATURE,"STATUT"=>0])->all();

                        foreach($suspensions as $suspension) {

                            $tmp = array();
                            $tmp["id"] = $suspension->ID;
                            $tmp["libelle"] = $jouissance->NUMERO;
                            $tmp["duree"] = $suspension->PERIODE;
                            $tmp["date1"] = date("d-m-Y",strtotime($jouissance->DATECREATION));
                            $tmp["date2"] = date("d-m-Y", strtotime($suspension->DEBUT));

                            $tab[] = $tmp;
                        }

                    }
            }

            return json_encode($tab);

        }

        else return "";

    }
}
