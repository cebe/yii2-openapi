<?php

namespace app\models\base;

/**
 * A blog post
 *
 * @property int $id
 * @property string $title
 *
 * @property array|\app\models\PostsAttaches[] $postsAttaches
 * @property array|\app\models\PostsGallery[] $postsGallery
 * @property array|\app\models\Photos2Posts[] $postsPhotos
 * @property array|\app\models\Tag[] $tags
 * @property array|\app\models\Photo[] $attaches
 * @property array|\app\models\Photo[] $images
 * @property array|\app\models\Photo[] $photos
 */
abstract class Post extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%posts}}';
    }

    public function rules()
    {
        return [
            'trim' => [['title'], 'trim'],
            'required' => [['title'], 'required'],
            'title_string' => [['title'], 'string'],
        ];
    }

    public function getPostsAttaches()
    {
        return $this->hasMany(\app\models\PostsAttaches::class, ['post_id' => 'id']);
    }

    public function getPostsGallery()
    {
        return $this->hasMany(\app\models\PostsGallery::class, ['post_id' => 'id']);
    }

    public function getPostsPhotos()
    {
        return $this->hasMany(\app\models\Photos2Posts::class, ['post_id' => 'id']);
    }

    public function getTags()
    {
        return $this->hasMany(\app\models\Tag::class, ['id' => 'tag_id'])
                    ->viaTable('posts2tags', ['post_id' => 'id']);
    }

    public function getAttaches()
    {
        return $this->hasMany(\app\models\Photo::class, ['id' => 'photo_id'])
                    ->via('postsAttaches');
    }

    public function getImages()
    {
        return $this->hasMany(\app\models\Photo::class, ['id' => 'photo_id'])
                    ->via('postsGallery');
    }

    public function getPhotos()
    {
        return $this->hasMany(\app\models\Photo::class, ['id' => 'photo_id'])
                    ->via('postsPhotos');
    }
}
