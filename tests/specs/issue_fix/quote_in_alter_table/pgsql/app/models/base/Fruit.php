<?php

namespace app\models\base;

/**
 * Quote in alter column in Pgsql test
 *
 * @property int $id
 * @property string $colourName
 *
 */
abstract class Fruit extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%fruits}}';
    }

    public function rules()
    {
        return [
            'trim' => [['colourName'], 'trim'],
            'colourName_string' => [['colourName'], 'string'],
        ];
    }
}
