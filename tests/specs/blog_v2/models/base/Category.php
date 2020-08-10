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
            'trim' => [['title', 'cover'], 'trim'],
            'required' => [['title', 'cover', 'active'], 'required'],
            'title_unique' => [['title'], 'unique'],
            'title_string' => [['title'], 'string', 'max' => 100],
            'cover_string' => [['cover'], 'string'],
            'active_boolean' => [['active'], 'boolean'],
        ];
    }

    public function getPosts()
    {
        return $this->hasMany(\app\models\Post::class,['category_id' => 'id']);
    }
}
