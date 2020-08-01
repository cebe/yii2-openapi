<?php

namespace app\mymodels\base;

/**
 * A Pet
 *
 * @property int $id
 * @property string $name
 * @property int $store_id A store's description
 * @property string $tag
 *
 * @property \app\mymodels\Store $store
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
            [['name', 'tag'], 'trim'],
            [['name'], 'required'],
            [['store_id'], 'integer'],
            [['store_id'], 'exist', 'targetRelation'=>'Store'],
            [['name', 'tag'], 'string'],
        ];
    }

    public function getStore()
    {
        return $this->hasOne(\app\mymodels\Store::class,['id' => 'store_id']);
    }
}
