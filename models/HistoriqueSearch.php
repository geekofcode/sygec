<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Historique;

/**
 * HistoriqueSearch represents the model behind the search form of `app\models\Historique`.
 */
class HistoriqueSearch extends Historique
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID', 'QUANTITE'], 'integer'],
            [['LIBELLE', 'FICHIER'], 'safe'],
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
        $query = Historique::find();

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
            'QUANTITE' => $this->QUANTITE,
        ]);

        $query->andFilterWhere(['like', 'LIBELLE', $this->LIBELLE])
            ->andFilterWhere(['like', 'FICHIER', $this->FICHIER]);

        $query->orderBy(["ID" => SORT_DESC]);

        return $dataProvider;
    }
}
