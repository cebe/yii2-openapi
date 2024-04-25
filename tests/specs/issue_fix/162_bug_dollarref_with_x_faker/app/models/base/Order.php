<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $name2
 * @property int $invoice_id
 *
 * @property \app\models\Invoice $invoice
 */
abstract class Order extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%orders}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name', 'name2'], 'trim'],
            'invoice_id_integer' => [['invoice_id'], 'integer'],
            'invoice_id_exist' => [['invoice_id'], 'exist', 'targetRelation' => 'Invoice'],
            'name_string' => [['name'], 'string'],
            'name2_string' => [['name2'], 'string'],
        ];
    }

    public function getInvoice()
    {
        return $this->hasOne(\app\models\Invoice::class, ['id' => 'invoice_id']);
    }
}
