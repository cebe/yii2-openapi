<?php

namespace app\models\base;

/**
 * Table with edit columns for migration code generation
 *
 * @property int $id
 * @property string $name
 * @property string $tag
 * @property string $first_name
 * @property string $string_col
 * @property double $dec_col
 * @property string $str_col_def
 * @property string $json_col
 * @property array $json_col_2
 * @property double $numeric_col
 * @property array $json_col_def_n
 * @property array $json_col_def_n_2
 *
 */
abstract class Editcolumn extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%editcolumns}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name', 'tag', 'first_name', 'string_col', 'str_col_def', 'json_col'], 'trim'],
            'name_string' => [['name'], 'string', 'max' => 254],
            'tag_string' => [['tag'], 'string'],
            'first_name_string' => [['first_name'], 'string', 'max' => 255],
            'string_col_string' => [['string_col'], 'string'],
            'dec_col_double' => [['dec_col'], 'double'],
            'str_col_def_string' => [['str_col_def'], 'string', 'max' => 3],
            'json_col_string' => [['json_col'], 'string'],
            'numeric_col_double' => [['numeric_col'], 'double'],
            'safe' => [['json_col_2', 'json_col_def_n', 'json_col_def_n_2'], 'safe'],
        ];
    }
}
