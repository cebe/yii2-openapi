<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property int $post_id A blog post (uid used as pk for test purposes)
 * @property int $author_id The User
 * @property array $message
 * @property array $meta_data
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
            'required' => [['post_id', 'author_id', 'created_at'], 'required'],
            'post_id_integer' => [['post_id'], 'integer'],
            'post_id_exist' => [['post_id'], 'exist', 'targetRelation' => 'Post'],
            'author_id_integer' => [['author_id'], 'integer'],
            'author_id_exist' => [['author_id'], 'exist', 'targetRelation' => 'Author'],
            'created_at_integer' => [['created_at'], 'integer'],
            'safe' => [['message', 'meta_data'], 'safe'],
        ];
    }

    public function getPost()
    {
        return $this->hasOne(\app\models\Post::class, ['uid' => 'post_id']);
    }

    public function getAuthor()
    {
        return $this->hasOne(\app\models\User::class, ['id' => 'author_id']);
    }
}
