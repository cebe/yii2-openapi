<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property string $filename
 *
 * @property array|\app\models\PostsAttaches[] $postsAttaches
 * @property array|\app\models\PostsGallery[] $postsGallery
 * @property array|\app\models\Photos2Posts[] $photosPosts
 * @property array|\app\models\Post[] $targets
 * @property array|\app\models\Post[] $articles
 * @property array|\app\models\Post[] $posts
 */
abstract class Photo extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%photo}}';
    }

    public function rules()
    {
        return [
            'trim' => [['filename'], 'trim'],
            'required' => [['filename'], 'required'],
            'filename_string' => [['filename'], 'string'],
        ];
    }

    public function getPostsAttaches()
    {
        return $this->hasMany(\app\models\PostsAttaches::class, ['photo_id' => 'id']);
    }

    public function getPostsGallery()
    {
        return $this->hasMany(\app\models\PostsGallery::class, ['photo_id' => 'id']);
    }

    public function getPhotosPosts()
    {
        return $this->hasMany(\app\models\Photos2Posts::class, ['photo_id' => 'id']);
    }

    public function getTargets()
    {
        return $this->hasMany(\app\models\Post::class, ['id' => 'post_id'])
                    ->via('postsAttaches');
    }

    public function getArticles()
    {
        return $this->hasMany(\app\models\Post::class, ['id' => 'post_id'])
                    ->via('postsGallery');
    }

    public function getPosts()
    {
        return $this->hasMany(\app\models\Post::class, ['id' => 'post_id'])
                    ->via('photosPosts');
    }
}
