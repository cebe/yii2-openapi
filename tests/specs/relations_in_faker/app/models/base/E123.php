<?php

namespace app\models\base;

/**
 * desc
 *
 * @property int $id
 * @property string $name
 * @property int $b123_id desc
 *
 * @property \app\models\B123 $b123
 */
abstract class E123 extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%e123s}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'b123_id_integer' => [['b123_id'], 'integer'],
            'b123_id_exist' => [['b123_id'], 'exist', 'targetRelation' => 'B123'],
            'name_string' => [['name'], 'string'],
        ];
    }

    public function getB123()
    {
        return $this->hasOne(\app\models\B123::class, ['id' => 'b123_id']);
    }
}
