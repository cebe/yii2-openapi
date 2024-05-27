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
 * @property string $status_x
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
            'trim' => [['status', 'status_x'], 'trim'],
            'num_integer' => [['num'], 'integer'],
            'num_default' => [['num'], 'default', 'value' => 0],
            'json1_default' => [['json1'], 'default', 'value' => []],
            'json2_default' => [['json2'], 'default', 'value' => []],
            'json3_default' => [['json3'], 'default', 'value' => [
                [
                    'foo' => 'foobar',
                ],
                [
                    'xxx' => 'yyy',
                ],
            ]],
            'json4_default' => [['json4'], 'default', 'value' => [
                'foo' => 'bar',
                'bar' => 'baz',
            ]],
            'status_string' => [['status'], 'string'],
            'status_in' => [['status'], 'in', 'range' => [
                'active',
                'draft',
            ]],
            'status_default' => [['status'], 'default', 'value' => 'draft'],
            'status_x_string' => [['status_x'], 'string', 'max' => 10],
            'status_x_in' => [['status_x'], 'in', 'range' => [
                'active',
                'draft',
            ]],
            'status_x_default' => [['status_x'], 'default', 'value' => 'draft'],
            'safe' => [['json1', 'json2', 'json3', 'json4'], 'safe'],
        ];
    }
}
