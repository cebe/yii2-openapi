<?php

namespace app\models\base;

/**
 * desc
 *
 * @property int $id
 * @property string $name
 * @property int $c123_id desc
 *
 * @property \app\models\C123 $c123
 */
abstract class B123 extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%b123s}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'c123_id_integer' => [['c123_id'], 'integer'],
            'c123_id_exist' => [['c123_id'], 'exist', 'targetRelation' => 'C123'],
            'name_string' => [['name'], 'string'],
        ];
    }

    public function getC123()
    {
        return $this->hasOne(\app\models\C123::class, ['id' => 'c123_id']);
    }
}
