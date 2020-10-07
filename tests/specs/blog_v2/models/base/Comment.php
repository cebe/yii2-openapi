<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property int $post_id A blog post (uid used as pk for test purposes)
 * @property int $user_id The User
 * @property string $message
 * @property string $created_at
 *
 * @property \app\models\Post $post
 * @property \app\models\User $user
 */
abstract class Comment extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%v2_comments}}';
    }

    public function rules()
    {
        return [
            'trim' => [['message', 'created_at'], 'trim'],
            'required' => [['post_id', 'message', 'created_at'], 'required'],
            'post_id_integer' => [['post_id'], 'integer'],
            'post_id_exist' => [['post_id'], 'exist', 'targetRelation' => 'Post'],
            'user_id_integer' => [['user_id'], 'integer'],
            'user_id_exist' => [['user_id'], 'exist', 'targetRelation' => 'User'],
            'message_string' => [['message'], 'string'],
            'created_at_datetime' => [['created_at'], 'datetime'],
        ];
    }

    public function getPost()
    {
        return $this->hasOne(\app\models\Post::class, ['id' => 'post_id']);
    }

    public function getUser()
    {
        return $this->hasOne(\app\models\User::class, ['id' => 'user_id']);
    }
}
