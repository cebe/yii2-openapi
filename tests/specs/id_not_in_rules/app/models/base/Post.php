<?php

namespace app\models\base;

/**
 *
 *
 * @property string $uid
 * @property string $title
 *
 */
abstract class Post extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%posts}}';
    }

    public function rules()
    {
        return [
            'trim' => [['title'], 'trim'],
            'title_string' => [['title'], 'string'],
        ];
    }
}
