<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "PARAMETRE".
 *
 * @property string $NUMERO
 * @property int $DELAIEMISSION
 * @property string $SUFFIXEREF
 * @property int $DUREECONGES
 * @property int $DUREECONGES2
 * @property int $DUREESERVICE
 * @property string $TEXTE
 * @property string $TEXTE2
 * @property string $TEXTE3
 * @property string $TEXTE4
 * @property string $ARTICLE1
 * @property string $ARTICLE2
 * @property string $ARTICLE3
 * @property string $ARTICLE31
 * @property string $JOUISSANCE1
 * @property string $JOUISSANCE2
 * @property string $JOUISSANCE3
 * @property string $JOUISSANCE4
 * @property string $JOUISSANCE5
 * @property string $DUREERAPPEL
 * @property int $NONJOUISSANCE
 * @property string $SIGNATAIRE
 * @property string $SIGNATAIRE1
 * @property string $SIGNATAIRE2
 * @property string $TIMBREJOUISSANCE
 */
class Parametre extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'parametre';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['NUMERO', 'DUREECONGES', 'DUREESERVICE','SUFFIXEREF','TEXTE','TEXTE2','TEXTE3','TEXTE4','ARTICLE1','ARTICLE2','ARTICLE3','ARTICLE31','JOUISSANCE1','JOUISSANCE2','JOUISSANCE3','JOUISSANCE4','JOUISSANCE5','SIGNATAIRE','TIMBREJOUISSANCE','SIGNATAIRE1','SIGNATAIRE2'], 'required'],
            [['DELAIEMISSION', 'DUREECONGES', 'DUREESERVICE','DUREERAPPEL','NONJOUISSANCE','DUREECONGES2'], 'integer'],
            [['NUMERO'], 'string', 'max' => 5],
            [['SUFFIXEREF'], 'string', 'max' => 50],
            [['NUMERO'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'NUMERO' => 'Numéro',
            'DELAIEMISSION' => 'Délai de mission (en jour)',
            'SUFFIXEREF' => 'Timbre des décisions ',
            'DUREECONGES' => 'Durée de congés (en jours)',
            'DUREECONGES2' => 'Durée de congés (en jours) pour le DG/DGA',
            'DUREESERVICE' => 'Durée de service (en jours)',
            'TEXTE' => 'Texte pour les décisions de congés normale',
            'TEXTE2' => 'Texte pour les decisions de congés avec absence et suspension',
            'TEXTE3' => 'Texte pour les decisions de congés avec absence maladie',
            'TEXTE4' => 'Texte pour les decisions de congés du DG/DGA',
            'ARTICLE1' => 'Article 1 pour les décisions de congés',
            'ARTICLE2' => 'Article 2 pour les décisions de congés',
            'ARTICLE3' => 'Article 3 pour les décisions de congés personnel non RH',
            'ARTICLE31' => 'Article 3 pour les décisions de congés personnel RH',
            'JOUISSANCE1' => 'Texte de jouissance de congés',
            'JOUISSANCE2' => 'Texte de jouissance de congés partielle',
            'JOUISSANCE3' => 'Texte de non jouissance de congés',
            'JOUISSANCE4' => 'Texte de reliquat congés',
            'JOUISSANCE5' => 'Texte de report congés',
            'NONJOUISSANCE' => 'ACTIVER L\'EDITION DE NON JOUISSANCE',
            'SIGNATAIRE' => 'Nom du signataire des décisions',
            'SIGNATAIRE1' => 'Nom du signataire des décisions pour le DG',
            'SIGNATAIRE2' => 'Nom du signataire des décisions pour le DGA',
            'TIMBREJOUISSANCE' => 'Timbre jouissance de congés par défaut'
        ];
    }
}
