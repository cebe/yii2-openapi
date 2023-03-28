<?php

namespace app\models\base;

/**
 * Test model for model code generation that should not contain id column in rules
 *
 * @property int $id
 * @property string $name
 *
 */
abstract class User extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%users}}';
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
