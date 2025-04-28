<?php


namespace app\controllers;


use app\models\Ampliation;
use app\models\Decisionconges;
use app\models\Direction;
use app\models\Etablissement;
use app\models\Parametre;
use kartik\mpdf\Pdf;

class Generator
{

    public static function getDate($jour) {

       if($jour != null) {

           $tab = explode("/",$jour);

           return $tab[2]."-".$tab[1]."-".$tab[0];
       }

       else return null;

    }


    public static function trueDate($jour){

        $tab = explode(" ",$jour);

        $day = $tab[0]; $tab2 = explode("-",$day);

        $mois = array("01"=>"Janvier","02"=>"Février","03"=>"Mars","04"=>"Avril","05"=>"Mai","06"=>"Juin","07"=>"Juillet","08"=>"Août","09"=>"Septembre","10"=>"Octobre","11"=>"Novembre","12"=>"Décembre");

        $finalj = $tab2[2]." ".$mois[$tab2[1]]." ".$tab2[0];

        return $finalj;
    }

    public static function trueDate2($jour){

        $tab2 = explode("-",$jour);

        $mois = array("01"=>"Janvier","02"=>"Février","03"=>"Mars","04"=>"Avril","05"=>"Mai","06"=>"Juin","07"=>"Juillet","08"=>"Août","09"=>"Septembre","10"=>"Octobre","11"=>"Novembre","12"=>"Décembre");

        $finalj = $tab2[2]." ".$mois[$tab2[1]]." ".$tab2[0];

        return $finalj;
    }

    public static function trueDate3($jour){

        $tab2 = explode("-",$jour);

        $mois = array("01"=>"Janvier","02"=>"Fevrier","03"=>"Mars","04"=>"Avril","05"=>"Mai","06"=>"Juin","07"=>"Juillet","08"=>"Aout","09"=>"Septembre","10"=>"Octobre","11"=>"Novembre","12"=>"Decembre");

        $finalj = $tab2[2]."/".$tab2[1]."/".$tab2[0];

        return $finalj;
    }

    public static function trueDate4($jour){

        $tab = explode("-",$jour);

        return $tab[2]."/".$tab[1]."/".$tab[0];
    }

    public static function ageEmploye($datnaiss,$nextconge) {

        $setting = Parametre::findOne(1);

        $nextyear = date('Y-m-d', strtotime($datnaiss . ' +'.$setting->RETRAITE.' years'));

        $date1 = strtotime($nextconge); $date2 = strtotime($nextyear);

        $diff = $date2 - $date1;

        $diff = round($diff/(86400 * 365));

        return $diff;

    }

   /* public static function decision($id) {

        $model = Decisionconges::findOne($id);

        $employe = \app\models\Employe::findOne($model->MATICULE);

        $setting = \app\models\Parametre::findOne(1);

        $emploi = \app\models\Emploi::findOne($employe->CODEEMP);

        $departements = \app\models\Departements::findOne($employe->CODEDPT);

        $etablissement = \app\models\Etablissement::findOne($employe->CODEETS);

        $directions = \app\models\Direction::findOne($employe->DIRECTION);

        $services = \app\models\Service::findOne($employe->SERVICE);

        $suspension = \app\models\Suspension::find()->where(['MATICULE'=>$employe->MATRICULE,'DEJA'=>0,'STATUTLEVE'=>2])->one();

        $date1 = strtotime($model->DEBUTPLANIF); $date2 = strtotime($model->FINPLANIF);

        //$diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

        $article1 = $setting->ARTICLE1;

        if($suspension == null) $texte = $setting->TEXTE;

        else $texte = $setting->TEXTE3;

        $nom = $employe->NOM.' '.$employe->PRENOM;

        if($departements != null) $departement = $departements->LIBELLE; else $departement = '';

        if($directions != null) $direction = $directions->LIBELLE; else $direction = '';

        if($services != null) $service = $services->LIBELLE; else $service = '';

        if($etablissement != null) $lieu = $etablissement->LIBELLE; else $lieu = '';

        $civile = \app\models\Civilite::findOne($employe->CODECIV);

        $finservice = date('Y-m-d',strtotime($employe->DATECALCUL.' -1 day'));

        $debutservice = date('Y-m-d',strtotime($employe->LASTCONGE.' +1 day'));

        $reprise = date('Y-m-d',strtotime($model->FINPLANIF.' +1 day'));

        $vacance = $setting->DUREECONGES - $model->NBJOUR;

        $nextconge = date('Y-m-d',strtotime($reprise.' +'.($setting->DUREESERVICE).' day'));

        $nextconge = date('Y-m-d',strtotime($nextconge.' +'.($vacance).' day'));

        $next = self::ageEmploye($employe->DATNAISS,$nextconge);

        if($next < 0) $nextconge = null;

        $employe->LASTCONGE = $model->FINPLANIF; $employe->DATECALCUL = $nextconge;

        if($nextconge != null) $vnextconge = self::trueDate2($nextconge); else $vnextconge = "";

        $employe->save(false);

        $builder = ''; $today = date("Y-m-d");

        if($model->TYPE_DEC == 0) {

            if ($suspension == null) {

                $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
                $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
                $article1 = str_replace('{nom}', $nom, $article1);
                $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
                $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
                $article1 = str_replace('{departement}', $departement, $article1);
                $article1 = str_replace('{plateforme}', $lieu, $article1);
                $article1 = str_replace('{direction}', $direction, $article1);
                $article1 = str_replace('{service}', $service, $article1);

                $jour_date = date("d M Y", strtotime($today));

                $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="30px">&nbsp;</td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="130px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($setting->TEXTE) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPAL</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';

                $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';


                $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 2px; font-size: 10px" valign="middle">Le ' . self::trueDate2($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">du ' . self::trueDate2($debutservice) . ' au ' . self::trueDate2($finservice) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >' . $setting->DUREECONGES . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . ($setting->DUREECONGES - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >du ' . self::trueDate2($model->DEBUTPLANIF) . ' au ' . self::trueDate2($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . self::trueDate2($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $vnextconge . '</td>';

                if ($employe->DEPLACE == 1) $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" >ADC S.A.</td>';

                else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

                if ($employe->CODEETS_EMB == "DLA") {

                    $di = Direction::findOne($employe->DIRECTION);

                    if (($di != null) && strpos($di->LIBELLE, 'Exploitation') !== false) {
                        $ampliation = Ampliation::findOne(8);
                    } else {
                        $ampliation = Ampliation::findOne(3);
                    }

                } else if ($employe->CODEETS_EMB == "NSI") {

                    $di = Direction::findOne($employe->DIRECTION);

                    if (($di != null) && strpos($di->LIBELLE, 'International') !== false) {

                        $ampliation = Ampliation::findOne(7);
                    } else {
                        $ampliation = Ampliation::findOne(9);
                    }
                } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);


                $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

                $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>';

                if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                    $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px; padding: 10px;"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

                }

                if ($ampliation != null) $tamp = $ampliation->CONTENU; else $tamp = "";

                $builder .= '
<tr>
<td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $setting->SIGNATAIRE . '</b><br>
							<br>
</td></tr>

<tr><td height="50px" style="text-align: left; padding: 10px;">
<u><b>Ampliations</b></u><br>
<div style="margin-left: 30pxl; font-size: 10px; font-style: italic">' . nl2br($tamp) . '</div>
</td>
<td style="text-align: left"></td></tr>

';
            }

            else {

                $suspension->DEJA = 1; $suspension->save(false);

                $textes = $setting->TEXTE3;

                $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
                $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
                $article1 = str_replace('{nom}', $nom, $article1);
                $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
                $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
                $article1 = str_replace('{departement}', $departement, $article1);
                $article1 = str_replace('{plateforme}', $lieu, $article1);
                $article1 = str_replace('{direction}', $direction, $article1);
                $article1 = str_replace('{service}', $service, $article1);
                $textes = str_replace('{date_debut}', $suspension->DATEDEBUT, $textes);
                $textes = str_replace('{date_fin}', $suspension->DATEFIN, $textes);

                $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>
<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="20px"></td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($textes) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SUSPENSION</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" width="20%" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS EXECUTES</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPALE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';


                $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';

                $debutsuspension = date('Y-m-d', strtotime($suspension->DATEDEBUT . ' -1 day'));
                $finsuspension = date('Y-m-d', strtotime($suspension->DATEFIN . ' +1 day'));

                $d1 = strtotime($debutservice);
                $d2 = strtotime($debutsuspension);

                $diff1 = $d2 - $d1;
                $jour1 = abs(round($diff1 / 86400)) + 1;

                $d3 = strtotime($finsuspension);
                $d4 = strtotime($finservice);

                $diff2 = $d4 - $d3;
                $jour2 = abs(round($diff2 / 86400)) + 1;

                $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 1px; font-size: 10px" valign="middle">Le ' . self::trueDate3($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td><td valign="middle" height="30px" style="text-align: center; padding: 2px; font-size: 10px">du ' . self::trueDate3($suspension->DATEDEBUT) . ' au ' . self::trueDate3($suspension->DATEFIN) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 0px; font-size: 10px"><table border="1" width="100%" height="auto"  style=" font-size: 10px; border-right: 1px solid #000000;" cellpadding="0" cellspacing="0"><tr><td style="" align="center">du<br>' . self::trueDate3($debutservice) . '<br>du<br>' . self::trueDate3($finsuspension) . '</td><td align="center">au<br>' . self::trueDate3($debutsuspension) . '<br>au<br>' . self::trueDate3($finservice) . '</td></tr></table></td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >' . $jour1 . ' Jours<br><br>' . $jour2 . ' Jours</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >' . $setting->DUREECONGES . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . ($setting->DUREECONGES - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >du ' . self::trueDate3($model->DEBUTPLANIF) . ' au ' . self::trueDate3($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . self::trueDate3($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . $vnextconge . '</td>';

                if ($employe->DEPLACE == 1) $builder .= '<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >ADC S.A.</td>'; else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

                if ($employe->CODEETS_EMB == "DLA") {

                    if (strpos($employe->DIRECTION, 'Exploitation') !== false) {

                        $ampliation = Ampliation::findOne(8);
                    } else {
                        $ampliation = Ampliation::findOne(3);
                    }

                } else if ($employe->CODEETS_EMB == "NSI") {

                    if (strpos($employe->DIRECTION, 'International') !== false) {

                        $ampliation = Ampliation::findOne(7);
                    } else {
                        $ampliation = Ampliation::findOne(9);
                    }
                } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);

                $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

                $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>

';

                if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                    $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px;  padding: 10px"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

                }

                if ($ampliation != null) $tamp = $ampliation->CONTENU; else $tamp = "";

                $builder .= '
<tr>
<td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $setting->SIGNATAIRE . '</b><br>
							<br>
</td></tr>

<tr><td height="50px" style="text-align: left; padding: 10px;">
<u><b>Ampliations</b></u><br>
<div style="margin-left: 30pxl; font-size: 10px; font-style: italic">' . nl2br($tamp) . '</div>
</td>
<td style="text-align: left"></td></tr>

';

            }

        }

        else {

            $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
            $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
            $article1 = str_replace('{nom}', $nom, $article1);
            $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
            $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
            $article1 = str_replace('{departement}', $departement, $article1);
            $article1 = str_replace('{plateforme}', $lieu, $article1);
            $article1 = str_replace('{direction}', $direction, $article1);
            $article1 = str_replace('{service}', $service, $article1);

            $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>

<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="20px"></td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="130px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($setting->TEXTE) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPAL</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';

            $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';


            $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 2px; font-size: 10px" valign="middle">Le ' . self::trueDate2($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">du ' . self::trueDate2($debutservice) . ' au ' . self::trueDate2($finservice) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >' . $setting->DUREECONGES . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . ($setting->DUREECONGES - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >du ' . self::trueDate2($model->DEBUTPLANIF) . ' au ' . self::trueDate2($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . self::trueDate2($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $vnextconge . '</td>';

            if ($employe->DEPLACE == 1) $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" >ADC S.A.</td>';

            else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

            if ($employe->CODEETS_EMB == "DLA") {

                if (strpos($employe->DIRECTION, 'Exploitation') !== false) {

                    $ampliation = Ampliation::findOne(8);
                } else {
                    $ampliation = Ampliation::findOne(3);
                }

            } else if ($employe->CODEETS_EMB == "NSI") {

                if (strpos($employe->DIRECTION, 'International') !== false) {

                    $ampliation = Ampliation::findOne(7);
                } else {
                    $ampliation = Ampliation::findOne(9);
                }
            } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);


            $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

            $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>';

            if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px; padding: 10px;"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

            }

            $builder .= '
<tr>
<td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $setting->SIGNATAIRE . '</b><br>	<br>
</td></tr>

<tr><td height="50px" style="text-align: left; padding: 10px;">
<u><b>Ampliations</b></u><br>
<div style="margin-left: 30pxl; font-size: 10px; font-style: italic">' . nl2br($ampliation->CONTENU) . '</div>
</td>
<td style="text-align: left"></td></tr>

';
        }

        $builder.='<tr><td colspan="2" height="30" valign="bottom" align="center"></td></tr></table></body></html>';

        $nom = $employe->NOM." ".$employe->PRENOM;
        $fichier_nom = str_replace("é","e",$nom);
        $fichier_nom = str_replace("'","",$fichier_nom);
        $fichier_nom = str_replace(" ","-",$fichier_nom);
        $filename = 'Decision-'.$nom.'-'.$model->ANNEEEXIGIBLE.'-'.time().'.pdf';
        $repertoire = '../web/uploads';

        $pdf = new Pdf([
            'mode' => '', // leaner size using standard fonts
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'marginTop' => 10,
            'marginBottom' => 0,
            'marginLeft' => 10,
            'marginRight' => 10,
            //'destination' => Pdf::DEST_BROWSER,
            //'content' => $this->renderPartial('certificate_pdf', ['']),
            'options' => [
                // any mpdf options you wish to set
            ],
            'methods' => [
                'SetTitle' => 'Decision de congés',
                'SetSubject' => '',
                'SetAuthor' => 'ADC',
                'SetCreator' => 'ADC',
                'SetKeywords' => 'ADC',
                'SetHeader'=>['<div style="text-align: left;  height: 100px"><img src="../web/img/logo.png" width="300px" height="auto" /></div>'],
                'SetFooter'=>['<div style="text-align: center; color:#000000; font-size: 12px"><br>Siège Social : Aéroport International de Yaoundé – Nsimalen – BP 13615 Yaoundé<br>Tél. : (237) 222 23 36 02 / 222 23 45 21 – Fax (237) 222 23 45 20</div>'],
            ],

        ]);

        $mpdf = $pdf->api;
      //  $mpdf = new \Mpdf\Mpdf();

        $mpdf->SetHTMLHeader('<div style="text-align: left;  height: 100px"><img src="../web/img/logo.png" width="300px" height="auto" /></div>',true);

        $mpdf->SetHTMLFooter('<div style="text-align: center; color:#000000; font-size: 12px"><br>Siège Social : Aéroport International de Yaoundé – Nsimalen – BP 13615 Yaoundé<br>Tél. : (237) 222 23 36 02 / 222 23 45 21 – Fax (237) 222 23 45 20</div>',true);

        $mpdf->WriteHtml($builder);
        $path = $mpdf->Output('', 'S');

        $model->EDITION = $filename;
        $model->DATEREPRISE = $reprise;
        $model->DEBUTREELL = $debutservice;
        $model->FINREEL = $finservice;
        $model->DATECLOTURE = $nextconge;
        $model->FICHIER = 1;
        $model->save(false);
        $employe->DATECALCUL = $nextconge;
        $employe->save(false);

        echo $mpdf->Output($repertoire."/".$filename, 'F');

        return $filename;

    }

    public static function decision1($id) {

        $model = Decisionconges::findOne($id);

        $employe = \app\models\Employe::findOne($model->MATICULE);

        $setting = \app\models\Parametre::findOne(1);

        $emploi = \app\models\Emploi::findOne($employe->CODEEMP);

        $departements = \app\models\Departements::findOne($employe->CODEDPT);

        $etablissement = \app\models\Etablissement::findOne($employe->CODEETS);

        $directions = \app\models\Direction::findOne($employe->DIRECTION);

        $services = \app\models\Service::findOne($employe->SERVICE);

        $suspension = \app\models\Suspension::find()->where(['MATICULE'=>$employe->MATRICULE,'DEJA'=>0,'STATUTLEVE'=>2])->one();

        $date1 = strtotime($model->DEBUTPLANIF); $date2 = strtotime($model->FINPLANIF);

        //$diff = $date2 - $date1; $nbjour = abs(round($diff/86400)) + 1;

        $article1 = $setting->ARTICLE1;

        if($suspension == null) $texte = $setting->TEXTE;

        else $texte = $setting->TEXTE3;

        $nom = $employe->NOM.' '.$employe->PRENOM;

        if($departements != null) $departement = $departements->LIBELLE; else $departement = '';

        if($directions != null) $direction = $directions->LIBELLE; else $direction = '';

        if($services != null) $service = $services->LIBELLE; else $service = '';

        if($etablissement != null) $lieu = $etablissement->LIBELLE; else $lieu = '';

        $civile = \app\models\Civilite::findOne($employe->CODECIV);

        $finservice = date('Y-m-d',strtotime($model->FINREEL));

        $debutservice = date('Y-m-d',strtotime($model->DEBUTREELL));

        $reprise = date('Y-m-d',strtotime($model->FINPLANIF.' +1 day'));

        $vacance = $setting->DUREECONGES - $model->NBJOUR;

        $nextconge = date('Y-m-d',strtotime($reprise.' +'.($setting->DUREESERVICE).' day'));

        $nextconge = date('Y-m-d',strtotime($nextconge.' +'.($vacance).' day'));

        $next = self::ageEmploye($employe->DATNAISS,$nextconge);

        if($next < 0) $nextconge = null;

        $employe->LASTCONGE = $model->FINPLANIF; $employe->DATECALCUL = $nextconge;

        if($nextconge != null) $vnextconge = self::trueDate2($nextconge); else $vnextconge = "";

        $employe->save(false);

        $builder = ''; $today = date("Y-m-d");

        if($model->TYPE_DEC == 0) {

            if ($suspension == null) {

                $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
                $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
                $article1 = str_replace('{nom}', $nom, $article1);
                $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
                $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
                $article1 = str_replace('{departement}', $departement, $article1);
                $article1 = str_replace('{plateforme}', $lieu, $article1);
                $article1 = str_replace('{direction}', $direction, $article1);
                $article1 = str_replace('{service}', $service, $article1);

                $jour_date = date("d M Y", strtotime($today));

                $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="30px">&nbsp;</td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="130px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($setting->TEXTE) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPAL</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';

                $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';


                $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 2px; font-size: 10px" valign="middle">Le ' . self::trueDate2($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">du ' . self::trueDate2($debutservice) . ' au ' . self::trueDate2($finservice) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >' . $setting->DUREECONGES . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . ($setting->DUREECONGES - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >du ' . self::trueDate2($model->DEBUTPLANIF) . ' au ' . self::trueDate2($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . self::trueDate2($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $vnextconge . '</td>';

                if ($employe->DEPLACE == 1) $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" >ADC S.A.</td>';

                else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

                if ($employe->CODEETS_EMB == "DLA") {

                    $di = Direction::findOne($employe->DIRECTION);

                    if (($di != null) && strpos($di->LIBELLE, 'Exploitation') !== false) {
                        $ampliation = Ampliation::findOne(8);
                    } else {
                        $ampliation = Ampliation::findOne(3);
                    }

                } else if ($employe->CODEETS_EMB == "NSI") {

                    $di = Direction::findOne($employe->DIRECTION);

                    if (($di != null) && strpos($di->LIBELLE, 'International') !== false) {

                        $ampliation = Ampliation::findOne(7);
                    } else {
                        $ampliation = Ampliation::findOne(9);
                    }
                } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);


                $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

                $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>';

                if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                    $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px; padding: 10px;"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

                }

                if ($ampliation != null) $tamp = $ampliation->CONTENU; else $tamp = "";

                $builder .= '
<tr>
<td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $setting->SIGNATAIRE . '</b><br>
							<br>
</td></tr>

<tr><td height="50px" style="text-align: left; padding: 10px;">
<u><b>Ampliations</b></u><br>
<div style="margin-left: 30pxl; font-size: 10px; font-style: italic">' . nl2br($tamp) . '</div>
</td>
<td style="text-align: left"></td></tr>

';
            }

            else {

                $suspension->DEJA = 1; $suspension->save(false);

                $textes = $setting->TEXTE3;

                $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
                $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
                $article1 = str_replace('{nom}', $nom, $article1);
                $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
                $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
                $article1 = str_replace('{departement}', $departement, $article1);
                $article1 = str_replace('{plateforme}', $lieu, $article1);
                $article1 = str_replace('{direction}', $direction, $article1);
                $article1 = str_replace('{service}', $service, $article1);
                $textes = str_replace('{date_debut}', $suspension->DATEDEBUT, $textes);
                $textes = str_replace('{date_fin}', $suspension->DATEFIN, $textes);

                $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>
<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="20px"></td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($textes) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SUSPENSION</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" width="20%" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS EXECUTES</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPALE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';


                $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';

                $debutsuspension = date('Y-m-d', strtotime($suspension->DATEDEBUT . ' -1 day'));
                $finsuspension = date('Y-m-d', strtotime($suspension->DATEFIN . ' +1 day'));

                $d1 = strtotime($debutservice);
                $d2 = strtotime($debutsuspension);

                $diff1 = $d2 - $d1;
                $jour1 = abs(round($diff1 / 86400)) + 1;

                $d3 = strtotime($finsuspension);
                $d4 = strtotime($finservice);

                $diff2 = $d4 - $d3;
                $jour2 = abs(round($diff2 / 86400)) + 1;

                $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 1px; font-size: 10px" valign="middle">Le ' . self::trueDate3($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td><td valign="middle" height="30px" style="text-align: center; padding: 2px; font-size: 10px">du ' . self::trueDate3($suspension->DATEDEBUT) . ' au ' . self::trueDate3($suspension->DATEFIN) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 0px; font-size: 10px"><table border="1" width="100%" height="auto"  style=" font-size: 10px; border-right: 1px solid #000000;" cellpadding="0" cellspacing="0"><tr><td style="" align="center">du<br>' . self::trueDate3($debutservice) . '<br>du<br>' . self::trueDate3($finsuspension) . '</td><td align="center">au<br>' . self::trueDate3($debutsuspension) . '<br>au<br>' . self::trueDate3($finservice) . '</td></tr></table></td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >' . $jour1 . ' Jours<br><br>' . $jour2 . ' Jours</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >' . $setting->DUREECONGES . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . ($setting->DUREECONGES - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >du ' . self::trueDate3($model->DEBUTPLANIF) . ' au ' . self::trueDate3($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . self::trueDate3($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . $vnextconge . '</td>';

                if ($employe->DEPLACE == 1) $builder .= '<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >ADC S.A.</td>'; else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

                if ($employe->CODEETS_EMB == "DLA") {

                    if (strpos($employe->DIRECTION, 'Exploitation') !== false) {

                        $ampliation = Ampliation::findOne(8);
                    } else {
                        $ampliation = Ampliation::findOne(3);
                    }

                } else if ($employe->CODEETS_EMB == "NSI") {

                    if (strpos($employe->DIRECTION, 'International') !== false) {

                        $ampliation = Ampliation::findOne(7);
                    } else {
                        $ampliation = Ampliation::findOne(9);
                    }
                } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);

                $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

                $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>

';

                if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                    $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px;  padding: 10px"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

                }

                if ($ampliation != null) $tamp = $ampliation->CONTENU; else $tamp = "";

                $builder .= '
<tr>
<td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $setting->SIGNATAIRE . '</b><br>
							<br>
</td></tr>

<tr><td height="50px" style="text-align: left; padding: 10px;">
<u><b>Ampliations</b></u><br>
<div style="margin-left: 30pxl; font-size: 10px; font-style: italic">' . nl2br($tamp) . '</div>
</td>
<td style="text-align: left"></td></tr>

';

            }

        }

        else {

            $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
            $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
            $article1 = str_replace('{nom}', $nom, $article1);
            $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
            $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
            $article1 = str_replace('{departement}', $departement, $article1);
            $article1 = str_replace('{plateforme}', $lieu, $article1);
            $article1 = str_replace('{direction}', $direction, $article1);
            $article1 = str_replace('{service}', $service, $article1);

            $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>

<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="20px"></td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="130px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($setting->TEXTE) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPAL</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';

            $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';


            $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 2px; font-size: 10px" valign="middle">Le ' . self::trueDate2($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">du ' . self::trueDate2($debutservice) . ' au ' . self::trueDate2($finservice) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >' . $setting->DUREECONGES . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . ($setting->DUREECONGES - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >du ' . self::trueDate2($model->DEBUTPLANIF) . ' au ' . self::trueDate2($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . self::trueDate2($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $vnextconge . '</td>';

            if ($employe->DEPLACE == 1) $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" >ADC S.A.</td>';

            else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

            if ($employe->CODEETS_EMB == "DLA") {

                if (strpos($employe->DIRECTION, 'Exploitation') !== false) {

                    $ampliation = Ampliation::findOne(8);
                } else {
                    $ampliation = Ampliation::findOne(3);
                }

            } else if ($employe->CODEETS_EMB == "NSI") {

                if (strpos($employe->DIRECTION, 'International') !== false) {

                    $ampliation = Ampliation::findOne(7);
                } else {
                    $ampliation = Ampliation::findOne(9);
                }
            } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);


            $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

            $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>';

            if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px; padding: 10px;"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

            }

            $builder .= '
<tr>
<td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $setting->SIGNATAIRE . '</b><br>	<br>
</td></tr>

<tr><td height="50px" style="text-align: left; padding: 10px;">
<u><b>Ampliations</b></u><br>
<div style="margin-left: 30pxl; font-size: 10px; font-style: italic">' . nl2br($ampliation->CONTENU) . '</div>
</td>
<td style="text-align: left"></td></tr>

';
        }

        $builder.='<tr><td colspan="2" height="30" valign="bottom" align="center"></td></tr></table></body></html>';

        $nom = $employe->NOM." ".$employe->PRENOM;
        $fichier_nom = str_replace("é","e",$nom);
        $fichier_nom = str_replace("'","",$fichier_nom);
        $fichier_nom = str_replace(" ","-",$fichier_nom);
        $filename = 'Decision-'.$nom.'-'.$model->ANNEEEXIGIBLE.'-'.time().'.pdf';
        $repertoire = '../web/uploads';

        $pdf = new Pdf([
            'mode' => '', // leaner size using standard fonts
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'marginTop' => 10,
            'marginBottom' => 0,
            'marginLeft' => 10,
            'marginRight' => 10,
            //'destination' => Pdf::DEST_BROWSER,
            //'content' => $this->renderPartial('certificate_pdf', ['']),
            'options' => [
                // any mpdf options you wish to set
            ],
            'methods' => [
                'SetTitle' => 'Decision de congés',
                'SetSubject' => '',
                'SetAuthor' => 'ADC',
                'SetCreator' => 'ADC',
                'SetKeywords' => 'ADC',
                'SetHeader'=>['<div style="text-align: left;  height: 100px"><img src="../web/img/logo.png" width="300px" height="auto" /></div>'],
                'SetFooter'=>['<div style="text-align: center; color:#000000; font-size: 12px"><br>Siège Social : Aéroport International de Yaoundé – Nsimalen – BP 13615 Yaoundé<br>Tél. : (237) 222 23 36 02 / 222 23 45 21 – Fax (237) 222 23 45 20</div>'],
            ],

        ]);

        $mpdf = $pdf->api;
        //  $mpdf = new \Mpdf\Mpdf();

        $mpdf->SetHTMLHeader('<div style="text-align: left;  height: 100px"><img src="../web/img/logo.png" width="300px" height="auto" /></div>',true);

        $mpdf->SetHTMLFooter('<div style="text-align: center; color:#000000; font-size: 12px"><br>Siège Social : Aéroport International de Yaoundé – Nsimalen – BP 13615 Yaoundé<br>Tél. : (237) 222 23 36 02 / 222 23 45 21 – Fax (237) 222 23 45 20</div>',true);

        $mpdf->WriteHtml($builder);
        $path = $mpdf->Output('', 'S');

        $model->EDITION = $filename;
        //$model->DATEREPRISE = $reprise;
        //$model->DEBUTREELL = $debutservice;
        //$model->FINREEL = $finservice;
        //$model->DATECLOTURE = $nextconge;
        $model->FICHIER = 1;
        $model->save(false);
       // $employe->DATECALCUL = $nextconge;
       // $employe->save(false);

        echo $mpdf->Output($repertoire."/".$filename, 'F');

        return $filename;

    } */

    public static function decision2($id) {

        $model = Decisionconges::findOne($id);

        $employe = \app\models\Employe::findOne($model->MATICULE);

        $setting = \app\models\Parametre::findOne(1);

        $emploi = \app\models\Emploi::findOne($employe->CODEEMP);

        $departements = \app\models\Departements::findOne($employe->CODEDPT);

        $etablissement = \app\models\Etablissement::findOne($employe->CODEETS);

        $directions = \app\models\Direction::findOne($employe->DIRECTION);

        $services = \app\models\Service::findOne($employe->SERVICE);

        $suspension = \app\models\Suspension::find()->where(['MATICULE'=>$employe->MATRICULE,'DEJA'=>0,'STATUTLEVE'=>2])->one();

        $article1 = $setting->ARTICLE1;

        if($suspension == null) $texte = $setting->TEXTE;

        else $texte = $setting->TEXTE3;

        if($employe->CODEEMP == 273 || $employe->CODEEMP == 462) $durer = $setting->DUREECONGES2;
        else $durer = $setting->DUREECONGES;


        $nom = $employe->NOM.' '.$employe->PRENOM;

        if($departements != null) $departement = $departements->LIBELLE; else $departement = '';

        if($directions != null) $direction = $directions->LIBELLE; else $direction = '';

        if($services != null) $service = $services->LIBELLE; else $service = '';

        if($etablissement != null) $lieu = $etablissement->LIBELLE; else $lieu = '';

        $civile = \app\models\Civilite::findOne($employe->CODECIV);

        $finservice = date('Y-m-d',strtotime($employe->DATECALCUL.' -1 day'));

        $debutservice = date('Y-m-d',strtotime($employe->LASTCONGE.' +1 day'));

        $reprise = date('Y-m-d',strtotime($model->FINPLANIF.' +1 day'));

        $vacance = $durer - $model->NBJOUR;

        $nextconge = date('Y-m-d',strtotime($reprise.' +'.($setting->DUREESERVICE).' day'));

        $nextconge = date('Y-m-d',strtotime($nextconge.' +'.($vacance).' day'));

        $next = self::ageEmploye($employe->DATNAISS,$nextconge);

        if($next < 0) $nextconge = null;

        $employe->LASTCONGE = $model->FINPLANIF; $employe->DATECALCUL = $nextconge;

        if($nextconge != null) $vnextconge = self::trueDate2($nextconge); else $vnextconge = "";

        $date1 = new \DateTime($model->DEBUTPLANIF); $date2 = new \DateTime($finservice);

        if($date1 > $date2) {

            $employe->save(false);

            $builder = ''; $today = date("Y-m-d");

            if($model->TYPE_DEC == 0) {

                if ($suspension == null) {

                    $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
                    $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
                    $article1 = str_replace('{nom}', $nom, $article1);
                    $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
                    $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
                    $article1 = str_replace('{departement}', $departement, $article1);
                    $article1 = str_replace('{plateforme}', $lieu, $article1);
                    $article1 = str_replace('{direction}', $direction, $article1);
                    $article1 = str_replace('{service}', $service, $article1);

                    $jour_date = date("d M Y", strtotime($today));

                    if($employe->CODEEMP == 273 || $employe->CODEEMP == 462) $textes = $setting->TEXTE4;
                    else $textes = $setting->TEXTE;

                    $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="30px">&nbsp;</td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="130px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($textes) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPAL</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';

                    $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';


                    $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 2px; font-size: 10px" valign="middle">Le ' . self::trueDate2($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">du ' . self::trueDate2($debutservice) . ' au ' . self::trueDate2($finservice) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >' . $durer . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . ($durer - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >du ' . self::trueDate2($model->DEBUTPLANIF) . ' au ' . self::trueDate2($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . self::trueDate2($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $vnextconge . '</td>';

                    if ($employe->DEPLACE == 1) $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" >ADC S.A.</td>';

                    else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

                    if ($employe->CODEETS_EMB == "DLA") {

                        $di = Direction::findOne($employe->DIRECTION);

                        if (($di != null) && strpos($di->LIBELLE, 'Exploitation') !== false) {
                            $ampliation = Ampliation::findOne(8);
                        } else {
                            $ampliation = Ampliation::findOne(3);
                        }

                    } else if ($employe->CODEETS_EMB == "NSI") {

                        $di = Direction::findOne($employe->DIRECTION);

                        if (($di != null) && strpos($di->LIBELLE, 'International') !== false) {

                            $ampliation = Ampliation::findOne(7);
                        } else {
                            $ampliation = Ampliation::findOne(9);
                        }
                    } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);


                    $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

                    $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>';

                    if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                        $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px; padding: 10px;"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

                    }

                    if($employe->AMPLIATION != null) $tamp = $employe->AMPLIATION;
                    else if ($ampliation != null) $tamp = $ampliation->CONTENU;
                    else $tamp = "";

               if($employe->CODEEMP == 273) $signature = $setting->SIGNATAIRE1;
                              else if($employe->CODEEMP == 462) $signature = $setting->SIGNATAIRE2;
                              else $signature = $setting->SIGNATAIRE;


                    $builder .= '
<tr>
<td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $signature. '</b><br>
							<br>
</td></tr>

<tr><td height="50px" style="text-align: left; padding: 10px;">
<u><b>Ampliations</b></u><br>
<div style="margin-left: 30px; font-size: 10px; font-style: italic">' . nl2br($tamp) . '</div>
</td>
<td style="text-align: left"></td></tr>

';
                }

                else {

                    $suspension->DEJA = 1; $suspension->save(false);

                    $textes = $setting->TEXTE3;

                    $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
                    $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
                    $article1 = str_replace('{nom}', $nom, $article1);
                    $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
                    $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
                    $article1 = str_replace('{departement}', $departement, $article1);
                    $article1 = str_replace('{plateforme}', $lieu, $article1);
                    $article1 = str_replace('{direction}', $direction, $article1);
                    $article1 = str_replace('{service}', $service, $article1);
                    $textes = str_replace('{date_debut}', $suspension->DATEDEBUT, $textes);
                    $textes = str_replace('{date_fin}', $suspension->DATEFIN, $textes);

                    $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>
<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="20px"></td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($textes) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SUSPENSION</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" width="20%" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS EXECUTES</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPALE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';


                    $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';

                    $debutsuspension = date('Y-m-d', strtotime($suspension->DATEDEBUT . ' -1 day'));
                    $finsuspension = date('Y-m-d', strtotime($suspension->DATEFIN . ' +1 day'));

                    $d1 = strtotime($debutservice);
                    $d2 = strtotime($debutsuspension);

                    $diff1 = $d2 - $d1;
                    $jour1 = abs(round($diff1 / 86400)) + 1;

                    $d3 = strtotime($finsuspension);
                    $d4 = strtotime($finservice);

                    $diff2 = $d4 - $d3;
                    $jour2 = abs(round($diff2 / 86400)) + 1;

                    $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 1px; font-size: 10px" valign="middle">Le ' . self::trueDate3($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td><td valign="middle" height="30px" style="text-align: center; padding: 2px; font-size: 10px">du ' . self::trueDate3($suspension->DATEDEBUT) . ' au ' . self::trueDate3($suspension->DATEFIN) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 0px; font-size: 10px"><table border="1" width="100%" height="auto"  style=" font-size: 10px; border-right: 1px solid #000000;" cellpadding="0" cellspacing="0"><tr><td style="" align="center">du<br>' . self::trueDate3($debutservice) . '<br>du<br>' . self::trueDate3($finsuspension) . '</td><td align="center">au<br>' . self::trueDate3($debutsuspension) . '<br>au<br>' . self::trueDate3($finservice) . '</td></tr></table></td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >' . $jour1 . ' Jours<br><br>' . $jour2 . ' Jours</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >' . $durer . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . ($durer - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >du ' . self::trueDate3($model->DEBUTPLANIF) . ' au ' . self::trueDate3($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . self::trueDate3($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . $vnextconge . '</td>';

                    if ($employe->DEPLACE == 1) $builder .= '<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >ADC S.A.</td>'; else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

                    if ($employe->CODEETS_EMB == "DLA") {

                        if (strpos($employe->DIRECTION, 'Exploitation') !== false) {

                            $ampliation = Ampliation::findOne(8);
                        } else {
                            $ampliation = Ampliation::findOne(3);
                        }

                    } else if ($employe->CODEETS_EMB == "NSI") {

                        if (strpos($employe->DIRECTION, 'International') !== false) {

                            $ampliation = Ampliation::findOne(7);
                        } else {
                            $ampliation = Ampliation::findOne(9);
                        }
                    } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);

                    $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

                    $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>

';

                    if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                        $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px;  padding: 10px"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

                    }

                    //if ($ampliation != null) $tamp = $ampliation->CONTENU; else $tamp = "";
                    if($employe->AMPLIATION != null) $tamp = $employe->AMPLIATION;
                    else if ($ampliation != null) $tamp = $ampliation->CONTENU;
                    else $tamp = "";


 if($employe->CODEEMP == 273) $signature = $setting->SIGNATAIRE1;
                else if($employe->CODEEMP == 462) $signature = $setting->SIGNATAIRE2;
                else $signature = $setting->SIGNATAIRE;

                    $builder .= '
<tr>
<td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $signature . '</b><br>
							<br>
</td></tr>

<tr><td height="50px" style="text-align: left; padding: 10px;">
<u><b>Ampliations</b></u><br>
<div style="margin-left: 30px; font-size: 10px; font-style: italic">' . nl2br($tamp) . '</div>
</td>
<td style="text-align: left"></td></tr>

';

                }

            }

            else {

                $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
                $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
                $article1 = str_replace('{nom}', $nom, $article1);
                $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
                $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
                $article1 = str_replace('{departement}', $departement, $article1);
                $article1 = str_replace('{plateforme}', $lieu, $article1);
                $article1 = str_replace('{direction}', $direction, $article1);
                $article1 = str_replace('{service}', $service, $article1);

                if($employe->CODEEMP == 273 || $employe->CODEEMP == 462) $textes = $setting->TEXTE4;
                else $textes = $setting->TEXTE;

                $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>

<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="20px"></td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="130px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($textes) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPAL</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';

                $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';


                $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 2px; font-size: 10px" valign="middle">Le ' . self::trueDate2($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">du ' . self::trueDate2($debutservice) . ' au ' . self::trueDate2($finservice) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >' . $durer . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . ($durer - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >du ' . self::trueDate2($model->DEBUTPLANIF) . ' au ' . self::trueDate2($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . self::trueDate2($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $vnextconge . '</td>';

                if ($employe->DEPLACE == 1) $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" >ADC S.A.</td>';

                else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

                if ($employe->CODEETS_EMB == "DLA") {

                    if (strpos($employe->DIRECTION, 'Exploitation') !== false) {

                        $ampliation = Ampliation::findOne(8);
                    } else {
                        $ampliation = Ampliation::findOne(3);
                    }

                } else if ($employe->CODEETS_EMB == "NSI") {

                    if (strpos($employe->DIRECTION, 'International') !== false) {

                        $ampliation = Ampliation::findOne(7);
                    } else {
                        $ampliation = Ampliation::findOne(9);
                    }
                } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);


                $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

                $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>';

                if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                    $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px; padding: 10px;"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

                }

                if($employe->CODEEMP == 273) $signature = $setting->SIGNATAIRE1;
                else if($employe->CODEEMP == 462) $signature = $setting->SIGNATAIRE2;
                else $signature = $setting->SIGNATAIRE;

                $builder .= '
                    <tr>
                    <td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $signature . '</b><br>	<br>
                    </td></tr>

                    <tr><td height="50px" style="text-align: left; padding: 10px;">
                    <u><b>Ampliations</b></u><br>
                    <div style="margin-left: 30px; font-size: 10px; font-style: italic">' . nl2br($ampliation->CONTENU) . '</div>
                    </td>
                    <td style="text-align: left"></td></tr>

                    ';
            }

            $builder.='<tr><td colspan="2" height="30" valign="bottom" align="center"></td></tr></table></body></html>';

            $nom = $employe->NOM." ".$employe->PRENOM;
            $fichier_nom = str_replace("é","e",$nom);
            $fichier_nom = str_replace("'","",$fichier_nom);
            $fichier_nom = str_replace(" ","-",$fichier_nom);
            $filename = 'Decision-'.$nom.'-'.$model->ANNEEEXIGIBLE.'-'.time().'.pdf';
            $repertoire = './web/uploads';

            $pdf = new Pdf([
                'mode' => '', // leaner size using standard fonts
                'format' => Pdf::FORMAT_A4,
                'orientation' => Pdf::ORIENT_PORTRAIT,
                'marginTop' => 10,
                'marginBottom' => 0,
                'marginLeft' => 10,
                'marginRight' => 10,
                //'destination' => Pdf::DEST_BROWSER,
                //'content' => $this->renderPartial('certificate_pdf', ['']),
                'options' => [
                    // any mpdf options you wish to set
                ],
                'methods' => [
                    'SetTitle' => 'Decision de congés',
                    'SetSubject' => '',
                    'SetAuthor' => 'ADC',
                    'SetCreator' => 'ADC',
                    'SetKeywords' => 'ADC',
                    'SetHeader'=>['<div style="text-align: left;  height: 100px"><img src="./web/img/logo.png" width="300px" height="auto" /></div>'],
                    'SetFooter'=>['<div style="text-align: center; color:#000000; font-size: 12px"><br>Siège Social : Aéroport International de Yaoundé – Nsimalen – BP 13615 Yaoundé<br>Tél. : (237) 222 23 36 02 / 222 23 45 21 – Fax (237) 222 23 45 20</div>'],
                ],

            ]);

            $mpdf = $pdf->api;
            //  $mpdf = new \Mpdf\Mpdf();

            $mpdf->SetHTMLHeader('<div style="text-align: left;  height: 100px"><img src="./web/img/logo.png" width="300px" height="auto" /></div>',true);

            $mpdf->SetHTMLFooter('<div style="text-align: center; color:#000000; font-size: 12px"><br>Siège Social : Aéroport International de Yaoundé – Nsimalen – BP 13615 Yaoundé<br>Tél. : (237) 222 23 36 02 / 222 23 45 21 – Fax (237) 222 23 45 20</div>',true);

            $mpdf->WriteHtml($builder);
            $path = $mpdf->Output('', 'S');

            $model->EDITION = $filename;
            $model->DATEREPRISE = $reprise;
            $model->DEBUTREELL = $debutservice;
            $model->FINREEL = $finservice;
            $model->DATECLOTURE = $nextconge;
            $model->FICHIER = 1;
            $model->save(false);
            $employe->DATECALCUL = $nextconge;
            $employe->save(false);

            echo $mpdf->Output($repertoire."/".$filename, 'F');

            return $filename;
            //return "ok";
        }

        else return "";

    }

    public static function decision($id) {

        $model = Decisionconges::findOne($id);

        $employe = \app\models\Employe::findOne($model->MATICULE);

        $setting = \app\models\Parametre::findOne(1);

        $emploi = \app\models\Emploi::findOne($employe->CODEEMP);

        $departements = \app\models\Departements::findOne($employe->CODEDPT);

        $etablissement = \app\models\Etablissement::findOne($employe->CODEETS);

        $directions = \app\models\Direction::findOne($employe->DIRECTION);

        $services = \app\models\Service::findOne($employe->SERVICE);

        $suspension = \app\models\Suspension::find()->where(['MATICULE'=>$employe->MATRICULE,'DEJA'=>0,'STATUTLEVE'=>2])->one();

        $article1 = $setting->ARTICLE1;

        if($suspension == null) $texte = $setting->TEXTE;

        else $texte = $setting->TEXTE3;

        if($employe->CODEEMP == 273 || $employe->CODEEMP == 462) $durer = $setting->DUREECONGES2;
        else $durer = $setting->DUREECONGES;


        $nom = $employe->NOM.' '.$employe->PRENOM;

        if($departements != null) $departement = $departements->LIBELLE; else $departement = '';

        if($directions != null) $direction = $directions->LIBELLE; else $direction = '';

        if($services != null) $service = $services->LIBELLE; else $service = '';

        if($etablissement != null) $lieu = $etablissement->LIBELLE; else $lieu = '';

        $civile = \app\models\Civilite::findOne($employe->CODECIV);

        $finservice = $model->FINPLANIF;

        $debutservice = $model->DEBUTPLANIF;

        $reprise = date('Y-m-d',strtotime($model->FINREEL.' +1 day'));

        $vacance = $durer - $model->NBJOUR;

        $nextconge = date('Y-m-d',strtotime($reprise.' +'.($setting->DUREESERVICE).' day'));

        //$nextconge = date('Y-m-d',strtotime($nextconge.' +'.($vacance).' day'));

        $next = self::ageEmploye($employe->DATNAISS,$nextconge);

        if($next < 0) $nextconge = null;

        $employe->LASTCONGE = $model->DEBUTREELL; $employe->DATECALCUL = $nextconge;

        if($nextconge != null) $vnextconge = self::trueDate2($nextconge); else $vnextconge = "";

        $date1 = new \DateTime($model->DEBUTPLANIF); $date2 = new \DateTime($finservice);

       // if($date1 > $date2) {

            $employe->save(false);

            $builder = ''; $today = date("Y-m-d");

            if($model->TYPE_DEC == 0) {

                if ($suspension == null) {

                    $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
                    $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
                    $article1 = str_replace('{nom}', $nom, $article1);
                    $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
                    $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
                    $article1 = str_replace('{departement}', $departement, $article1);
                    $article1 = str_replace('{plateforme}', $lieu, $article1);
                    $article1 = str_replace('{direction}', $direction, $article1);
                    $article1 = str_replace('{service}', $service, $article1);

                    $jour_date = date("d M Y", strtotime($today));

                    if($employe->CODEEMP == 273 || $employe->CODEEMP == 462) $textes = $setting->TEXTE4;
                    else $textes = $setting->TEXTE;

                    $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="30px">&nbsp;</td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="130px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($textes) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPAL</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';

                    $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';


                    $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 2px; font-size: 10px" valign="middle">Le ' . self::trueDate2($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">du ' . self::trueDate2($debutservice) . ' au ' . self::trueDate2($finservice) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >' . $durer . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . ($durer - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >du ' . self::trueDate2($model->DEBUTPLANIF) . ' au ' . self::trueDate2($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . self::trueDate2($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $vnextconge . '</td>';

                    if ($employe->DEPLACE == 1) $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" >ADC S.A.</td>';

                    else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

                    if ($employe->CODEETS_EMB == "DLA") {

                        $di = Direction::findOne($employe->DIRECTION);

                        if (($di != null) && strpos($di->LIBELLE, 'Exploitation') !== false) {
                            $ampliation = Ampliation::findOne(8);
                        } else {
                            $ampliation = Ampliation::findOne(3);
                        }

                    } else if ($employe->CODEETS_EMB == "NSI") {

                        $di = Direction::findOne($employe->DIRECTION);

                        if (($di != null) && strpos($di->LIBELLE, 'International') !== false) {

                            $ampliation = Ampliation::findOne(7);
                        } else {
                            $ampliation = Ampliation::findOne(9);
                        }
                    } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);


                    $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

                    $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>';

                    if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                        $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px; padding: 10px;"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

                    }

                    if($employe->AMPLIATION != null) $tamp = $employe->AMPLIATION;
                    else if ($ampliation != null) $tamp = $ampliation->CONTENU;
                    else $tamp = "";

                    if($employe->CODEEMP == 273) $signature = $setting->SIGNATAIRE1;
                    else if($employe->CODEEMP == 462) $signature = $setting->SIGNATAIRE2;
                    else $signature = $setting->SIGNATAIRE;


                    $builder .= '
<tr>
<td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $signature. '</b><br>
							<br>
</td></tr>

<tr><td height="50px" style="text-align: left; padding: 10px;">
<u><b>Ampliations</b></u><br>
<div style="margin-left: 30px; font-size: 10px; font-style: italic">' . nl2br($tamp) . '</div>
</td>
<td style="text-align: left"></td></tr>

';
                }

                else {

                    $suspension->DEJA = 1; $suspension->save(false);

                    $textes = $setting->TEXTE3;

                    $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
                    $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
                    $article1 = str_replace('{nom}', $nom, $article1);
                    $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
                    $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
                    $article1 = str_replace('{departement}', $departement, $article1);
                    $article1 = str_replace('{plateforme}', $lieu, $article1);
                    $article1 = str_replace('{direction}', $direction, $article1);
                    $article1 = str_replace('{service}', $service, $article1);
                    $textes = str_replace('{date_debut}', $suspension->DATEDEBUT, $textes);
                    $textes = str_replace('{date_fin}', $suspension->DATEFIN, $textes);

                    $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>
<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="20px"></td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($textes) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SUSPENSION</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" width="20%" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS EXECUTES</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPALE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';


                    $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';

                    $debutsuspension = date('Y-m-d', strtotime($suspension->DATEDEBUT . ' -1 day'));
                    $finsuspension = date('Y-m-d', strtotime($suspension->DATEFIN . ' +1 day'));

                    $d1 = strtotime($debutservice);
                    $d2 = strtotime($debutsuspension);

                    $diff1 = $d2 - $d1;
                    $jour1 = abs(round($diff1 / 86400)) + 1;

                    $d3 = strtotime($finsuspension);
                    $d4 = strtotime($finservice);

                    $diff2 = $d4 - $d3;
                    $jour2 = abs(round($diff2 / 86400)) + 1;

                    $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 1px; font-size: 10px" valign="middle">Le ' . self::trueDate3($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td><td valign="middle" height="30px" style="text-align: center; padding: 2px; font-size: 10px">du ' . self::trueDate3($suspension->DATEDEBUT) . ' au ' . self::trueDate3($suspension->DATEFIN) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 0px; font-size: 10px"><table border="1" width="100%" height="auto"  style=" font-size: 10px; border-right: 1px solid #000000;" cellpadding="0" cellspacing="0"><tr><td style="" align="center">du<br>' . self::trueDate3($debutservice) . '<br>du<br>' . self::trueDate3($finsuspension) . '</td><td align="center">au<br>' . self::trueDate3($debutsuspension) . '<br>au<br>' . self::trueDate3($finservice) . '</td></tr></table></td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >' . $jour1 . ' Jours<br><br>' . $jour2 . ' Jours</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >' . $durer . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . ($durer - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >du ' . self::trueDate3($model->DEBUTPLANIF) . ' au ' . self::trueDate3($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . self::trueDate3($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px">' . $vnextconge . '</td>';

                    if ($employe->DEPLACE == 1) $builder .= '<td valign="middle" height="30px" style="text-align: center; padding: 1px; font-size: 10px" >ADC S.A.</td>'; else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

                    if ($employe->CODEETS_EMB == "DLA") {

                        if (strpos($employe->DIRECTION, 'Exploitation') !== false) {

                            $ampliation = Ampliation::findOne(8);
                        } else {
                            $ampliation = Ampliation::findOne(3);
                        }

                    } else if ($employe->CODEETS_EMB == "NSI") {

                        if (strpos($employe->DIRECTION, 'International') !== false) {

                            $ampliation = Ampliation::findOne(7);
                        } else {
                            $ampliation = Ampliation::findOne(9);
                        }
                    } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);

                    $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

                    $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>

';

                    if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                        $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px;  padding: 10px"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

                    }

                    //if ($ampliation != null) $tamp = $ampliation->CONTENU; else $tamp = "";
                    if($employe->AMPLIATION != null) $tamp = $employe->AMPLIATION;
                    else if ($ampliation != null) $tamp = $ampliation->CONTENU;
                    else $tamp = "";


                    if($employe->CODEEMP == 273) $signature = $setting->SIGNATAIRE1;
                    else if($employe->CODEEMP == 462) $signature = $setting->SIGNATAIRE2;
                    else $signature = $setting->SIGNATAIRE;

                    $builder .= '
<tr>
<td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $signature . '</b><br>
							<br>
</td></tr>

<tr><td height="50px" style="text-align: left; padding: 10px;">
<u><b>Ampliations</b></u><br>
<div style="margin-left: 30px; font-size: 10px; font-style: italic">' . nl2br($tamp) . '</div>
</td>
<td style="text-align: left"></td></tr>

';

                }

            }

            else {

                $article1 = str_replace('{nbjour}', $model->NBJOUR, $article1);
                $article1 = str_replace('{civilite}', $civile->LIBELLE, $article1);
                $article1 = str_replace('{nom}', $nom, $article1);
                $article1 = str_replace('{matricule}', $employe->MATRICULE, $article1);
                $article1 = str_replace('{poste}', $emploi->LIBELLE, $article1);
                $article1 = str_replace('{departement}', $departement, $article1);
                $article1 = str_replace('{plateforme}', $lieu, $article1);
                $article1 = str_replace('{direction}', $direction, $article1);
                $article1 = str_replace('{service}', $service, $article1);

                if($employe->CODEEMP == 273 || $employe->CODEEMP == 462) $textes = $setting->TEXTE4;
                else $textes = $setting->TEXTE;

                $builder .= '<html>
        <head><title>Projet de decision numero ' . $model->REF_DECISION . '</title></head> <body style="background: #ffffff; font-family: Arial, Helvetica, sans-serif"> 
        <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="color: #000000; font-size: 14px; border:0px solid #000000">
<tr><td colspan="2" height="80"></td></tr>

<tr><td colspan="2" height="80"></td></tr>
<tr><td height="30" style="padding: 5px" style="padding: 5px;" ></td><td style="padding: 5px" style="padding: 5px; font-weight: normal" align="right">Yaound&eacute; le  '.self::trueDate2($today).'</td></tr>

<tr><td colspan="2" height="20px"></td></tr>

<tr><td colspan="2" height="60px" style="text-align: left; padding-top: 10px; padding-left: 180px; padding-bottom: 10px; padding-right: 10px; font-weight: bold; line-height: 20px">Décision N° ' . $model->REF_DECISION . '<br>
	Accordant un congé annuel à ' . $civile->LIBELLE . ' ' . $employe->NOM . ' ' . $employe->PRENOM . ' (Mle ' . $employe->MATRICULE . ')<br><br>
LE DIRECTEUR GENERAL<br>
</td></tr>

<tr><td colspan="2" height="130px" style="text-align: left; padding: 10px; line-height: 18px;font-size: 12px">' . nl2br($textes) . '</td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 3px; padding-left: 10px; padding-top: 4px; font-size: 12px"> </td></tr>

<tr><td colspan="2"  style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 3px; font-size: 12px"><div style="font-size: 12px"><b>Considérant les nécessités de service,</b></div></td></tr>

<tr><td colspan="2" style=" text-align: center; padding-bottom: 10px; font-weight: bold;">DECIDE :</td></tr>

<tr><td colspan="2"   style="padding-right: 10px; padding-bottom: 10px; padding-left: 10px; padding-top: 4px; font-size: 12px">

<u><b>ARTICLE 1</b></u> : ' . $article1 . '<br>

<table width="100%" border="1" style="border: 1px solid #000000; margin-top: 10px" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE/LIEU RECRUTEMENT</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE SERVICE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CONGE PRINCIPAL</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS PERMISSION A DEDUIRE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>JOURS RESTANTS</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>PERIODE DE JOUISSANCE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>DATE DE REPRISE</b></td>
<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>EXIGIBILITE PROCHAIN CONGE</b></td>';

                $builder .= '<td style="font-size: 10px; text-align: center; padding: 2px" valign="middle"><b>CHARGE FRAIS DE TRANSPORT</b></td>';


                $builder .= '</tr>

<tr>
<td height="30px" style="text-align: center; padding: 2px; font-size: 10px" valign="middle">Le ' . self::trueDate2($employe->DATEEMBAUCHE) . ' à ' . $lieu . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">du ' . self::trueDate2($debutservice) . ' au ' . self::trueDate2($finservice) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >' . $durer . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . ($durer - $model->NBJOUR) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $model->NBJOUR . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px" >du ' . self::trueDate2($model->DEBUTPLANIF) . ' au ' . self::trueDate2($model->FINPLANIF) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . self::trueDate2($reprise) . '</td>
<td valign="middle" height="30px" style="text-align: center; padding: 2; font-size: 10px">' . $vnextconge . '</td>';

                if ($employe->DEPLACE == 1) $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" >ADC S.A.</td>';

                else $builder .= '<td valign="top" height="30px" style="text-align: center; padding: 2px; font-size: 10px" > / </td>';

                if ($employe->CODEETS_EMB == "DLA") {

                    if (strpos($employe->DIRECTION, 'Exploitation') !== false) {

                        $ampliation = Ampliation::findOne(8);
                    } else {
                        $ampliation = Ampliation::findOne(3);
                    }

                } else if ($employe->CODEETS_EMB == "NSI") {

                    if (strpos($employe->DIRECTION, 'International') !== false) {

                        $ampliation = Ampliation::findOne(7);
                    } else {
                        $ampliation = Ampliation::findOne(9);
                    }
                } else  $ampliation = Ampliation::findOne(['VILLE' => $employe->CODEETS_EMB]);


                $text3 = $employe->RH == 1 ? $setting->ARTICLE31 : $setting->ARTICLE3;

                $builder .= '</tr>
</table><br>

<u><b>ARTICLE 2</b></u> : ' . $setting->ARTICLE2 . '<br><br>

<u><b>ARTICLE 3</b></u> : ' . $text3 . '

</td></tr>';

                if (($model->COMMENTAIRE != NULL) && !empty($model->COMMENTAIRE)) {

                    $builder .= '<tr>
<td colspan="2" height="30px" style="text-align: left; font-size: 10px; padding: 10px;"><b>COMMENTAIRE : </b>' . nl2br($model->COMMENTAIRE) . '<br></td></tr>';

                }

                if($employe->CODEEMP == 273) $signature = $setting->SIGNATAIRE1;
                else if($employe->CODEEMP == 462) $signature = $setting->SIGNATAIRE2;
                else $signature = $setting->SIGNATAIRE;

                $builder .= '
                    <tr>
                    <td colspan="2" height="30px" style="text-align: left; padding-left: 300px"><br><b>' . $signature . '</b><br>	<br>
                    </td></tr>

                    <tr><td height="50px" style="text-align: left; padding: 10px;">
                    <u><b>Ampliations</b></u><br>
                    <div style="margin-left: 30px; font-size: 10px; font-style: italic">' . nl2br($ampliation->CONTENU) . '</div>
                    </td>
                    <td style="text-align: left"></td></tr>

                    ';
            }

            $builder.='<tr><td colspan="2" height="30" valign="bottom" align="center"></td></tr></table></body></html>';

            $nom = $employe->NOM." ".$employe->PRENOM;
            $fichier_nom = str_replace("é","e",$nom);
            $fichier_nom = str_replace("'","",$fichier_nom);
            $fichier_nom = str_replace(" ","-",$fichier_nom);
            $filename = 'Decision-'.$nom.'-'.$model->ANNEEEXIGIBLE.'-'.time().'.pdf';
            $repertoire = './web/uploads';

            $pdf = new Pdf([
                'mode' => '', // leaner size using standard fonts
                'format' => Pdf::FORMAT_A4,
                'orientation' => Pdf::ORIENT_PORTRAIT,
                'marginTop' => 10,
                'marginBottom' => 0,
                'marginLeft' => 10,
                'marginRight' => 10,
                //'destination' => Pdf::DEST_BROWSER,
                //'content' => $this->renderPartial('certificate_pdf', ['']),
                'options' => [
                    // any mpdf options you wish to set
                ],
                'methods' => [
                    'SetTitle' => 'Decision de congés',
                    'SetSubject' => '',
                    'SetAuthor' => 'ADC',
                    'SetCreator' => 'ADC',
                    'SetKeywords' => 'ADC',
                    'SetHeader'=>['<div style="text-align: left;  height: 100px"><img src="./web/img/logo.png" width="300px" height="auto" /></div>'],
                    'SetFooter'=>['<div style="text-align: center; color:#000000; font-size: 12px"><br>Siège Social : Aéroport International de Yaoundé – Nsimalen – BP 13615 Yaoundé<br>Tél. : (237) 222 23 36 02 / 222 23 45 21 – Fax (237) 222 23 45 20</div>'],
                ],

            ]);

            $mpdf = $pdf->api;
            //  $mpdf = new \Mpdf\Mpdf();

            $mpdf->SetHTMLHeader('<div style="text-align: left;  height: 100px"><img src="./web/img/logo.png" width="300px" height="auto" /></div>',true);

            $mpdf->SetHTMLFooter('<div style="text-align: center; color:#000000; font-size: 12px"><br>Siège Social : Aéroport International de Yaoundé – Nsimalen – BP 13615 Yaoundé<br>Tél. : (237) 222 23 36 02 / 222 23 45 21 – Fax (237) 222 23 45 20</div>',true);

            $mpdf->WriteHtml($builder);
            $path = $mpdf->Output('', 'S');

            $model->EDITION = $filename;
            $model->DATEREPRISE = $reprise;
            $model->DEBUTREELL = $debutservice;
            $model->FINREEL = $finservice;
            $model->DATECLOTURE = $nextconge;
            $model->FICHIER = 1;
            $model->save(false);
            $employe->DATECALCUL = $nextconge;
            $employe->save(false);

            echo $mpdf->Output($repertoire."/".$filename, 'F');

            return $filename;
            //return "ok";
        // }

        //else return "";

    }
}
