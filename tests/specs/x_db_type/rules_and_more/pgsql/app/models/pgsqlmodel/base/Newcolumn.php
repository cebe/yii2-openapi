<?php

namespace app\models\pgsqlmodel\base;

/**
 * New Fresh table with new columns for migration code generation
 *
 * @property int $id
 * @property string $name
 * @property string $first_name
 * @property string $last_name
 * @property double $dec_col
 * @property array $json_col
 * @property string $varchar_col
 * @property double $numeric_col
 * @property array $json_col_def_n
 * @property array $json_col_def_n_2
 * @property array $text_col_array
 *
 */
abstract class Newcolumn extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%newcolumns}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name', 'first_name', 'last_name', 'varchar_col'], 'trim'],
            'required' => [['name'], 'required'],
            'name_string' => [['name'], 'string'],
            'first_name_string' => [['first_name'], 'string'],
            'last_name_string' => [['last_name'], 'string'],
            'dec_col_double' => [['dec_col'], 'double'],
            'varchar_col_string' => [['varchar_col'], 'string'],
            'numeric_col_double' => [['numeric_col'], 'double'],
            'json_col_def_n_default' => [['json_col_def_n'], 'default', 'value' => []],
            'json_col_def_n_2_default' => [['json_col_def_n_2'], 'default', 'value' => []],
            'safe' => [['json_col', 'json_col_def_n', 'json_col_def_n_2', 'text_col_array'], 'safe'],
        ];
    }
}
