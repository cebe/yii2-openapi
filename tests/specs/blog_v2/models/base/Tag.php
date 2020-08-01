<?php

namespace app\models\base;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $lang
 *
 * @property array|\app\models\PostTag[] $post_tags
 */
abstract class Tag extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%v2_tags}}';
    }

    public function rules()
    {
        return [
            [['name', 'lang'], 'trim'],
            [['name', 'lang'], 'required'],
            [['name', 'lang'], 'string'],
        ];
    }

    public function getPostTags()
    {
        return $this->hasMany(\app\models\PostTag::class,['tag_id' => 'id']);
    }
}
