<?php

namespace app\mymodels;

/**
 * A Pet
 *
 * @property int $id
 * @property string $name
 * @property string $tag
 */
class Pet extends \yii\db\ActiveRecord
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
            [['name', 'tag'], 'string'],
        ];
    }

}
