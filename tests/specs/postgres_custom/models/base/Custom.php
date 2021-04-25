<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property int $num
 * @property array $json1
 * @property array $json2
 * @property array $json3
 * @property array $json4
 * @property string $status
 * @property string $search
 *
 */
abstract class Custom extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%v3_pgcustom}}';
    }

    public function rules()
    {
        return [
            'trim' => [['status'], 'trim'],
            'num_integer' => [['num'], 'integer'],
            'status_string' => [['status'], 'string'],
            'status_in' => [['status'], 'in', 'range' => ['draft', 'pending', 'active']],
            'safe' => [['json1', 'json2', 'json3', 'json4'], 'safe'],
        ];
    }
}
