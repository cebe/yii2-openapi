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
 * @property string $uuid
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
            [['uuid', 'str_text', 'str_varchar', 'str_date', 'str_datetime', 'str_country'], 'trim'],
            [['active'], 'boolean'],
            [['floatval'], 'double'],
            [['floatval_lim'], 'double', 'min' => 0, 'max' => 1],
            [['doubleval'], 'double'],
            [['int_min'], 'integer', 'min' => 5],
            [['int_max'], 'integer', 'max' => 5],
            [['int_minmax'], 'integer', 'min' => 5, 'max' => 25],
            [['int_created_at'], 'integer'],
            [['int_simple'], 'integer'],
            [['uuid'], 'string'],
            [['str_text'], 'string'],
            [['str_varchar'], 'string', 'max' => 100],
            [['str_date'], 'date'],
            [['str_datetime'], 'datetime'],
            [['str_country'], 'string'],
        ];
    }

}
