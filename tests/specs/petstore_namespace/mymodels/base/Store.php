<?php

namespace app\mymodels\base;

/**
 * A store's description
 *
 * @property int $id
 * @property string $name
 *
 */
abstract class Store extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%stores}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'required' => [['name'], 'required'],
            'name_string' => [['name'], 'string'],
        ];
    }

}
