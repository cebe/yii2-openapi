<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property bool $active
 * @property float $floatval
 * @property float $floatval_lim
 * @property double $doubleval
 * @property int $int_min
 * @property int $int_max
 * @property int $int_minmax
 * @property int $int_created_at
 * @property int $int_simple
 * @property string $str_text
 * @property string $str_varchar
 * @property string $str_date
 * @property string $str_datetime
 * @property string $str_country
 *
 */
abstract class Fakerable extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%fakerable}}';
    }

    public function rules()
    {
        return [
            'trim' => [['str_text', 'str_varchar', 'str_date', 'str_datetime', 'str_country'], 'trim'],
            'active_boolean' => [['active'], 'boolean'],
            'floatval_double' => [['floatval'], 'double'],
            'floatval_lim_double' => [['floatval_lim'], 'double', 'min' => 0, 'max' => 1],
            'doubleval_double' => [['doubleval'], 'double'],
            'int_min_integer' => [['int_min'], 'integer', 'min' => 5],
            'int_min_default' => [['int_min'], 'default', 'value' => 3],
            'int_max_integer' => [['int_max'], 'integer', 'max' => 5],
            'int_minmax_integer' => [['int_minmax'], 'integer', 'min' => 5, 'max' => 25],
            'int_created_at_integer' => [['int_created_at'], 'integer'],
            'int_simple_integer' => [['int_simple'], 'integer'],
            'str_text_string' => [['str_text'], 'string'],
            'str_varchar_string' => [['str_varchar'], 'string', 'max' => 100],
            'str_date_date' => [['str_date'], 'date', 'format' => 'php:Y-m-d'],
            'str_datetime_datetime' => [['str_datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            'str_country_string' => [['str_country'], 'string'],
        ];
    }
}
