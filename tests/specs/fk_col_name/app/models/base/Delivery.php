<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property string $title
 *
 */
abstract class Delivery extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%deliveries}}';
    }

    public function rules()
    {
        return [
            'trim' => [['title'], 'trim'],
            'title_string' => [['title'], 'string'],
        ];
    }
}
