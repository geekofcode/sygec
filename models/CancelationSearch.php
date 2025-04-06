<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Cancelation;

/**
 * CancelationSearch represents the model behind the search form of `app\models\Cancelation`.
 */
class CancelationSearch extends Cancelation
{

    public $direction;
    public $service;
    public $departement;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID', 'JOUISSANCE', 'PERIODE', 'IDUSER','STATUT'], 'integer'],
            [['DEBUT', 'COMMENTAIRE', 'FICHIER', 'DATECREATION','employe'], 'safe'],
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
        $query = Cancelation::find();

        if(Yii::$app->user->identity->ROLE == "R3") {

            if(Yii::$app->user->identity->DIRECTION != null) {
                $direction = array(); $t1 = array(); $t2 = array();
                $emps = Employe::find()->where(["DIRECTION" => Yii::$app->user->identity->DIRECTION])->all();
                foreach($emps as $emp) $direction[] = $emp->MATRICULE;
                $decs = Decisionconges::find()->where(["IN","MATICULE",$direction])->all();
                foreach($decs as $dec) $t1[] = $dec->ID_DECISION;
                $jouiss = Jouissance::find()->where(["IN","IDDECISION",$t1])->all();
                foreach($jouiss as $joui) $t2[] = $joui->IDNATURE;
                $query->andWhere(["IN","JOUISSANCE",$t2]);
            }

            if(Yii::$app->user->identity->DEPARTEMENT != null) {
                $direction = array(); $t1 = array();  $t2 = array();
                $emps = Employe::find()->where(["CODEDPT" => Yii::$app->user->identity->DEPARTEMENT])->all();
                foreach($emps as $emp) $direction[] = $emp->MATRICULE;
                $decs = Decisionconges::find()->where(["IN","MATICULE",$direction])->all();
                foreach($decs as $dec) $t1[] = $dec->ID_DECISION;
                $jouiss = Jouissance::find()->where(["IN","IDDECISION",$t1])->all();
                foreach($jouiss as $joui) $t2[] = $joui->IDNATURE;
                $query->andWhere(["IN","JOUISSANCE",$t2]);
            }

            if(Yii::$app->user->identity->SERVICE != null) {
                $direction = array(); $t1 = array(); $t2 = array();
                $emps = Employe::find()->where(["SERVICE" => Yii::$app->user->identity->SERVICE])->all();
                foreach($emps as $emp) $direction[] = $emp->MATRICULE;
                $decs = Decisionconges::find()->where(["IN","MATICULE",$direction])->all();
                foreach($decs as $dec) $t1[] = $dec->ID_DECISION;
                $jouiss = Jouissance::find()->where(["IN","IDDECISION",$t1])->all();
                foreach($jouiss as $joui) $t2[] = $joui->IDNATURE;
                $query->andWhere(["IN","JOUISSANCE",$t2]);
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
            'ID' => $this->ID,
            'JOUISSANCE' => $this->JOUISSANCE,
            //'DEBUT' => $this->DEBUT,
            'PERIODE' => $this->PERIODE,
            'IDUSER' => $this->IDUSER,
            'STATUT' => $this->STATUT,
            'DATECREATION' => $this->DATECREATION,
        ]);

        $query->andFilterWhere(['like', 'COMMENTAIRE', $this->COMMENTAIRE])
            ->andFilterWhere(['like', 'FICHIER', $this->FICHIER]);

        if(isset($this->DEBUT)) {

            if(!empty($this->DEBUT)) {

                list($start_date, $end_date) = explode(' - ', $this->DEBUT);

                $query->andWhere(['>=', 'DEBUT', $start_date])->andWhere(['<=', 'DEBUT', $end_date]);
            }

        }

        if(isset($this->direction)) {

            $tab = array(); $tab2 = array(); $tab3 = array();
            $emps = Employe::find()->where(["DIRECTION"=>$this->direction])->all();
            foreach($emps as $emp) $tab[] = $emp->MATRICULE;

            $ds = Decisionconges::find()->where(["IN","MATICULE",$tab])->all();
            foreach ($ds as $d) $tab2[] = $d->ID_DECISION;

            $js = Jouissance::find()->where(["IN","IDDECISION",$tab2])->all();
            foreach ($js as $j) $tab3[] = $j->IDNATURE;

            $query->andFilterWhere(["IN","JOUISSANCE",$tab3]);

        }

        if(isset($this->service)) {

            $tab = array(); $tab2 = array(); $tab3 = array();
            $emps = Employe::find()->where(["SERVICE"=>$this->service])->all();
            foreach($emps as $emp) $tab[] = $emp->MATRICULE;

            $ds = Decisionconges::find()->where(["IN","MATICULE",$tab])->all();
            foreach ($ds as $d) $tab2[] = $d->ID_DECISION;

            $js = Jouissance::find()->where(["IN","IDDECISION",$tab2])->all();
            foreach ($js as $j) $tab3[] = $j->IDNATURE;

            $query->andFilterWhere(["IN","JOUISSANCE",$tab3]);
        }

        if(isset($this->departement)) {

            $tab = array(); $tab2 = array(); $tab3 = array();
            $emps = Employe::find()->where(["CODEDPT"=>$this->departement])->all();
            foreach($emps as $emp) $tab[] = $emp->MATRICULE;

            $ds = Decisionconges::find()->where(["IN","MATICULE",$tab])->all();
            foreach ($ds as $d) $tab2[] = $d->ID_DECISION;

            $js = Jouissance::find()->where(["IN","IDDECISION",$tab2])->all();
            foreach ($js as $j) $tab3[] = $j->IDNATURE;

            $query->andFilterWhere(["IN","JOUISSANCE",$tab3]);
        }

        if(isset($this->employe)) {

            $tab = array(); $tab2 = array();

            $ds = Decisionconges::find()->where(["MATICULE" => $this->employe])->all();
            foreach ($ds as $d) $tab2[] = $d->ID_DECISION;

            $js = Jouissance::find()->where(["IN","IDDECISION",$tab2])->all();
            foreach ($js as $j) $tab[] = $j->IDNATURE;

            $query->andFilterWhere(["IN","JOUISSANCE",$tab]);
        }

        return $dataProvider;
    }
}
