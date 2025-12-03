<?php

/* @var $this yii\web\View */

use yii\web\View;
use yii\helpers\Html;
use app\models\Employe;
use app\models\Absenceponctuel;
use app\models\Decisionconges;
use app\models\Jouissance;
use app\models\Arret;
use app\models\Suspension;



$employes = Employe::find()->where(["STATUT"=>"1"])->all(); $nbemployes = count($employes);
$exo = \app\models\Exercice::find()->where(["STATUT"=>"O"])->orderBy(["ANNEEEXIGIBLE"=>SORT_DESC])->one();
if($exo == null) $exo = \app\models\Exercice::find()->where(["STATUT"=>"F"])->orderBy(["ANNEEEXIGIBLE"=>SORT_DESC])->one();
$decisions = Decisionconges::find()->where(['STATUT'=>'V','ANNEEEXIGIBLE'=>$exo->ANNEEEXIGIBLE])->all(); $nbdecision = count($decisions);
$absences = Absenceponctuel::find()->where(['STATUT'=>'V','ANNEEEXIGIBLE'=>$exo->ANNEEEXIGIBLE])->all(); $nbabsence = count($absences);
$jouissances = Jouissance::find()->where(['STATUT'=>'V','EXERCICE'=>$exo->ANNEEEXIGIBLE,'TYPES'=>['01','02']])->all(); $nbjouissance = count($jouissances);
$jouissances1 = Jouissance::find()->where(['STATUT'=>'V','EXERCICE'=>$exo->ANNEEEXIGIBLE,'TYPES'=>['03']])->all(); $nbjouissance1 = count($jouissances1);
$arret = Arret::find()->where(['STATUT'=>'V'])->all(); $nbarret = count($arret);
$suspension = Suspension::find()->where(['STATUT'=>'V','ANNEEEXIGIBLE'=>$exo->ANNEEEXIGIBLE])->all(); $nsuspension = count($suspension);


$this->title = 'Tableau de bord';
?>
<div class="site-index">

    <div class="sortiepesage-view">


       <div style="float: right"> <?php if(Yii::$app->user->identity->NIVEAU == 2) echo Html::a('Etats et Reporting',['/site/page'],['class' => 'btn btn-success btn-flat']) ?> </div><br> <br><br>

        <div class="row">


            <div class="col-lg-3 col-md-6">
                <div class="panel" style="background-color: #e7993c">
                    <div class="panel-heading">
                        <div class="row" align="center">
                            <h1 style="font-size: 20pt; color: #ffffff"><?=number_format($nbemployes, 0, '.', ' ') ?></h1>
                        </div>
                    </div>
                   <div class="panel-footer" style="text-align: center">
                            <span >EMPLOYÉS</span>
                        </div>
                </div>
            </div>


            <div class="col-lg-3 col-md-6">
                <div class="panel" style="background-color: #057a0b">
                    <div class="panel-heading">
                        <div class="row" align="center">
                            <h1 style="font-size: 20pt; color: #ffffff"><?=number_format($nbdecision, 0, '.', ' ') ?></h1>
                        </div>
                    </div>
                   <div class="panel-footer" style="text-align: center">
                            <span >DÉCISION DE CONGÉS</span>
                        </div>
                </div>
            </div>


            <div class="col-lg-3 col-md-6">
                <div class="panel" style="background-color: #9ca69d">
                    <div class="panel-heading">
                        <div class="row" align="center">
                            <h1 style="font-size: 20pt; color: #ffffff"><?=number_format($nbabsence, 0, '.', ' ') ?></h1>
                        </div>
                    </div>
                   <div class="panel-footer" style="text-align: center">
                            <span >PERMISSIONS</span>
                        </div>
                </div>
            </div>


            <div class="col-lg-3 col-md-6">
                <div class="panel" style="background-color: #0099ff">
                    <div class="panel-heading">
                        <div class="row" align="center">
                            <h1 style="font-size: 20pt; color: #ffffff"><?=number_format($nbjouissance, 0, '.', ' ') ?></h1>
                        </div>
                    </div>
                 <div class="panel-footer" style="text-align: center">
                            <span >JOUISSANCE DE CONGÉS</span>
                        </div>
                </div>
            </div>

        </div>

        <div class="row">


            <div class="col-lg-3 col-md-6">
                <div class="panel" style="background-color: #4a148c">
                    <div class="panel-heading">
                        <div class="row" align="center">
                            <h1 style="font-size: 20pt; color: #ffffff"><?=number_format($nbjouissance1, 0, '.', ' ') ?></h1>
                        </div>
                    </div>
                    <div class="panel-footer" style="text-align: center">
                            <span >NON JOUISSANCE DE CONGÉS</span>
                        </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="panel" style="background-color: #0099ff">
                    <div class="panel-heading">
                        <div class="row" align="center">
                            <h1 style="font-size: 20pt; color: #ffffff"><?=number_format($nsuspension, 0, '.', ' ') ?></h1>
                        </div>
                    </div>
                  <div class="panel-footer" style="text-align: center">
                            <span >SUSPENSION DE CONTRATS</span>
                        </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="panel" style="background-color: #f0ad4e">
                    <div class="panel-heading">
                        <div class="row" align="center">
                            <h1 style="font-size: 20pt; color: #ffffff"><?=number_format($nbarret, 0, '.', ' ') ?></h1>
                        </div>
                    </div>
                  <div class="panel-footer" style="text-align: center">
                            <span >SUSPENSION DE CONGÉS</span>
                        </div>
                </div>
            </div>

        </div>



    </div>

</div>