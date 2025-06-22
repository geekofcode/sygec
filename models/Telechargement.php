<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Telechargement".
 *
 * @property string $id
 * @property string $dateperation
 * @property string $fichier
 */
class Telechargement extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'telechargement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dateoperation','fichier'], 'required'],
            [['fichier'], 'string', 'max' => 300],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fichier' => 'Fichier Zip de decisions',
            'dateoperation' => 'Date de génération',
        ];
    }

    public function showFile(){

        $path = $this->fichier;

        if(!empty($path)) { return '../web/uploads/'.$path;   }

    }
}
