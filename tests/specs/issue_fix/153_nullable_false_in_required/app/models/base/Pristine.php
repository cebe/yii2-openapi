<?php

namespace app\models\base;

/**
 * 153_nullable_false_in_required
 *
 * @property int $id
 * @property int $billing_factor integer between 0 and 100, default value 100
 *
 */
abstract class Pristine extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%pristines}}';
    }

    public function rules()
    {
        return [
            'required' => [['billing_factor'], 'required'],
            'billing_factor_integer' => [['billing_factor'], 'integer'],
            'billing_factor_default' => [['billing_factor'], 'default', 'value' => 100],
        ];
    }
}
