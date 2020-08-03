<?php

namespace app\models\base;

/**
 * 
 *
 * @property int $id
 * @property int $post_id A blog post (uid used as pk for test purposes)
 * @property int $author_id The User
 * @property array $message
 * @property int $created_at
 *
 * @property \app\models\Post $post
 * @property \app\models\User $author
 */
abstract class Comment extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%post_comments}}';
    }

    public function rules()
    {
        return [
            [['post_id', 'author_id', 'message', 'created_at'], 'required'],
            [['post_id', 'author_id', 'created_at'], 'integer'],
            [['post_id'], 'exist', 'targetRelation' => 'Post'],
            [['author_id'], 'exist', 'targetRelation' => 'Author'],
            [['message'], 'safe'],
        ];
    }

    public function getPost()
    {
        return $this->hasOne(\app\models\Post::class,['uid' => 'post_id']);
    }
    public function getAuthor()
    {
        return $this->hasOne(\app\models\User::class,['id' => 'author_id']);
    }
}
