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
            [['int_min', 'int_max', 'int_minmax', 'int_created_at', 'int_simple'], 'integer'],
            [['uuid', 'str_text', 'str_varchar', 'str_date', 'str_datetime', 'str_country'], 'string'],
            [['floatval', 'floatval_lim', 'doubleval'], 'double'],
            [['active'], 'boolean'],
        ];
    }

}
