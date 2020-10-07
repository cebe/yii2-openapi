<?php

namespace app\models\base;

/**
 * A Pet
 *
 * @property int $id
 * @property string $name
 * @property string $tag
 *
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
            'name_string' => [['name'], 'string'],
            'tag_string' => [['tag'], 'string'],
        ];
    }

}
