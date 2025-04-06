<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "AMPLIATION".
 *
 * @property int $ID
 * @property string $VILLE
 * @property string $CONTENU
 * @property string $TIMBRE
 */
class Ampliation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ampliation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['CONTENU','TIMBRE'], 'string'],
            [['VILLE'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'VILLE' => 'Ville',
            'CONTENU' => 'Contenu',
            'TIMBRE' => 'Timbre jouissance de congés'
        ];
    }
}
