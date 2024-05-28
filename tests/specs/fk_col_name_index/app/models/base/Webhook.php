<?php

namespace app\models\base;

/**
 * example for x-fk-column-name
 *
 * @property int $id
 * @property string $name
 * @property int $user_id Test model for model code generation that should not contain id column in rules
 * @property int $redelivery_of
 * @property int $rd_abc_2
 *
 * @property \app\models\User $user
 * @property \app\models\Delivery $redeliveryOf
 * @property \app\models\Delivery $rd2
 */
abstract class Webhook extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%webhooks}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'user_id_integer' => [['user_id'], 'integer'],
            'user_id_exist' => [['user_id'], 'exist', 'targetRelation' => 'User'],
            'redelivery_of_integer' => [['redelivery_of'], 'integer'],
            'redelivery_of_exist' => [['redelivery_of'], 'exist', 'targetRelation' => 'RedeliveryOf'],
            'rd_abc_2_integer' => [['rd_abc_2'], 'integer'],
            'rd_abc_2_exist' => [['rd_abc_2'], 'exist', 'targetRelation' => 'Rd2'],
            'user_id_name_unique' => [['user_id', 'name'], 'unique', 'targetAttribute' => [
                'user_id',
                'name',
            ]],
            'redelivery_of_name_unique' => [['redelivery_of', 'name'], 'unique', 'targetAttribute' => [
                'redelivery_of',
                'name',
            ]],
            'rd_abc_2_name_unique' => [['rd_abc_2', 'name'], 'unique', 'targetAttribute' => [
                'rd_abc_2',
                'name',
            ]],
            'name_string' => [['name'], 'string', 'max' => 255],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(\app\models\User::class, ['id' => 'user_id']);
    }

    public function getRedeliveryOf()
    {
        return $this->hasOne(\app\models\Delivery::class, ['id' => 'redelivery_of']);
    }

    public function getRd2()
    {
        return $this->hasOne(\app\models\Delivery::class, ['id' => 'rd_abc_2']);
    }
}
