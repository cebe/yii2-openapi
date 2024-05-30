<?php

namespace app\models\base;

/**
 * PaymentMethod
 *
 * @property int $id
 * @property string $name
 *
 */
abstract class PaymentMethod extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%payment_methods}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'required' => [['name'], 'required'],
            'name_unique' => [['name'], 'unique'],
            'name_string' => [['name'], 'string', 'max' => 150],
        ];
    }
}
