<?php

namespace app\models;

use Yii;
use app\models\Employe;
use app\models\Exercice;

/**
 * This is the model class for table "DECISIONCONGES".
 *
 * @property int $ID_DECISION
 * @property string $MATICULE
 * @property int $ANNEEEXIGIBLE
 * @property string $REF_DECISION
 * @property string $DEBUTPERIODE
 * @property string $FINPERIODE
 * @property string $DEBUTPLANIF
 * @property string $FINPLANIF
 * @property string $DEBUTREELL
 * @property string $FINREEL
 * @property string $STATUT
 * @property string $DATEEMIS
 * @property string $DATEVAL
 * @property string $DATESUSP
 * @property string $DATEANN
 * @property string $DATEREPRISE
 * @property string $DATECLOTURE
 * @property string $SITUTATIONFAMILIALE
 * @property string $MODETRANSPORT
 * @property integer $HISTORIQUE
 * @property string $COMMENTAIRE
 * @property string $EDITION
 * @property string $NUMERO
 * @property string $NBJOUR
 * @property string $DEPARTEMENT
 * @property string $FICHIER
 * @property int $IDUSER
 * @property int $TYPE_DEC
 *
 * @property EMPLOYE $mATICULE
 * @property EXERCICE $aNNEEEXIGIBLE
 * @property PRESTATIONCONGES[] $pRESTATIONCONGESs
 */
class Decisionconges extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'decisionconges';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ANNEEEXIGIBLE','TYPE_DEC'], 'integer'],
            [['ANNEEEXIGIBLE','MATICULE'], 'required'],
            [['DEBUTPERIODE', 'FINPERIODE', 'DEBUTPLANIF', 'FINPLANIF', 'DEBUTREELL', 'FINREEL', 'DATEEMIS', 'DATEVAL', 'DATESUSP', 'DATEANN', 'DATEREPRISE', 'DATECLOTURE','HISTORIQUE','COMMENTAIRE','EDITION','NUMERO','NBJOUR','DEPARTEMENT','FICHIER','IDUSER'], 'safe'],
            [['MATICULE'], 'string', 'max' => 10],
            [['REF_DECISION'], 'string', 'max' => 100],
            [['STATUT'], 'string', 'max' => 5],
            [['ANNEEEXIGIBLE'], 'required','on'=>'export'],
            [['SITUTATIONFAMILIALE', 'MODETRANSPORT'], 'string', 'max' => 255],
            [['MATICULE'], 'exist', 'skipOnError' => true, 'targetClass' => Employe::className(), 'targetAttribute' => ['MATICULE' => 'MATICULE']],
            [['ANNEEEXIGIBLE'], 'exist', 'skipOnError' => true, 'targetClass' => Exercice::className(), 'targetAttribute' => ['ANNEEEXIGIBLE' => 'ANNEEEXIGIBLE']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_DECISION' => 'Prestation de congés',
            'MATICULE' => 'Employé',
            'ANNEEEXIGIBLE' => 'Exercice',
            'REF_DECISION' => 'Référence  Décision',
            'DEBUTPERIODE' => 'Début exercice',
            'FINPERIODE' => 'Fin exercice',
            'DEBUTPLANIF' => 'Début planifié',
            'FINPLANIF' => 'Fin planifié',
            'DEBUTREELL' => 'Debut réel',
            'FINREEL' => 'Fin réel',
            'STATUT' => 'Statut',
            'DATEEMIS' => 'Date émission',
            'DATEVAL' => 'Date validation',
            'DATESUSP' => 'Date suspension',
            'DATEANN' => 'Date annulation',
            'DATEREPRISE' => 'Date reprise',
            'DATECLOTURE' => 'Date cloture',
            'SITUTATIONFAMILIALE' => 'Situtation familiale',
            'MODETRANSPORT' => 'Mode de transport',
            'EDITION' => 'Fichier édition',
            'DEPARTEMENT' => 'Département',
            'FICHIER' => 'EDITION'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMATICULE()
    {
        return $this->hasOne(EMPLOYE::className(), ['MATRICULE' => 'MATICULE']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getANNEEEXIGIBLE()
    {
        return $this->hasOne(EXERCICE::className(), ['ANNEEEXIGIBLE' => 'ANNEEEXIGIBLE']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPRESTATIONCONGESs()
    {
        return $this->hasMany(PRESTATIONCONGES::className(), ['ID_DECISION' => 'ID_DECISION']);
    }

    public function getStatut($statut){

        if($statut == "E") return "EMISE";

        else if($statut == "A") return "ANNULE";

        else if($statut == "V") return "VALIDER";

        else if($statut == "R") return "REPRISE NORMALE";

        else if($statut == "S") return "SUSPENDU";

        else if($statut == "C") return "CLOTURE";
    }

    public function getFichier(){

        if($this->FICHIER == 0) return "NON";

        else return "OUI";

    }

    public function getAnnee()
    {
        if($this->ANNEEEXIGIBLE != null) {

            $type = Exercice::findOne($this->ANNEEEXIGIBLE);

            if($type != null) return $type->ANNEEEXIGIBLE; else return "";
        }
        return "";
    }

    public function getEmploi()
    {
        if($this->MATICULE != null) {

            $type = Employe::findOne($this->MATICULE);

            if($type != null) return $type->getfullname(); else return "";
        }
        return "";
    }

    public function getDepartement()
    {
        if($this->DEPARTEMENT != null) {

            $type = Departements::findOne($this->DEPARTEMENT);

            if($type != null) return $type->LIBELLE; else return "";
        }
        return "";
    }

    public function showFile(){

        $path = $this->EDITION;

        if(!empty($path)) { return '../web/uploads/'.$path;   }

    }

    public function getName() {

        $d1 = date("d-m-Y",strtotime($this->DEBUTPLANIF));
        $d2 = date("d-m-Y", strtotime($this->FINPLANIF));

        return "Décision ".$this->REF_DECISION." pour une période de ".$this->NBJOUR." jour(s) (du ".$d1." au ".$d2.") ";

    }

    public function getName2() {

        $d1 = date("d-m-Y",strtotime($this->DEBUTPLANIF));
        $d2 = date("d-m-Y", strtotime($this->FINPLANIF));

        return "Décision ".$this->REF_DECISION." pour la période du ".$d1." au ".$d2."";

    }

    public function getName3() {

        $d1 = date("d-m-Y",strtotime($this->DEBUTPLANIF));
        $d2 = date("d-m-Y", strtotime($this->FINPLANIF));

        return "Décision ".$this->REF_DECISION." pour une période de ".$this->NBJOUR." jour(s) (du ".$d1." au ".$d2.")";

    }
}
