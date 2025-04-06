<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "JOUISSANCE".
 *
 * @property int $IDNATURE
 * @property string $TITRE
 * @property string $NUMERO
 * @property string $DEBUT
 * @property string $FIN
 * @property string $MESSAGE
 * @property string $LIEU
 * @property string $JOUR
 * @property string $TYPES
 * @property string $DOCUMENT
 * @property int $IDDECISION
 * @property string $DATECREATION
 * @property int $USERCREATE
 * @property string $EXERCICE
 * @property string $STATUT
 * @property string $COMMENTAIRE
 * @property string $DOCUMENT2
 * @property string $NUMERO2
 * @property string $DATECANCEL
 * @property int $IDUSER
 * @property string $DEBUTREPORT
 * @property string $FINREPORT
 * @property string $DOCUMENT3
 * @property string $DOCUMENT4
 * @property string $TIMBRE
 * @property string $SIGNATAIRE
 * @property string $RESPONSABLE
 * @property string $PLATEFORME
 */
class Jouissance extends \yii\db\ActiveRecord
{
    public $DOCUMENTFILE, $DOCUMENTFILE2;
    public $employe;
    public $debutconge;
    public $nbjour;
    public $timbre;
    public $signataire;
    public $decision;
    public $jouissances;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jouissance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MESSAGE','employe','debutconge','timbre','signataire','decision','jouissances'], 'string'],
            [['IDDECISION','TYPES','PLATEFORME'],'required'],
            [['TIMBRE','SIGNATAIRE','RESPONSABLE'],'safe'],
            [['JOUR', 'DATECREATION','EXERCICE','STATUT','COMMENTAIRE','DOCUMENT2','NUMERO2','DATECANCEL','IDUSER','DEBUTREPORT','FINREPORT'], 'safe'],
            [['IDDECISION', 'USERCREATE','nbjour'], 'integer'],
            [['TITRE', 'DEBUT', 'LIEU', 'DOCUMENT','DOCUMENT3','DOCUMENT4'], 'string', 'max' => 100],
            [['NUMERO'], 'string', 'max' => 50],
            [['EXERCICE'], 'required','on'=>'export'],
            [['TYPES', 'EXERCICE'], 'string', 'max' => 10],
            [['DOCUMENTFILE','DOCUMENTFILE2'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png,jpeg,jpg,PNG,JPEG,JPG,pdf,PDF,doc,docx,DOC,DOCX'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'IDNATURE' => 'Idnature',
            'TITRE' => 'Titre',
            'NUMERO' => 'Timbre',
            'NUMERO2' => 'Numéro de jouissance précedent',
            'DEBUT' => 'Debut période',
            'FIN' => 'Fin période',
            'DEBUTREPORT' => 'Debut report période',
            'FINREPORT' => 'Fin report période',
            'MESSAGE' => 'Message',
            'LIEU' => 'Lieu de saisie',
            'JOUR' => 'Jour de saisie',
            'TYPES' => 'Types',
            'DOCUMENT' => 'Document',
            'IDDECISION' => 'Décision de congés',
            'DATECREATION' => 'Date de création',
            'USERCREATE' => 'User create',
            'EXERCICE' => 'Exercice',
            'DOCUMENT' => 'Document de jouissance',
            'PLATEFORME' => 'Plateforme Emettrice',
            'COMMENTAIRE' => 'Commentaire',
            'DOCUMENT2' => 'Piece jointe jouissance',
            'DOCUMENT3' => 'Piece jointe report',
            'DOCUMENT4' => 'Document de report',
            'employe' => 'Employé',
            'debutconge' => 'Début de la période',
            'nbjour' => 'Nombre de jour voulu',
            'timbre' => 'Timbre du document',
            'signataire' => 'Signataire du document'
        ];
    }


    public function getDOC(){

        $path = $this->DOCUMENT2;

        if(!empty($path)) echo "<a href='../web/uploads/$path' target='_blank'>Fichier actuel Ici</a><br><br>";

    }

    public function getDOC1(){

        $path = $this->DOCUMENT3;

        if(!empty($path)) echo "<a href='../web/uploads/$path' target='_blank'>Fichier actuel Ici</a><br><br>";

    }

    public function showFile(){

        $path = $this->DOCUMENT;

        if(!empty($path)) { return '../web/uploads/'.$path;   }

    }


    public function showFile2(){

        $path = $this->DOCUMENT2;

        if(!empty($path)) { return '../web/uploads/'.$path;   }

    }


    public function showFile3(){

        $path = $this->DOCUMENT3;

        if(!empty($path)) { return '../web/uploads/'.$path;   }

    }

    public function showFile4(){

        $path = $this->DOCUMENT4;

        if(!empty($path)) { return '../web/uploads/'.$path;   }

    }


    public function getStatut($statut){

        if($statut == "B") return "BROUILLON";

        else if($statut == "A") return "ANNULEE";

        else if($statut == "V") return "VALIDEE";

        else if($statut == "R") return "REPORTEE";

        else if($statut == "S") return "SUSPENDUE";
    }

    public function getType(){

        if($this->TYPES == "01") return "JOUISSANCE TOTALE";

        else if($this->TYPES == "02") return "JOUISSANCE PARTIELLE";

        else if($this->TYPES == "03") return "NON JOUISSANCE";

        else if($this->TYPES == "04") return "RELIQUAT CONGES";

        else if($this->TYPES == "05") return "REPORT CONGES";
    }

    public function getDecision(){

        $item = Decisionconges::findOne($this->IDDECISION);

        if($item == null) return "";

        else return $item->REF_DECISION;
    }


    public function getName() {

        $d1 = date("d-m-Y",strtotime($this->DEBUT));
        $d2 = date("d-m-Y", strtotime($this->FIN));

        return "Jouissance ".$this->NUMERO." pour la période du ".$d1." au ".$d2." ";

    }
}
