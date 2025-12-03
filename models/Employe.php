<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "EMPLOYE".
 *
 * @property string $MATRICULE
 * @property string $CODECAT
 * @property string $CODEECH
 * @property string $CODEEMP
 * @property string $CODEETS
 * @property string $CODECIV
 * @property string $CODECONT
 * @property string $CODEETS_EMB
 * @property string $NOM
 * @property string $PRENOM
 * @property string $DATEEMBAUCHE
 * @property double $SOLDECREDIT
 * @property double $SOLDEAVANCE
 * @property string $DATECALCUL
 * @property string $CODEDPT
 * @property string $LASTCONGE
 * @property int $DEPLACE
 * @property string $DATNAISS
 * @property int $STATUT
 * @property string $COMMENTAIRE
 * @property string $DIRECTION
 * @property int $RH
 * @property int $ENFANT
 * @property string $SITMAT
 * @property string $JEUNEFILLE
 * @property string $VILLE
 * @property string $SERVICE
 * @property string $AMPLIATION
 * @property string $TIMBRE
 *
 * @property ABSENCEPONCTUEL[] $aBSENCEPONCTUELs
 * @property DECISIONCONGES[] $dECISIONCONGESs
 * @property CATEGORIE $cODECAT
 * @property CIVILITE $cODECIV
 * @property CONTRAT $cODECONT
 * @property ECHELLON $cODEECH
 * @property EMPLOI $cODEEMP
 * @property ETABLISSEMENT $cODEETS
 * @property ETABLISSEMENT $cODEETSEMB
 */
class Employe extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'employe';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MATRICULE','CODECAT','CODEECH','CODEEMP','CODEETS','CODECIV','CODECONT','SITMAT','CODEETS_EMB'], 'required'],
            [['DATEEMBAUCHE','SITMAT','JEUNEFILLE','SERVICE','VILLE','DATECALCUL','CODEDPT','LASTCONGE','COMMENTAIRE','STATUT','DATNAISS','DEPLACE','DIRECTION','RH','AMPLIATION','TIMBRE'], 'safe'],
            [['SOLDECREDIT', 'SOLDEAVANCE','ENFANT'], 'number'],
            [['MATRICULE'], 'string', 'max' => 10],
           // [['CODECAT', 'CODEECH', 'CODEEMP', 'CODEETS', 'CODECIV', 'CODECONT', 'CODEETS_EMB'], 'string', 'max' => 5],
            [['NOM', 'PRENOM'], 'string', 'max' => 500],
            [['MATRICULE'], 'unique'],
            [['CODECAT'], 'exist', 'skipOnError' => true, 'targetClass' => Categorie::className(), 'targetAttribute' => ['CODECAT' => 'CODECAT']],
            [['CODECIV'], 'exist', 'skipOnError' => true, 'targetClass' => Civilite::className(), 'targetAttribute' => ['CODECIV' => 'CODECIV']],
            [['CODECONT'], 'exist', 'skipOnError' => true, 'targetClass' => Contrat::className(), 'targetAttribute' => ['CODECONT' => 'CODECONT']],
            [['CODEECH'], 'exist', 'skipOnError' => true, 'targetClass' => Echellon::className(), 'targetAttribute' => ['CODEECH' => 'CODEECH']],
            [['CODEEMP'], 'exist', 'skipOnError' => true, 'targetClass' => Emploi::className(), 'targetAttribute' => ['CODEEMP' => 'CODEEMP']],
            [['CODEETS'], 'exist', 'skipOnError' => true, 'targetClass' => Etablissement::className(), 'targetAttribute' => ['CODEETS' => 'CODEETS']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MATRICULE' => 'Matricule',
            'CODECAT' => 'Catégorie',
            'CODEECH' => 'Echelon',
            'CODEEMP' => 'Poste',
            'CODEETS' => 'Lieu d\'embauche',
            'CODECIV' => 'Civilité',
            'CODECONT' => 'Contrats',
            'CODEETS_EMB' => 'Lieu d\'affectation',
            'NOM' => 'Nom',
            'PRENOM' => 'Prénom',
            'DATEEMBAUCHE' => 'Date embauche',
            'SOLDECREDIT' => 'Congés non consomés (En jours)',
            'SOLDEAVANCE' => 'Permission a débiter (En jours)',
            'DATECALCUL' => 'Date de prochain congé',
            'CODEDPT' => 'Département',
            'DIRECTION' => 'Direction',
            'RH' => 'Personnel RH',
            'DEPLACE' => 'Déplacé ',
            'SITMAT' => 'Situation matrimoniale',
            'DATNAISS' => 'Date de naissance',
            'AMPLIATION' => 'Ampliation',
            'TIMBRE' => 'Timbre de décisions'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getABSENCEPONCTUELs()
    {
        return $this->hasMany(ABSENCEPONCTUEL::className(), ['MATICULE' => 'MATRICULE']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDECISIONCONGESs()
    {
        return $this->hasMany(DECISIONCONGES::className(), ['MATICULE' => 'MATRICULE']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCODECAT()
    {
        return $this->hasOne(CATEGORIE::className(), ['CODECAT' => 'CODECAT']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCODECIV()
    {
        return $this->hasOne(CIVILITE::className(), ['CODECIV' => 'CODECIV']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCODECONT()
    {
        return $this->hasOne(CONTRAT::className(), ['CODECONT' => 'CODECONT']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCODEECH()
    {
        return $this->hasOne(ECHELLON::className(), ['CODEECH' => 'CODEECH']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCODEEMP()
    {
        return $this->hasOne(EMPLOI::className(), ['CODEEMP' => 'CODEEMP']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCODEETS()
    {
        return $this->hasOne(ETABLISSEMENT::className(), ['CODEETS' => 'CODEETS']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCODEETSEMB()
    {
        return $this->hasOne(ETABLISSEMENT::className(), ['CODEETS' => 'CODEETS_EMB']);
    }

    public function getCategorie(){

        if($this->CODECAT == NULL) return "Non Defini";

        else {

            $data = Categorie::findOne($this->CODECAT);

            if($data == null) return ""; else return $data->LIBELLE;
        }
    }

    public function getEchellon(){

        if($this->CODEECH == NULL) return "Non Defini";

        else {

            $data = Echellon::findOne($this->CODEECH);

            if($data == null) return ""; else return $data->LIBELLE;
        }
    }

    public function getEmploi(){

        if($this->CODEEMP == NULL) return "Non Defini";

        else {

            $data = Emploi::findOne($this->CODEEMP);

            if($data == null) return ""; else return $data->LIBELLE;
        }
    }

    public function getEtablissement(){

        if($this->CODEETS == NULL) return "Non Defini";

        else {

            $data = Etablissement::findOne($this->CODEETS);

            if($data == null) return ""; else return $data->LIBELLE;
        }
    }

    public function getEtablissement2(){

        if($this->CODEETS_EMB == NULL) return "Non Defini";

        else {

            $data = Etablissement::findOne($this->CODEETS_EMB);

            if($data == null) return ""; else return $data->LIBELLE;
        }
    }

    public function getCivilite(){

        if($this->CODECIV == NULL) return "Non Defini";

        else {

            $data = Civilite::findOne($this->CODECIV);

            if($data == null) return ""; else return $data->LIBELLE;
        }
    }

    public function getContrat(){

        if($this->CODECONT == NULL) return "Non Defini";

        else {

            $data = Contrat::findOne($this->CODECONT);

            if($data == null) return ""; else return $data->LIBELLE;
        }
    }

    public function getFullname(){

        return $this->NOM." ".$this->PRENOM;
    }

    public function getDepartement(){

        if($this->CODEDPT == NULL) return "Non Defini";

        else {

            $data = Departements::findOne($this->CODEDPT);

            if($data == null) return ""; else return $data->LIBELLE;
        }
    }

    public function getDirection(){

        if($this->DIRECTION == NULL) return "Non Defini";

        else {

            $data = Direction::findOne($this->DIRECTION);

            if($data == null) return ""; else  return $data->LIBELLE;
        }
    }

    public function getService(){

        if($this->SERVICE == NULL) return "Non Defini";

        else {

            $data = Service::findOne($this->SERVICE);

            if($data == null) return ""; else return $data->LIBELLE;
        }
    }

    public function getStatut(){

        if($this->STATUT == 1) return "ACTIF"; else return "INACTIF";
    }

    public function getDeplace(){

        if($this->DEPLACE == 1) return "OUI"; else return "NON";
    }

    public function getRH(){

        if($this->RH == 1) return "OUI"; else return "NON";
    }

    public function getConvertDate($date){

        if($date == NULL) return "Non Defini";

        else {

            $d = explode("-",$date);

            return $d[2]."/".$d[1]."/".$d[0];
        }
    }

}
