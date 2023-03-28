<?php

namespace app\models\base;

/**
 * example for x-fk-column-name
 *
 * @property int $id
 * @property string $name
 * @property int $user_id Test model for model code generation that should not contain id column in rules
 * @property int $redelivery_of
 *
 * @property \app\models\User $user
 * @property \app\models\Delivery $redeliveryOf
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
            'name_string' => [['name'], 'string'],
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
}
