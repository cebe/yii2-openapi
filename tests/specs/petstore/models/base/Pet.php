<?php

namespace app\models\base;

/**
 * A Pet
 *
 * @property int $id
 * @property string $name
 * @property int $store_id A store's description
 * @property string $tag
 *
 * @property \app\models\Store $store
 */
abstract class Pet extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%pets}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name', 'tag'], 'trim'],
            'required' => [['name'], 'required'],
            'store_id_integer' => [['store_id'], 'integer'],
            'store_id_exist' => [['store_id'], 'exist', 'targetRelation' => 'Store'],
            'name_string' => [['name'], 'string'],
            'tag_string' => [['tag'], 'string'],
        ];
    }

    public function getStore()
    {
        return $this->hasOne(\app\models\Store::class, ['id' => 'store_id']);
    }
}
