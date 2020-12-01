<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property int $photo_id
 * @property int $post_id
 *
 * @property \app\models\Photo $photo
 * @property \app\models\Post $post
 */
abstract class Photos2Posts extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%photos2posts}}';
    }

    public function rules()
    {
        return [
            'photo_id_integer' => [['photo_id'], 'integer'],
            'photo_id_exist' => [['photo_id'], 'exist', 'targetRelation' => 'Photo'],
            'post_id_integer' => [['post_id'], 'integer'],
            'post_id_exist' => [['post_id'], 'exist', 'targetRelation' => 'Post'],
        ];
    }

    public function getPhoto()
    {
        return $this->hasOne(\app\models\Photo::class, ['id' => 'photo_id']);
    }

    public function getPost()
    {
        return $this->hasOne(\app\models\Post::class, ['id' => 'post_id']);
    }
}
