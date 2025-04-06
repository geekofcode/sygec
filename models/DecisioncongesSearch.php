<?php

namespace app\models;

use kartik\daterange\DateRangeBehavior;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Decisionconges;

/**
 * DecisioncongesSearch represents the model behind the search form of `app\models\Decisionconges`.
 */
class DecisioncongesSearch extends Decisionconges
{
    public $direction;
    public $service;
    public $matricule1;

    /*public function behaviors()
    {
        return [
         [
             'class'=>DateRangeBehavior::className(),
             'attribute' =>'DEBUTPLANIF',
             'dateStartAttribute' => 'DEBUTPLANIF',
             'dateEndAttribute' => 'FINPLANIF',
             'dateStartFormat' => 'd-m-Y',
             'dateEndFormat' => 'd-m-Y'
         ]
        ];
    } */

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID_DECISION', 'ANNEEEXIGIBLE'], 'integer'],
            [['MATICULE', 'REF_DECISION', 'DEBUTPERIODE', 'FINPERIODE', 'DEBUTPLANIF', 'FINPLANIF', 'DEBUTREELL', 'FINREEL', 'STATUT', 'DATEEMIS', 'DATEVAL', 'DATESUSP', 'DATEANN', 'DATEREPRISE', 'DATECLOTURE', 'SITUTATIONFAMILIALE', 'MODETRANSPORT','HISTORIQUE','direction','service','FICHIER','matricule1'], 'safe'],
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
        // si admin ou gestionnaire
        if(Yii::$app->user->identity->ROLE == "R1" || Yii::$app->user->identity->ROLE == "R2") $query = Decisionconges::find();

        else $query = Decisionconges::find()->where(['IDUSER'=>Yii::$app->user->identity->IDUSER]);

        if(isset($_REQUEST["HISTORIQUE"])) $query->andWhere(["HISTORIQUE"=>$_REQUEST["HISTORIQUE"]]);

        if(Yii::$app->user->identity->ROLE == "R3") {

            if(Yii::$app->user->identity->DIRECTION != null) {
                $direction = array();
                $emps = Employe::find()->where(["DIRECTION" => Yii::$app->user->identity->DIRECTION])->all();
                foreach($emps as $emp) $direction[] = $emp->MATRICULE;
                $query->andWhere(["IN","MATICULE",$direction]);
            }

            if(Yii::$app->user->identity->DEPARTEMENT != null) {
                $direction = array();
                $emps = Employe::find()->where(["CODEDPT" => Yii::$app->user->identity->DEPARTEMENT])->all();
                foreach($emps as $emp) $direction[] = $emp->MATRICULE;
                $query->andWhere(["IN","MATICULE",$direction]);
            }

            if(Yii::$app->user->identity->SERVICE != null) {
                $direction = array();
                $emps = Employe::find()->where(["SERVICE" => Yii::$app->user->identity->SERVICE])->all();
                foreach($emps as $emp) $direction[] = $emp->MATRICULE;
                $query->andWhere(["IN","MATICULE",$direction]);
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
            'ID_DECISION' => $this->ID_DECISION,
            'ANNEEEXIGIBLE' => $this->ANNEEEXIGIBLE,
            'DEBUTPERIODE' => $this->DEBUTPERIODE,
            'FINPERIODE' => $this->FINPERIODE,
            'DEBUTREELL' => $this->DEBUTREELL,
            'FINREEL' => $this->FINREEL,
            'DATEEMIS' => $this->DATEEMIS,
            'DATEVAL' => $this->DATEVAL,
            'DATESUSP' => $this->DATESUSP,
            'DATEANN' => $this->DATEANN,
            'DATEREPRISE' => $this->DATEREPRISE,
            'DATECLOTURE' => $this->DATECLOTURE,
            'HISTORIQUE' => $this->HISTORIQUE,
            'FICHIER' => $this->FICHIER
        ]);

        $query->andFilterWhere(['like', 'MATICULE', $this->MATICULE])
            ->andFilterWhere(['like', 'REF_DECISION', $this->REF_DECISION])
           // ->andFilterWhere(['like', 'DEBUTPLANIF', $this->DEBUTPLANIF])
           // ->andFilterWhere(['like', 'FINPLANIF', $this->FINPLANIF])
            ->andFilterWhere(['like', 'STATUT', $this->STATUT])
            ->andFilterWhere(['like', 'SITUTATIONFAMILIALE', $this->SITUTATIONFAMILIALE])
            ->andFilterWhere(['like', 'MODETRANSPORT', $this->MODETRANSPORT]);

        if(isset($this->matricule1) && !empty($this->matricule1)) {
            $query->andWhere(['like', 'MATICULE', $this->matricule1]);
        }

        if($this->DEBUTPLANIF && $this->FINPLANIF) {

          //  list($start_date, $end_date) = explode(' - ', $this->DEBUTPLANIF);

            $query->andWhere(['>=', 'DEBUTPLANIF', $this->DEBUTPLANIF])->andWhere(['<=', 'DEBUTPLANIF', $this->FINPLANIF]);

        }

        if(isset($this->direction)) {
            if(!empty($this->direction)) {
                $tab = array();
                $emps = Employe::find()->where(["DIRECTION" => $this->direction])->all();
                foreach ($emps as $emp) $tab[] = $emp->MATRICULE;
                $query->andFilterWhere(["IN", "MATICULE", $tab]);
            }
        }

        if(isset($this->service)) {
            if(!empty($this->service)) {
                $tab = array();
                $emps = Employe::find()->where(["SERVICE" => $this->service])->all();
                foreach ($emps as $emp) $tab[] = $emp->MATRICULE;
                $query->andFilterWhere(["IN", "MATICULE", $tab]);
            }
        }

        return $dataProvider;
    }
}
