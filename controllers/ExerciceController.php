<?php

namespace app\controllers;

use app\models\Direction;
use app\models\Etablissement;
use app\models\Service;
use Yii;
use app\models\Exercice;
use app\models\ExerciceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Employe;
use app\models\Parametre;
use app\models\Decisionconges;
use app\models\Absenceponctuel;
use kartik\mpdf\Pdf;
use app\models\Historique;
use app\models\Departements;
use app\models\Loges;
use app\models\Suspension;
use yii\filters\AccessControl;


/**
 * ExerciceController implements the CRUD actions for Exercice model.
 */
class ExerciceController extends Controller
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
     * Lists all Exercice models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $searchModel = new ExerciceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Exercice model.
     * @param integer $id
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

    public function actionOuverture(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $exo = $_REQUEST['exercice'];

        $model = Exercice::find()->where(['ANNEEEXIGIBLE'=>$exo, 'STATUT'=>'B'])->One();

        if($model == NULL) {

            Yii::$app->session->setFlash('error', 'Attention, aucune occurence de trouvée pour cet exercice');

            return $this->redirect(['view','id'=>$exo,'table'=>'T11']);
        }

        else {

            $model->STATUT = "O"; $model->DATEOUVERT = date("Y-m-d");

            $model->save(false);

            $logs = new Loges();
            $logs->DATEOP = date("Y-m-d H:i:s");
            $logs->USERID = Yii::$app->user->identity->IDUSER;
            $logs->USERNAME = Yii::$app->user->identity->NOM;
            $logs->OPERATION = "Ouverture exercice numero ".$exo;
            $logs->save(false);

            Yii::$app->session->setFlash('success', 'Exercice ouvert avec succès');

            return $this->redirect(['view','id'=>$exo,'table'=>'T11']);

        }

    }

    public function actionFermeture(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $exo = $_REQUEST['exercice'];

        $model = Exercice::find()->where(['ANNEEEXIGIBLE'=>$exo, 'STATUT'=>'O'])->One();

        if($model == NULL) {

            Yii::$app->session->setFlash('error', 'Attention, aucune occurence de trouvée pour cet exercice');

            return $this->redirect(['view','id'=>$exo,'table'=>'T11']);
        }

        else {

            $model->STATUT = "F"; $model->DATECLOTURE = date("Y-m-d");

            $model->save(false);

            // mise a jour des infos pour chaque decision de conges

          /*  $decisions = Decisionconges::find()->where(['ANNEEEXIGIBLE'=>$exo,'STATUT'=>'V'])->all();

            foreach($decisions as $decision) {

                $decision->STATUT = "C"; $decision->DATECLOTURE = date("Y-m-d H:i:s");

                $decision->save(false); $jourdue = 0; $jourfait = 0; $jourprogramme = 0;

                $datetime2 = new \DateTime($decision->FINPERIODE); 
                $datetime1 = new \DateTime($decision->DEBUTPERIODE);
                $difference = $datetime1->diff($datetime2);
                $jourdue+= (int)$difference->d;

                $datetim2 = new \DateTime($decision->FINREEL); 
                $datetim1 = new \DateTime($decision->DEBUTREELL);
                $differenc = $datetim1->diff($datetim2);
                $jourfait+= (int)$differenc->d;

                $dateti2 = new \DateTime($decision->FINPLANIF);
                $dateti1 = new \DateTime($decision->DEBUTPLANIF);
                $differen = $dateti1->diff($dateti2);
                $jourprogramme+= (int)$differen->d;

                $absencej = 0;
                
                $absences = Absenceponctuel::find()->where(['ANNEEEXIGIBLE'=>$exo,'STATUT'=>'V','IMPUTERCONGES'=>1,'MATICULE'=>$decision->MATICULE])->all();

                foreach($absences as $absence) {

                $date2 = new \DateTime($absence->DATEFIN); 
                $date1 = new \DateTime($absence->DATEDEBUT);
                $diffe = $date1->diff($date2);
                $absencej+= (int)$diffe->d;

                }

                $employe = Employe::findOne($decision->MATICULE);

                // calcul du nombre jour de conges pris

                if($jourdue >= $jourfait) {

                    $reste1 = (int)($jourdue - $jourfait); $reste2 = 0;

                    if($jourprogramme >= $jourdue) {

                        $reste2 = $jourprogramme - $jourdue;
                    }

                    $total = $reste1 + $reste2;  // en solde

                    if($total > $absencej) {

                        $dif = (int)($total - $absencej);

                        $employe->SOLDECREDIT = $dif; $employe->save(false);
                    }

                    else if($total < $absencej) {

                        $dif = (int)($absencej - $total);

                        $employe->SOLDEAVANCE = $dif; $employe->save(false);

                    }
                    
                
                }

                else {

                    $reste1 = (int)($jourfait - $jourdue); $reste2 = 0;

                    if($jourprogramme >= $jourdue) {

                        $reste2 = $jourprogramme - $jourdue;
                    }

                    //$total = (int)($reste1 - $reste2);

                    if($reste1 >= $reste2) {

                        $dif = (int)($reste1 - $reste2);

                        $total = $dif + $absencej;  $employe->SOLDEAVANCE = $total; $employe->save(false);

                    }
                    
                    else {

                        $dif = (int)($reste2 - $reste1);

                        if($absencej >= $dif) {

                            $dif = (int)($absencej - $dif);

                            $employe->SOLDEAVANCE = $dif; $employe->save(false);
                        }

                        else {

                            $dif = (int)($dif - $absencej);

                            $employe->SOLDECREDIT = $dif; $employe->save(false);

                        }

                    }
                }

            } */

            $logs = new Loges();
            $logs->DATEOP = date("Y-m-d H:i:s");
            $logs->USERID = Yii::$app->user->identity->IDUSER;
            $logs->USERNAME = Yii::$app->user->identity->NOM;
            $logs->OPERATION = "Cloture exercie numero ".$exo;
            $logs->save(false);

            Yii::$app->session->setFlash('success', 'Exercice cloturé avec succès');

            return $this->redirect(['view','id'=>$exo,'table'=>'T11']);

        }

    }

    public function actionBuild(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $setting = Parametre::findOne(1);

        //$exo = $_REQUEST['cle'];
        $jour = $_REQUEST['jour'];
        $jour2 = $_REQUEST['jour2'];
        $exo = $_REQUEST['exercice1'];
        $service = $_REQUEST['service'];
        $departement = $_REQUEST['departement'];
        $direction = $_REQUEST['direction'];
        $suffix = $setting->SUFFIXEREF;



        $retour = '<table width="100%"  border="1"><tr style="background-color:#666666; border:1px solid #ffffff; color:#000000; font-weight: bold"><td style="padding: 5px" style="padding: 5px">MATRICULE</td><td style="padding: 5px">EMPLOYE</td><td style="padding: 5px" width="150">DATE DE DEBUT</td><td  style="padding: 5px" width="150">DATE DE FIN</td><td  style="padding: 5px">PERMISSION</td><td  style="padding: 5px">DIRECTION</td><td  style="padding: 5px">DEPARTEMENT</td><td  style="padding: 5px">SERVICE</td></tr>';

        $model = Exercice::find()->where(['ANNEEEXIGIBLE' => $exo])->One();

        $nb = 0; $lg = 0;

        if($model != NULL){

            $e1 = new \DateTime($model->DATEBEDUT);  $e2 = new \DateTime($model->DATEFIN);

            $c1 = new \DateTime($jour);  $c2 = new \DateTime($jour2);

            if(($c1 < $e1) || ($c2 > $e2)) {

                $retour.='</table>@@-1@@La période d\'émission doit appartenir à l\'excercie';

                return $retour;

            }

            else if($c1 > $c2) {

                $retour.='</table>@@-1@@La date de début ne peut être supérieure à la date de fin';

                return $retour;
            }

            else {

                $query = "select * from employe where STATUT = 1 ";

                if($service != 0) {
                    $query.=" and SERVICE = $service ";
                }

                if($direction != 0) {
                    $query.=" and DIRECTION = $direction ";
                }

                if($departement != 0) {
                    $query.=" and CODEDPT = '$departement' ";
                }

                $query.=" order by NOM asc ";

                $employes = Employe::findBySql("$query")->all();

                $setting = Parametre::findOne(1);

                foreach ($employes as $employe) {

                    // determination de la position du CDD

                    $cree = 0;

                    if($employe->CODECONT == "C.D.D. ") {

                        $date_start = ($employe->DATEEMBAUCHE." 00:00:00");
                        $date_stop = date("Y-m-d H:i:s") ;

                        $date_start = new \DateTime($date_start);
                        $date_stop = new \DateTime($date_stop);

                        $diff = $date_stop->diff($date_start);

                        if($diff->y < 1) $cree = 1;
                    }

                    if($cree == 0) {

                        $conge = Decisionconges::find()->where(['MATICULE' => $employe->MATRICULE, 'ANNEEEXIGIBLE' => $exo])->One();

                        if ($conge == NULL) {

                            $d1 = new \DateTime($jour);
                            $d2 = new \DateTime($jour2);

                            if($employe->DATECALCUL != null) {

                                $debut = $employe->DATECALCUL;

                                $d3 = new \DateTime($employe->DATECALCUL);

                                if ($d3 >= $d1 && $d3 <= $d2) {

                                    $nbpermission = $employe->SOLDEAVANCE;

                                    if($employe->CODEEMP == 273 || $employe->CODEEMP == 462) $nbjour = $setting->DUREECONGES2 - $nbpermission;
                                    else $nbjour = $setting->DUREECONGES - $nbpermission;

                                    $direction2 = Direction::findOne($employe->DIRECTION);
                                    $service2 = Service::findOne($employe->SERVICE);
                                    $mdpt = Departements::findOne($employe->CODEDPT);

                                    $direction_value = $direction2 != null ? $direction2->LIBELLE:"";
                                    $service_value = $service2 != null ? $service2->LIBELLE:"";
                                    $dpt_value = $mdpt != null ? $mdpt->LIBELLE:"";

                                    if ($nbjour >= 0) {

                                        $datefin = date('d/m/Y', strtotime($debut . ' + ' . ($nbjour - 1) . ' days'));
                                        $datedebut = date('d/m/Y', strtotime($debut));


                                        if($lg % 2 == 0)   $retour .= '<tr style="background-color:#ffffff; border:1px solid #dededc; color:#000000"><td style="padding: 5px">' . $employe->MATRICULE . '</td><td style="padding: 5px">' . $employe->NOM . ' ' . $employe->PRENOM . '</td><td style="padding: 5px">' . $datedebut . '</td><td style="padding: 5px">' . $datefin . '</td><td  style="padding: 5px" align="center">'.$nbpermission.'</td><td  style="padding: 5px">'.$direction_value.'</td><td  style="padding: 5px">'.$dpt_value.'</td><td  style="padding: 5px">'.$service_value.'</td></tr>';

                                        else $retour .= '<tr style="background-color:#dededc; border:1px solid #dededc; color:#000000"><td style="padding: 5px">' . $employe->MATRICULE . '</td><td style="padding: 5px">' . $employe->NOM . ' ' . $employe->PRENOM . '</td><td style="padding: 5px">' . $datedebut . '</td><td style="padding: 5px">' . $datefin . '</td><td  style="padding: 5px" align="center">'.$nbpermission.'</td><td  style="padding: 5px">'.$direction_value.'</td><td  style="padding: 5px">'.$dpt_value.'</td><td  style="padding: 5px">'.$service_value.'</td></tr>';


                                        $nb++; $lg++;
                                    }
                                }
                            }

                        }

                    }
                }

                $retour.='</table>@@'.$nb.'@@'.$jour.'@@'.$jour2.'@@'.$service.'@@'.$exo."@@".$suffix."@@".$direction."@@".$service;

                return $retour;

            }

        }

    }

    public function actionCreation(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $exo = $_REQUEST['vexercice'];
        $jour = $_REQUEST['vjour'];
        $jour2 = $_REQUEST['vjour2'];
        $service = $_REQUEST['vservice'];
        $departement = $_REQUEST['vdepartement'];
        $direction = $_REQUEST['vdirection'];

        $suffix = $_REQUEST['vsuffix'];

        $model = Exercice::find()->where(['ANNEEEXIGIBLE' => $exo])->One();

        if ($model == NULL) {

            Yii::$app->session->setFlash('error', 'Attention, aucune occurence de trouvée pour cet exercice');

            return $this->redirect(['view', 'id' => $exo, 'table' => 'T11']);

        } else {


            $query = "select * from employe where STATUT = 1 ";

            if($service != 0) {
                $query.=" and SERVICE = $service ";
            }

            if($direction != 0) {
                $query.=" and DIRECTION = $direction ";
            }

            if($departement != 0) {
                $query.=" and CODEDPT = '$departement' ";
            }

            $query.=" order by NOM asc ";

            $employes = Employe::findBySql("$query")->all();

            $nb = 0;

            $setting = Parametre::findOne(1);

            $builder = '<html>
<head><title>Generation projet decisions du '.$jour.' - ADC</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
<table border="1" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:1px solid #000000"><tr><td style="padding: 5px" style="padding: 5px; font-weight: bold">REFERENCE</td><td style="padding: 5px; font-weight: bold" style="padding: 5px; font-weight: bold">MATRICULE</td><td style="padding: 5px; font-weight: bold">EMPLOYE</td><td style="padding: 5px; font-weight: bold">PERIODE DE SERVICE</td><td style="padding: 5px; font-weight: bold">DEBUT CONGE</td><td style="padding: 5px; font-weight: bold">FIN CONGE</td><td style="padding: 5px; font-weight: bold">LIEU D\'EMBAUCHE</td><td style="padding: 5px; font-weight: bold">LIEU D\'AFFECTATION</td><td style="padding: 5px; font-weight: bold">PERMISSIONS DEDUIT (JOUR)</td><td style="padding: 5px; font-weight: bold">CONGES NON PRIS (JOUR)</td></tr>';

            $historique = new Historique();
            $historique->LIBELLE = "Decision de conges ".date("d/m/Y H:i:s");
            $historique->save(false);
            $idhistorique = $historique->ID;


            foreach ($employes as $employe) {

                $cree = 0;

                if($employe->CODECONT == "C.D.D. ") {

                    $date_start = ($employe->DATEEMBAUCHE." 00:00:00");
                    $date_stop = date("Y-m-d H:i:s") ;

                    $date_start = new \DateTime($date_start);
                    $date_stop = new \DateTime($date_stop);

                    $diff = $date_stop->diff($date_start);

                    if($diff->y < 1) $cree = 1;
                }

                if($cree == 0 && $employe->STATUT = 1) {

                    $conge = Decisionconges::find()->where(['MATICULE' => $employe->MATRICULE, 'ANNEEEXIGIBLE' => $exo])->One();

                    if ($conge == NULL) {

                        if($employe->DATECALCUL != null) {

                            $d1 = new \DateTime($jour);
                            $d2 = new \DateTime($jour2);

                            $debut = $employe->DATECALCUL;

                            $d3 = new \DateTime($employe->DATECALCUL);

                            if ($d3 >= $d1 && $d3 <= $d2) {

                                $nbpermission = $employe->SOLDEAVANCE;

                                //$nbjour = $setting->DUREECONGES - $nbpermission;
                                if($employe->CODEEMP == 273 || $employe->CODEEMP == 462) $nbjour = $setting->DUREECONGES2 - $nbpermission;
                                else $nbjour = $setting->DUREECONGES - $nbpermission;

                                if ($nbjour >= 0) {


                                    $datefin = date('Y-m-d', strtotime($debut . ' + ' . ($nbjour - 1) . ' days'));

                                    $decision = new Decisionconges();
                                    $decision->DATEEMIS = date("Y-m-d H:i:s");

                                    $decision->MATICULE = $employe->MATRICULE;
                                    $decision->ANNEEEXIGIBLE = $exo;
                                    $decision->NBJOUR = $nbjour;
                                    $decision->DEBUTPLANIF = $debut;
                                    $decision->FINPLANIF = $datefin;
                                    $decision->DEPARTEMENT = $employe->CODEDPT;
                                    $decision->TYPE_DEC = 0;
                                    $decision->STATUT = "V";
                                    $decision->DATEVAL = date("Y-m-d H:i:s");
                                    $decision->HISTORIQUE = $idhistorique;

                                    $decision->DEBUTPERIODE = $model->DATEBEDUT;
                                    $decision->FINPERIODE = $model->DATEFIN;

                                    $decision->save(false);

                                    if($employe->TIMBRE != null) $code = date("Y")."/".$employe->TIMBRE; else $code = $setting->SUFFIXEREF;

                                    $numero = $this->getDecisionNumber($exo)." - ".$code."".Yii::$app->user->identity->INITIAL;

                                    // $currentd = Dec

                                    $employe->SOLDEAVANCE = 0;

                                    $employe->save(false);

                                    $decision->REF_DECISION = $numero;

                                    $decision->IDUSER = Yii::$app->user->identity->IDUSER;

                                    $decision->save(false);

                                    $finservice = date('Y-m-d',strtotime($employe->DATECALCUL.' -1 day'));

                                    $debutservice = date('Y-m-d',strtotime($employe->LASTCONGE.' +1 day'));

                                    $nb++;

                                    if($employe->CODEETS_EMB != null) {

                                        $ets = Etablissement::findOne($employe->CODEETS_EMB);

                                        $embauche = $ets->LIBELLE;

                                    }

                                    else $embauche = "";

                                    if($employe->CODEETS != null) {

                                        $ets = Etablissement::findOne($employe->CODEETS);

                                        $emploie = $ets->LIBELLE;

                                    }

                                    else $emploie = "";

                                    $debut = date('d/m/Y', strtotime($debut));
                                    $datefin = date('d/m/Y', strtotime($datefin));
                                    $debutservice = date('d/m/Y', strtotime($debutservice));
                                    $finservice = date('d/m/Y', strtotime($finservice));

                                    $builder .= '<tr style="border-bottom: 1px solid #ffffff"><td style="padding: 5px">' . $numero . '</td><td style="padding: 5px">' . $employe->MATRICULE . '</td><td style="padding: 5px">' . $employe->NOM . ' ' . $employe->PRENOM . '</td><td style="padding: 5px">Du '.$debutservice.' au '.$finservice.'</td><td style="padding: 5px">' . $debut . '</td><td style="padding: 5px">' . $datefin . '</td><td style="padding: 5px">' . $emploie . '</td><td style="padding: 5px">' . $embauche . '</td><td style="padding: 5px">' . $nbpermission . '</td><td style="padding: 5px">' . $employe->SOLDECREDIT . '</td></tr>';

                                }

                                else {

                                   $employe->SOLDEAVANCE = abs($nbjour);
                                   $employe->save(false);

                                }


                            } //else echo "test";
                        }

                    }

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

            $logs = new Loges();
            $logs->DATEOP = date("Y-m-d H:i:s");
            $logs->USERID = Yii::$app->user->identity->IDUSER;
            $logs->USERNAME = Yii::$app->user->identity->NOM;
            $logs->OPERATION = "Generation de ".$nb." decisions de conges pour l'historique numero ".$idhistorique;
            $logs->save(false);

            echo $mpdf->Output($repertoire."/".$filename, 'F');

            Yii::$app->session->setFlash('success', $nb . ' décisions crées avec succès : <a href="../web/uploads/'.$filename.'" target="_blank">Ouvrir le PDF </a>');

            return $this->redirect(['decisionconges/index', 'id' => $exo, 'table' => 'T6']);
        }
    }

    public function actionCheck(){

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $exo = $_REQUEST['exercice'];

        $exercice = Exercice::findOne($exo);

        if($exercice != null) {

            return $exercice->DATEBEDUT.":".$exercice->DATEFIN;
        }

        else return "0:0";
    }
    
    public function actionDecision(){

          //  $exo = $_REQUEST['exercice'];

           // $model = $this->findModel($exo);

           // , [ 'model' => $model, ]

            return $this->render('decision');

    }

    /**
     * Creates a new Exercice model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new Exercice();

        if ($model->load(Yii::$app->request->post())) {

            // controle sur l'existence de l'exercie

            $exist = Exercice::findOne($model->ANNEEEXIGIBLE);

            if($exist != NULL) {

                Yii::$app->session->setFlash('error', 'Attention, un exercice existe déjà pour cette année');

                return $this->redirect(['index','table'=>'T11']);
            }

            else {

                $model->STATUT = 'B';

                $model->save(false);

                Yii::$app->session->setFlash('success', 'Exercice crée avec succès');

                $logs = new Loges();
                $logs->DATEOP = date("Y-m-d H:i:s");
                $logs->USERID = Yii::$app->user->identity->IDUSER;
                $logs->USERNAME = Yii::$app->user->identity->NOM;
                $logs->OPERATION = "Création exercice numero ".$model->ANNEEEXIGIBLE;
                $logs->save(false);

                return $this->redirect(['update', 'id' => $model->ANNEEEXIGIBLE,'table'=>'T11']);
                
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Exercice model.
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

            Yii::$app->session->setFlash('success', 'Exercice enregistré avec succès');

            return $this->redirect(['update', 'id' => $model->ANNEEEXIGIBLE,'table'=>'T11']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Exercice model.
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

        if($model->STATUT != "B") {

            Yii::$app->session->setFlash('error', 'Vous ne pouvez pas supprimer un exercice déjà operationnel');


        }

        else {

            $this->findModel($id)->delete();

            $logs = new Loges();
            $logs->DATEOP = date("Y-m-d H:i:s");
            $logs->USERID = Yii::$app->user->identity->IDUSER;
            $logs->USERNAME = Yii::$app->user->identity->NOM;
            $logs->OPERATION = "Suppression exercie numero ".$model->ANNEEEXIGIBLE;
            $logs->save(false);

            Yii::$app->session->setFlash('success', 'Exercice supprimé avec succès');

        }

        return $this->redirect(['index','table'=>'T11']);
    }

    /**
     * Finds the Exercice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Exercice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Exercice::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    function getDecisionNumber($exercice){

        $decision = Decisionconges::find()->where(['ANNEEEXIGIBLE'=>$exercice])->all();

        $total = count($decision); $next = $total;

        $position = 4 - strlen($next);

        $retour = "";

        for($i=1; $i<=$position; $i++){

            $retour.="0";
        }

        return $retour."".$next;
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
}
