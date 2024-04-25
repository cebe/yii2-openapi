<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 *
 */
abstract class Contact extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%contacts}}';
    }

    public function rules()
    {
        return [];
    }
}
