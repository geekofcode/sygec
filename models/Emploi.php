<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "EMPLOI".
 *
 * @property int $CODEEMP
 * @property string $LIBELLE
 *
 * @property EMPLOYE[] $eMPLOYEs
 */
class Emploi extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'emploi';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['LIBELLE'], 'required'],
            [['LIBELLE'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'CODEEMP' => 'ID',
            'LIBELLE' => 'Libelle',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEMPLOYEs()
    {
        return $this->hasMany(EMPLOYE::className(), ['CODEEMP' => 'CODEEMP']);
    }
}
