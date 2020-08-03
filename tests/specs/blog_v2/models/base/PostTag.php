<?php

namespace app\models\base;

/**
 * 
 *
 * @property int $id
 * @property int $post_id A blog post (uid used as pk for test purposes)
 * @property int $tag_id
 *
 * @property \app\models\Post $post
 * @property \app\models\Tag $tag
 */
abstract class PostTag extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%v2_post_tag}}';
    }

    public function rules()
    {
        return [
            [['post_id', 'tag_id'], 'required'],
            [['post_id', 'tag_id'], 'integer'],
            [['post_id'], 'exist', 'targetRelation' => 'Post'],
            [['tag_id'], 'exist', 'targetRelation' => 'Tag'],
        ];
    }

    public function getPost()
    {
        return $this->hasOne(\app\models\Post::class,['id' => 'post_id']);
    }
    public function getTag()
    {
        return $this->hasOne(\app\models\Tag::class,['id' => 'tag_id']);
    }
}
