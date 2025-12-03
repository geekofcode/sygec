<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Jouissance;

/**
 * JouissanceSearch represents the model behind the search form of `app\models\Jouissance`.
 */
class JouissanceSearch extends Jouissance
{
    public $direction;
    public $service;
    public $departement;
    public $employe;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['IDNATURE', 'IDDECISION', 'USERCREATE'], 'integer'],
            [['TITRE', 'NUMERO', 'DEBUT', 'FIN', 'MESSAGE', 'LIEU', 'JOUR', 'TYPES', 'DOCUMENT', 'DATECREATION', 'EXERCICE','direction','service','departement','employe','PLATEFORME','STATUT'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        if(Yii::$app->user->identity->ROLE == "R1" || Yii::$app->user->identity->ROLE == "R2")  $query = Jouissance::find();

        else $query = Jouissance::find(['IDUSER'=>Yii::$app->user->identity->IDUSER]);

        if(Yii::$app->user->identity->ROLE == "R3") {

            if(Yii::$app->user->identity->DIRECTION != null) {
                $direction = array(); $t1 = array();
                $emps = Employe::find()->where(["DIRECTION" => Yii::$app->user->identity->DIRECTION])->all();
                foreach($emps as $emp) $direction[] = $emp->MATRICULE;
                $decs = Decisionconges::find()->where(["IN","MATICULE",$direction])->all();
                foreach($decs as $dec) $t1[] = $dec->ID_DECISION;
                $query->andWhere(["IN","IDDECISION",$t1]);
            }

            if(Yii::$app->user->identity->DEPARTEMENT != null) {
                $direction = array(); $t1 = array();
                $emps = Employe::find()->where(["CODEDPT" => Yii::$app->user->identity->DEPARTEMENT])->all();
                foreach($emps as $emp) $direction[] = $emp->MATRICULE;
                $decs = Decisionconges::find()->where(["IN","MATICULE",$direction])->all();
                foreach($decs as $dec) $t1[] = $dec->ID_DECISION;
                $query->andWhere(["IN","IDDECISION",$t1]);
            }

            if(Yii::$app->user->identity->SERVICE != null) {
                $direction = array(); $t1 = array();
                $emps = Employe::find()->where(["SERVICE" => Yii::$app->user->identity->SERVICE])->all();
                foreach($emps as $emp) $direction[] = $emp->MATRICULE;
                $decs = Decisionconges::find()->where(["IN","MATICULE",$direction])->all();
                foreach($decs as $dec) $t1[] = $dec->ID_DECISION;
                $query->andWhere(["IN","IDDECISION",$t1]);
            }
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'IDNATURE' => $this->IDNATURE,
            'JOUR' => $this->JOUR,
            'IDDECISION' => $this->IDDECISION,
            'DATECREATION' => $this->DATECREATION,
            'USERCREATE' => $this->USERCREATE,
            'PLATEFORME' => $this->PLATEFORME,
            'STATUT' => $this->STATUT,
            'EXERCICE' => $this->EXERCICE,
            'TYPES' => $this->TYPES
        ]);

        $query->andFilterWhere(['like', 'TITRE', $this->TITRE])
            ->andFilterWhere(['like', 'NUMERO', $this->NUMERO])
            ->andFilterWhere(['like', 'DEBUT', $this->DEBUT])
            ->andFilterWhere(['like', 'FIN', $this->FIN])
            ->andFilterWhere(['like', 'MESSAGE', $this->MESSAGE])
            ->andFilterWhere(['like', 'LIEU', $this->LIEU])
            ->andFilterWhere(['like', 'DOCUMENT', $this->DOCUMENT]);


        if(isset($this->direction) && $this->direction != "") {

            $tab = array(); $tab2 = array();
            $emps = Employe::find()->where(["DIRECTION"=>$this->direction])->all();
            foreach($emps as $emp) $tab[] = $emp->MATRICULE;

            $ds = Decisionconges::find()->where(["IN","MATICULE",$tab])->all();
            foreach ($ds as $d) $tab2[] = $d->ID_DECISION;

            $query->andFilterWhere(["IN","IDDECISION",$tab2]);

        }

        if(isset($this->service) && $this->service != "") {

            $tab = array(); $tab2 = array();
            $emps = Employe::find()->where(["SERVICE"=>$this->service])->all();
            foreach($emps as $emp) $tab[] = $emp->MATRICULE;

            $ds = Decisionconges::find()->where(["IN","MATICULE",$tab])->all();
            foreach ($ds as $d) $tab2[] = $d->ID_DECISION;

            $query->andFilterWhere(["IN","IDDECISION",$tab2]);
        }


        if(isset($this->departement) && $this->departement!= ""){

            $tab = array(); $tab2 = array();
            $emps = Employe::find()->where(["CODEDPT"=>$this->departement])->all();
            foreach($emps as $emp) $tab[] = $emp->MATRICULE;

            $ds = Decisionconges::find()->where(["IN","MATICULE",$tab])->all();
            foreach ($ds as $d) $tab2[] = $d->ID_DECISION;

            $query->andFilterWhere(["IN","IDDECISION",$tab2]);
        }

        if(isset($this->employe) && $this->employe != "") {

            $tab2 = array();

            $ds = Decisionconges::find()->where(["MATICULE"=>$this->employe])->all();

            foreach ($ds as $d) $tab2[] = $d->ID_DECISION;

            $query->andFilterWhere(["IN","IDDECISION",$tab2]);
        }

        return $dataProvider;
    }
}
