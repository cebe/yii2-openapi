<?php

namespace app\models\base;

/**
 * Account
 *
 * @property int $id
 * @property string $name account name
 * @property string $paymentMethodName
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
            'trim' => [['name', 'paymentMethodName'], 'trim'],
            'required' => [['name'], 'required'],
            'name_string' => [['name'], 'string', 'max' => 128],
            'paymentMethodName_string' => [['paymentMethodName'], 'string'],
        ];
    }
}
