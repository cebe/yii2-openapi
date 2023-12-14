<?php

namespace app\models\base;

/**
 * user account
 *
 * @property int $id
 * @property string $name account name
 *
 */
abstract class Account extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%accounts}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'required' => [['name'], 'required'],
            'name_string' => [['name'], 'string', 'max' => 40],
        ];
    }
}
