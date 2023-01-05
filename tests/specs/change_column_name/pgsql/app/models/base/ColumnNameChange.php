<?php

namespace app\models\base;

/**
 * Test model for change in column name test for Pgsql
 *
 * @property int $id
 * @property string $name
 * @property string $updated_at_2
 *
 */
abstract class ColumnNameChange extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%column_name_changes}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'required' => [['name'], 'required'],
            'name_string' => [['name'], 'string'],
        ];
    }
}
