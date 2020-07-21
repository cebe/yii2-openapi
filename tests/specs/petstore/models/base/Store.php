<?php

namespace app\models\base;

/**
 * A store's description
 *
 * @property int $id
 * @property string $name
 *
 */
abstract class Store extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%stores}}';
    }

    public function rules()
    {
        return [
            [['name'], 'trim'],
            [['name'], 'required'],
            [['name'], 'string'],
        ];
    }

}
