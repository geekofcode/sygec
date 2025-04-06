<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Emploi;

class EmploiSearch extends Emploi
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['CODEEMP'], 'required'],
            [['CODEEMP'], 'string', 'max' => 5],
            [['LIBELLE'], 'string', 'max' => 50],
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
        $query = Emploi::find();

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
            'CODEEMP' => $this->CODEEMP,
        ]);

        $query->andFilterWhere(['like', 'LIBELLE', $this->LIBELLE]);

        return $dataProvider;
    }

}