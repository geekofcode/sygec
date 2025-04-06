<?php

namespace app\controllers;

use app\models\Decisionconges;
use app\models\Employe;
use app\models\Jouissance;
use Yii;
use app\models\Cancelation;
use app\models\CancelationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\models\Loges;
use yii\filters\AccessControl;

/**
 * CancelationController implements the CRUD actions for Cancelation model.
 */
class CancelationController extends Controller
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
     * Lists all Cancelation models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $searchModel = new CancelationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Cancelation model.
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
     * Creates a new Cancelation model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new Cancelation();

        if ($model->load(Yii::$app->request->post())) {

            $jouissance = \app\models\Jouissance::findOne($_REQUEST["jouissance"]);

            if($jouissance->STATUT == "R") {

                $debut = $jouissance->DEBUTREPORT; $fin = $jouissance->FINREPORT;
            }

            else {

                $debut = $jouissance->DEBUT; $fin = $jouissance->FIN;
            }

            $e1 = new \DateTime($debut);  $e2 = new \DateTime($fin); $e3 = new \DateTime($model->DEBUT);

            if(($e3 < $e1) || ($e3 > $e2) ) {

                Yii::$app->session->setFlash('error', 'La date de suspension doit être dans la période de jouissance');

                return $this->redirect(['create', 'id' => $_REQUEST["jouissance"]]);
            }

            else {

                $model->DOCUMENTFILE = UploadedFile::getInstance($model, 'DOCUMENTFILE');

                if ($model->DOCUMENTFILE != null) {

                    $model->FICHIER = $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension;
                }

                $model->IDUSER = Yii::$app->user->identity->IDUSER;

                $model->JOUISSANCE = $_REQUEST["jouissance"];

                $model->DATECREATION = date("Y-m-d");

                $model->save(false);

                $jouissance = Jouissance::findOne($model->JOUISSANCE);
                $decision = Decisionconges::findOne($jouissance->IDDECISION);
                $employe = Employe::findOne($decision->MATICULE);

                 $employe->SOLDECREDIT = $employe->SOLDECREDIT + $model->PERIODE; $employe->save(false);

                if ($model->DOCUMENTFILE != null) $model->DOCUMENTFILE->saveAs('../web/uploads/' . $model->DOCUMENTFILE->baseName . '.' . $model->DOCUMENTFILE->extension);

                Yii::$app->session->setFlash('success', 'Suspension enregistrée avec succès');


                $logs = new Loges();
                $logs->DATEOP = date("Y-m-d H:i:s");
                $logs->USERID = Yii::$app->user->identity->IDUSER;
                $logs->USERNAME = Yii::$app->user->identity->NOM;
                $logs->OPERATION = "Création suspension numero ".$model->ID;
                $logs->save(false);

                return $this->redirect(['view', 'id' => $model->ID]);

            }

        }

        else {

            $joui = Jouissance::findOne($_REQUEST["id"]);
            $dec = Decisionconges::findOne($joui->IDDECISION);
            $emp = Employe::findOne($dec->MATICULE);

            $model->employe = $emp->getFullname(); $model->decision = $dec->getName2();

            $model->jouissances = $joui->getName();

            return $this->render('create', [
                'model' => $model,
            ]);
        }

    }

    /**
     * Updates an existing Cancelation model.
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            Yii::$app->session->setFlash('success', 'Suspension enregistrée avec succès');

            return $this->redirect(['view', 'id' => $model->ID]);
        }

        else {

            $joui = Jouissance::findOne($model->JOUISSANCE);
            $dec = Decisionconges::findOne($joui->IDDECISION);
            $emp = Employe::findOne($dec->MATICULE);

            $model->employe = $emp->getFullname(); $model->decision = $dec->getName2();

            $model->jouissances = $joui->getName();

            return $this->render('update', [
                'model' => $model,
            ]);
        }


    }

    /**
     * Deletes an existing Cancelation model.
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

        $logs = new Loges();
        $logs->DATEOP = date("Y-m-d H:i:s");
        $logs->USERID = Yii::$app->user->identity->IDUSER;
        $logs->USERNAME = Yii::$app->user->identity->NOM;
        $logs->OPERATION = "Suppression suspension numero ".$id;
        $logs->save(false);

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Cancelation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Cancelation the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Cancelation::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
