<?php

namespace app\models\base;

/**
 * All DB data type
 *
 * @property int $id
 * @property string $string_col
 * @property string $varchar_col
 * @property string $text_col
 * @property string $varchar_4_col
 * @property string $char_4_col
 * @property string $char_5_col
 * @property string $char_6_col
 * @property string $char_7_col
 * @property string $char_8_col
 * @property double $decimal_col
 * @property resource $varbinary_col
 * @property resource $blob_col
 * @property integer $bit_col
 * @property integer $bit_2
 * @property integer $bit_3
 * @property integer $ti
 * @property integer $ti_2
 * @property integer $ti_3
 * @property integer $si_col
 * @property integer $si_col_2
 * @property integer $mi
 * @property integer $bi
 * @property integer $int_col
 * @property integer $int_col_2
 * @property double $numeric_col
 * @property double $float_col
 * @property double $float_2
 * @property double $float_3
 * @property double $double_col
 * @property double $double_p
 * @property double $double_p_2
 * @property double $real_col
 * @property string $date_col
 * @property string $time_col
 * @property string $datetime_col
 * @property string $timestamp_col
 * @property string $year_col
 * @property array $json_col
 * @property array $json_col_def
 * @property array $json_col_def_2
 * @property resource $blob_def
 * @property string $text_def
 * @property array $json_def
 *
 */
abstract class Alldbdatatype extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%alldbdatatypes}}';
    }

    public function rules()
    {
        return [
            'trim' => [['string_col', 'varchar_col', 'text_col', 'varchar_4_col', 'char_4_col', 'char_5_col', 'char_6_col', 'char_7_col', 'char_8_col', 'date_col', 'time_col', 'datetime_col', 'timestamp_col', 'year_col', 'text_def'], 'trim'],
            'required' => [['char_6_col'], 'required'],
            'string_col_string' => [['string_col'], 'string', 'max' => 255],
            'varchar_col_string' => [['varchar_col'], 'string', 'max' => 132],
            'text_col_string' => [['text_col'], 'string'],
            'varchar_4_col_string' => [['varchar_4_col'], 'string', 'max' => 4],
            'char_4_col_string' => [['char_4_col'], 'string', 'max' => 4],
            'char_5_col_string' => [['char_5_col'], 'string'],
            'char_6_col_string' => [['char_6_col'], 'string'],
            'char_7_col_string' => [['char_7_col'], 'string', 'max' => 6],
            'char_8_col_string' => [['char_8_col'], 'string'],
            'decimal_col_double' => [['decimal_col'], 'double'],
            'numeric_col_double' => [['numeric_col'], 'double'],
            'float_col_double' => [['float_col'], 'double'],
            'float_2_double' => [['float_2'], 'double'],
            'float_3_double' => [['float_3'], 'double'],
            'double_col_double' => [['double_col'], 'double'],
            'double_p_double' => [['double_p'], 'double'],
            'double_p_2_double' => [['double_p_2'], 'double'],
            'real_col_double' => [['real_col'], 'double'],
            'date_col_date' => [['date_col'], 'date'],
            'time_col_time' => [['time_col'], 'time'],
            'datetime_col_datetime' => [['datetime_col'], 'datetime'],
            'timestamp_col_string' => [['timestamp_col'], 'string'],
            'year_col_string' => [['year_col'], 'string'],
            'text_def_string' => [['text_def'], 'string'],
            'safe' => [['varbinary_col', 'blob_col', 'bit_col', 'bit_2', 'bit_3', 'ti', 'ti_2', 'ti_3', 'si_col', 'si_col_2', 'mi', 'bi', 'int_col', 'int_col_2', 'json_col', 'json_col_def', 'json_col_def_2', 'blob_def', 'json_def'], 'safe'],
        ];
    }
}
