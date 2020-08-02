<?php

namespace app\models\base;

/**
 * A blog post (uid used as pk for test purposes)
 *
 * @property int $uid
 * @property string $title
 * @property string $slug
 * @property int $category_id Category of posts
 * @property bool $active
 * @property string $created_at
 * @property int $created_by_id The User
 *
 * @property \app\models\Category $category
 * @property \app\models\User $created_by
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
            [['title', 'slug', 'created_at'], 'trim'],
            [['title', 'category_id', 'active'], 'required'],
            [['category_id', 'created_by_id'], 'integer'],
            [['category_id'], 'exist', 'targetRelation'=>'Category'],
            [['created_by_id'], 'exist', 'targetRelation'=>'CreatedBy'],
            [['title', 'slug', 'created_at'], 'string'],
            [['active'], 'boolean'],
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(\app\models\Category::class,['id' => 'category_id']);
    }
    public function getCreatedBy()
    {
        return $this->hasOne(\app\models\User::class,['id' => 'created_by_id']);
    }
    public function getComments()
    {
        return $this->hasMany(\app\models\Comment::class,['post_id' => 'uid']);
    }
}