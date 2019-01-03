<?php

namespace app\models;

/**
 * A Pet
 *
 * @var int $id
 * @var string $name
 * @var string $tag
 */
class Pet extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%pets}}';
    }

}
