<?php

namespace app\models\base;

/**
 * A blog post (uid used as pk for test purposes)
 *
 * @property string $uid
 * @property string $title
 * @property string $slug
 * @property int $category_id Category of posts
 * @property bool $active
 * @property string $created_at
 * @property int $created_by_id The User
 *
 * @property \app\models\Category $category
 * @property \app\models\User $createdBy
 * @property array|\app\models\Comment[] $comments
 */
abstract class Post extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%blog_posts}}';
    }

    public function rules()
    {
        return [
            'trim' => [['title', 'slug', 'created_at'], 'trim'],
            'required' => [['title', 'category_id', 'active'], 'required'],
            'category_id_integer' => [['category_id'], 'integer'],
            'category_id_exist' => [['category_id'], 'exist', 'targetRelation' => 'Category'],
            'created_by_id_integer' => [['created_by_id'], 'integer'],
            'created_by_id_exist' => [['created_by_id'], 'exist', 'targetRelation' => 'CreatedBy'],
            'title_unique' => [['title'], 'unique'],
            'slug_unique' => [['slug'], 'unique'],
            'title_string' => [['title'], 'string', 'max' => 255],
            'slug_string' => [['slug'], 'string', 'min' => 1, 'max' => 200],
            'active_boolean' => [['active'], 'boolean'],
            'active_default' => [['active'], 'default', 'value' => false],
            'created_at_date' => [['created_at'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(\app\models\Category::class, ['id' => 'category_id']);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(\app\models\User::class, ['id' => 'created_by_id']);
    }

    public function getComments()
    {
        return $this->hasMany(\app\models\Comment::class, ['post_id' => 'uid']);
    }
}
