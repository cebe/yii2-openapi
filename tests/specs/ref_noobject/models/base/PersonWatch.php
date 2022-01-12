<?php

namespace app\models\base;

/**
 * Information about a user watching a Person
 *
 *
 */
abstract class PersonWatch extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%person_watches}}';
    }

    public function rules()
    {
        return [];
    }
}
