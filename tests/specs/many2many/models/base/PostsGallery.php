<?php

namespace app\models\base;

/**
 *
 *
 * @property int $image_id
 * @property int $article_id
 * @property bool $is_cover
 *
 * @property \app\models\Photo $image
 * @property \app\models\Post $article
 */
abstract class PostsGallery extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%posts_gallery}}';
    }

    public function rules()
    {
        return [
            'image_id_integer' => [['image_id'], 'integer'],
            'image_id_exist' => [['image_id'], 'exist', 'targetRelation' => 'Image'],
            'article_id_integer' => [['article_id'], 'integer'],
            'article_id_exist' => [['article_id'], 'exist', 'targetRelation' => 'Article'],
            'is_cover_boolean' => [['is_cover'], 'boolean'],
        ];
    }

    public function getImage()
    {
        return $this->hasOne(\app\models\Photo::class, ['id' => 'image_id']);
    }

    public function getArticle()
    {
        return $this->hasOne(\app\models\Post::class, ['id' => 'article_id']);
    }
}
