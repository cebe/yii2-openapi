<?php

namespace app\models\base;

/**
 * Category of posts
 *
 * @property int $id
 * @property string $title
 * @property bool $active
 *
 * @property array|\app\models\Post[] $posts
 */
abstract class Category extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%categories}}';
    }

    public function rules()
    {
        return [
            'trim' => [['title'], 'trim'],
            'required' => [['title'], 'required'],
            'title_unique' => [['title'], 'unique'],
            'title_string' => [['title'], 'string', 'max' => 255],
            'active_boolean' => [['active'], 'boolean'],
            'active_default' => [['active'], 'default', 'value' => false],
        ];
    }

    public function getPosts()
    {
        return $this->hasMany(\app\models\Post::class, ['category_id' => 'id']);
    }
}
