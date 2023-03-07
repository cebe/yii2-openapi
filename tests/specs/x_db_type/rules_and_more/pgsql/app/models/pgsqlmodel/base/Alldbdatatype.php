<?php

namespace app\models\pgsqlmodel\base;

/**
 * All DB data type
 *
 * @property int $id
 * @property string $string_col
 * @property string $varchar_col
 * @property string $text_col
 * @property array $text_col_array
 * @property string $varchar_4_col
 * @property string $varchar_5_col
 * @property string $char_4_col
 * @property string $char_5_col
 * @property string $char_6_col
 * @property string $char_7_col
 * @property string $char_8_col
 * @property double $decimal_col
 * @property resource $bytea_col_2
 * @property integer $bit_col
 * @property integer $bit_2
 * @property integer $bit_3
 * @property integer $ti
 * @property integer $int2_col
 * @property integer $smallserial_col
 * @property integer $serial2_col
 * @property integer $si_col
 * @property integer $si_col_2
 * @property integer $bi
 * @property integer $bi2
 * @property integer $int4_col
 * @property integer $bigserial_col
 * @property integer $bigserial_col_2
 * @property integer $int_col
 * @property integer $int_col_2
 * @property double $numeric_col
 * @property double $numeric_col_2
 * @property double $numeric_col_3
 * @property double $double_p_2
 * @property double $double_p_3
 * @property double $real_col
 * @property double $float4_col
 * @property string $date_col
 * @property string $time_col
 * @property string $time_col_2
 * @property string $time_col_3
 * @property string $time_col_4
 * @property string $timetz_col
 * @property string $timetz_col_2
 * @property string $timestamp_col
 * @property string $timestamp_col_2
 * @property string $timestamp_col_3
 * @property string $timestamp_col_4
 * @property string $timestamptz_col
 * @property string $timestamptz_col_2
 * @property string $date2
 * @property string $timestamp_col_z
 * @property integer $bit_varying
 * @property integer $bit_varying_n
 * @property integer $bit_varying_n_2
 * @property integer $bit_varying_n_3
 * @property boolean $bool_col
 * @property boolean $bool_col_2
 * @property string $box_col
 * @property string $character_col
 * @property string $character_n
 * @property string $character_varying
 * @property string $character_varying_n
 * @property array $json_col
 * @property array $jsonb_col
 * @property array $json_col_def
 * @property array $json_col_def_2
 * @property resource $bytea_def
 * @property string $text_def
 * @property array $json_def
 * @property array $jsonb_def
 * @property string $cidr_col
 * @property string $circle_col
 * @property string $date_col_z
 * @property double $float8_col
 * @property string $inet_col
 * @property string $interval_col
 * @property string $interval_col_2
 * @property string $interval_col_3
 * @property string $line_col
 * @property string $lseg_col
 * @property string $macaddr_col
 * @property string $money_col
 * @property string $path_col
 * @property integer $pg_lsn_col
 * @property string $point_col
 * @property string $polygon_col
 * @property integer $serial_col
 * @property integer $serial4_col
 * @property string $tsquery_col
 * @property string $tsvector_col
 * @property string $txid_snapshot_col
 * @property string $uuid_col
 * @property string $xml_col
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
            'trim' => [['string_col', 'varchar_col', 'text_col', 'varchar_4_col', 'varchar_5_col', 'char_4_col', 'char_5_col', 'char_6_col', 'char_7_col', 'char_8_col', 'date_col', 'time_col', 'time_col_2', 'time_col_3', 'time_col_4', 'timetz_col', 'timetz_col_2', 'timestamp_col', 'timestamp_col_2', 'timestamp_col_3', 'timestamp_col_4', 'timestamptz_col', 'timestamptz_col_2', 'date2', 'timestamp_col_z', 'box_col', 'character_col', 'character_n', 'character_varying', 'character_varying_n', 'text_def', 'cidr_col', 'circle_col', 'date_col_z', 'inet_col', 'interval_col', 'interval_col_2', 'interval_col_3', 'line_col', 'lseg_col', 'macaddr_col', 'money_col', 'path_col', 'point_col', 'polygon_col', 'tsquery_col', 'tsvector_col', 'txid_snapshot_col', 'uuid_col', 'xml_col'], 'trim'],
            'required' => [['char_6_col'], 'required'],
            'string_col_string' => [['string_col'], 'string'],
            'varchar_col_string' => [['varchar_col'], 'string'],
            'text_col_string' => [['text_col'], 'string'],
            'varchar_4_col_string' => [['varchar_4_col'], 'string', 'max' => 4],
            'varchar_5_col_string' => [['varchar_5_col'], 'string', 'max' => 5],
            'char_4_col_string' => [['char_4_col'], 'string', 'max' => 4],
            'char_5_col_string' => [['char_5_col'], 'string'],
            'char_6_col_string' => [['char_6_col'], 'string'],
            'char_7_col_string' => [['char_7_col'], 'string', 'max' => 6],
            'char_8_col_string' => [['char_8_col'], 'string'],
            'char_8_col_default' => [['char_8_col'], 'default', 'value' => 'd'],
            'decimal_col_double' => [['decimal_col'], 'double'],
            'bit_col_integer' => [['bit_col'], 'integer'],
            'bit_2_integer' => [['bit_2'], 'integer'],
            'bit_3_integer' => [['bit_3'], 'integer'],
            'ti_integer' => [['ti'], 'integer'],
            'int2_col_integer' => [['int2_col'], 'integer'],
            'smallserial_col_integer' => [['smallserial_col'], 'integer'],
            'serial2_col_integer' => [['serial2_col'], 'integer'],
            'si_col_integer' => [['si_col'], 'integer'],
            'si_col_2_integer' => [['si_col_2'], 'integer'],
            'bi_integer' => [['bi'], 'integer'],
            'bi2_integer' => [['bi2'], 'integer'],
            'int4_col_integer' => [['int4_col'], 'integer'],
            'bigserial_col_integer' => [['bigserial_col'], 'integer'],
            'bigserial_col_2_integer' => [['bigserial_col_2'], 'integer'],
            'int_col_integer' => [['int_col'], 'integer'],
            'int_col_2_integer' => [['int_col_2'], 'integer'],
            'numeric_col_double' => [['numeric_col'], 'double'],
            'numeric_col_2_double' => [['numeric_col_2'], 'double'],
            'numeric_col_3_double' => [['numeric_col_3'], 'double'],
            'double_p_2_double' => [['double_p_2'], 'double'],
            'double_p_3_double' => [['double_p_3'], 'double'],
            'real_col_double' => [['real_col'], 'double'],
            'float4_col_double' => [['float4_col'], 'double'],
            'date_col_date' => [['date_col'], 'date', 'format' => 'php:Y-m-d'],
            'time_col_time' => [['time_col'], 'time', 'format' => 'php:H:i:s'],
            'time_col_2_string' => [['time_col_2'], 'string'],
            'time_col_3_string' => [['time_col_3'], 'string'],
            'time_col_4_string' => [['time_col_4'], 'string', 'max' => 3],
            'timetz_col_string' => [['timetz_col'], 'string'],
            'timetz_col_2_string' => [['timetz_col_2'], 'string', 'max' => 3],
            'timestamp_col_string' => [['timestamp_col'], 'string'],
            'timestamp_col_2_string' => [['timestamp_col_2'], 'string'],
            'timestamp_col_3_string' => [['timestamp_col_3'], 'string'],
            'timestamp_col_4_string' => [['timestamp_col_4'], 'string', 'max' => 3],
            'timestamptz_col_string' => [['timestamptz_col'], 'string'],
            'timestamptz_col_2_string' => [['timestamptz_col_2'], 'string', 'max' => 3],
            'date2_date' => [['date2'], 'date', 'format' => 'php:Y-m-d'],
            'timestamp_col_z_string' => [['timestamp_col_z'], 'string'],
            'bit_varying_integer' => [['bit_varying'], 'integer'],
            'bit_varying_n_integer' => [['bit_varying_n'], 'integer'],
            'bit_varying_n_2_integer' => [['bit_varying_n_2'], 'integer'],
            'bit_varying_n_3_integer' => [['bit_varying_n_3'], 'integer'],
            'bool_col_boolean' => [['bool_col'], 'boolean'],
            'bool_col_2_boolean' => [['bool_col_2'], 'boolean'],
            'box_col_string' => [['box_col'], 'string'],
            'character_col_string' => [['character_col'], 'string'],
            'character_n_string' => [['character_n'], 'string', 'max' => 12],
            'character_varying_string' => [['character_varying'], 'string'],
            'character_varying_n_string' => [['character_varying_n'], 'string', 'max' => 12],
            'json_col_def_default' => [['json_col_def'], 'default', 'value' => []],
            'json_col_def_2_default' => [['json_col_def_2'], 'default', 'value' => []],
            'bytea_def_default' => [['bytea_def'], 'default', 'value' => 'the bytea blob default'],
            'text_def_string' => [['text_def'], 'string'],
            'text_def_default' => [['text_def'], 'default', 'value' => 'the text'],
            'json_def_default' => [['json_def'], 'default', 'value' => [
                'a' => 'b',
            ]],
            'jsonb_def_default' => [['jsonb_def'], 'default', 'value' => [
                'ba' => 'bb',
            ]],
            'cidr_col_string' => [['cidr_col'], 'string'],
            'circle_col_string' => [['circle_col'], 'string'],
            'date_col_z_date' => [['date_col_z'], 'date', 'format' => 'php:Y-m-d'],
            'float8_col_double' => [['float8_col'], 'double'],
            'inet_col_string' => [['inet_col'], 'string'],
            'interval_col_string' => [['interval_col'], 'string'],
            'interval_col_2_string' => [['interval_col_2'], 'string'],
            'interval_col_3_string' => [['interval_col_3'], 'string', 'max' => 3],
            'line_col_string' => [['line_col'], 'string'],
            'lseg_col_string' => [['lseg_col'], 'string'],
            'macaddr_col_string' => [['macaddr_col'], 'string'],
            'money_col_string' => [['money_col'], 'string'],
            'path_col_string' => [['path_col'], 'string'],
            'pg_lsn_col_integer' => [['pg_lsn_col'], 'integer'],
            'point_col_string' => [['point_col'], 'string'],
            'polygon_col_string' => [['polygon_col'], 'string'],
            'serial_col_integer' => [['serial_col'], 'integer'],
            'serial4_col_integer' => [['serial4_col'], 'integer'],
            'tsquery_col_string' => [['tsquery_col'], 'string'],
            'tsvector_col_string' => [['tsvector_col'], 'string'],
            'txid_snapshot_col_string' => [['txid_snapshot_col'], 'string'],
            'uuid_col_string' => [['uuid_col'], 'string'],
            'xml_col_string' => [['xml_col'], 'string'],
            'safe' => [['text_col_array', 'bytea_col_2', 'json_col', 'jsonb_col', 'json_col_def', 'json_col_def_2', 'bytea_def', 'json_def', 'jsonb_def'], 'safe'],
        ];
    }
}
