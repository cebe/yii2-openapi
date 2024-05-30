<?php

namespace app\models\base;

/**
 * Mailing
 *
 * @property int $id
 * @property string $name name
 * @property string $paymentMethodName
 *
 */
abstract class Mailing extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%mailings}}';
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
