<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Suspension;

/**
 * SuspensionSearch represents the model behind the search form of `app\models\Suspension`.
 */
class SuspensionSearch extends Suspension
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
            [['ID_SUSPENSION', 'ANNEEEXIGIBLE'], 'integer'],
            [['MATICULE', 'DATEDEBUT', 'DATEFIN', 'STATUT', 'STATUTLEVE', 'DATEEMIS', 'DATEVAL', 'DATEANN','direction','service','departement'], 'safe'],
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
        if(Yii::$app->user->identity->ROLE == "R1" || Yii::$app->user->identity->ROLE == "R2")  $query = Suspension::find();

        else {

            $query = Suspension::find(['IDUSER'=>Yii::$app->user->identity->IDUSER]);

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
            'ID_SUSPENSION' => $this->ID_SUSPENSION,
            'ANNEEEXIGIBLE' => $this->ANNEEEXIGIBLE,
           // 'DATEDEBUT' => $this->DATEDEBUT,
           // 'DATEFIN' => $this->DATEFIN,
            'DATEEMIS' => $this->DATEEMIS,
            'DATEVAL' => $this->DATEVAL,
            'DATEANN' => $this->DATEANN,
        ]);

        $query->andFilterWhere(['like', 'MATICULE', $this->MATICULE])
            ->andFilterWhere(['like', 'STATUTLEVE', $this->STATUTLEVE])
            ->andFilterWhere(['like', 'STATUT', $this->STATUT]);

        if($this->DATEDEBUT && $this->DATEFIN) {

            $query->andWhere(['>=', 'DATEDEBUT', $this->DATEDEBUT])->andWhere(['<=', 'DATEFIN', $this->DATEFIN]);

        }

        if(isset($this->direction)) {

            $tab = array(); $tab2 = array();
            $emps = Employe::find()->where(["LIKE","DIRECTION",$this->direction])->all();
            foreach($emps as $emp) $tab[] = $emp->MATRICULE;


            $query->andFilterWhere(["IN","MATICULE",$tab]);

        }

        if(isset($this->service)) {

            $tab = array(); $tab2 = array();
            $emps = Employe::find()->where(["LIKE","SERVICE",$this->service])->all();
            foreach($emps as $emp) $tab[] = $emp->MATRICULE;

            $query->andFilterWhere(["IN","MATICULE",$tab]);
        }

        if(isset($departement)) {

            $tab = array(); $tab2 = array();
            $emps = Employe::find()->where(["CODEDPT"=>$departement])->all();
            foreach($emps as $emp) $tab[] = $emp->MATRICULE;

            $query->andFilterWhere(["IN","MATICULE",$tab]);
        }

        return $dataProvider;
    }
}
