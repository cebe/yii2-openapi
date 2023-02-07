<?php

namespace app\models\pgsqlmodel\base;

/**
 * New Fresh table with new columns for migration code generation
 *
 * @property integer $custom_id_col
 * @property string $name
 * @property string $tag
 * @property string $new_col
 * @property double $col_5
 * @property double $col_6
 * @property double $col_7
 * @property array $col_8
 * @property string $col_9
 * @property string $col_10
 * @property string $col_11
 * @property double $price price in EUR
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
            'trim' => [['name', 'tag', 'new_col', 'col_9', 'col_10', 'col_11'], 'trim'],
            'required' => [['custom_id_col', 'name'], 'required'],
            'custom_id_col_integer' => [['custom_id_col'], 'integer'],
            'name_string' => [['name'], 'string'],
            'tag_string' => [['tag'], 'string'],
            'tag_default' => [['tag'], 'default', 'value' => '4 leg'],
            'new_col_string' => [['new_col'], 'string'],
            'col_5_double' => [['col_5'], 'double'],
            'col_6_double' => [['col_6'], 'double'],
            'col_7_double' => [['col_7'], 'double'],
            'col_9_string' => [['col_9'], 'string'],
            'col_10_string' => [['col_10'], 'string'],
            'col_11_string' => [['col_11'], 'string'],
            'price_double' => [['price'], 'double'],
            'price_default' => [['price'], 'default', 'value' => 0],
            'safe' => [['col_8'], 'safe'],
        ];
    }
}
