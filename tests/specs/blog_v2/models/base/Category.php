<?php

namespace app\models\base;

/**
 * Category of posts
 *
 * @property int $id
 * @property string $title
 * @property string $cover
 * @property bool $active
 *
 * @property array|\app\models\Post[] $posts
 */
abstract class Category extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%v2_categories}}';
    }

    public function rules()
    {
        return [
            [['title', 'cover'], 'trim'],
            [['title', 'cover', 'active'], 'required'],
            [['title'], 'unique'],
            [['title'], 'string', 'max' => 100],
            [['cover'], 'string'],
            [['active'], 'boolean'],
        ];
    }

    public function getPosts()
    {
        return $this->hasMany(\app\models\Post::class,['category_id' => 'id']);
    }
}
