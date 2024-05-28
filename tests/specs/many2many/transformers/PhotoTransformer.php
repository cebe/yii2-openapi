<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Photo;

class PhotoTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['postsAttaches', 'postsGallery', 'photosPosts', 'targets', 'articles', 'posts'];
    protected array $defaultIncludes = [];

    public function transform(Photo $model)
    {
        return $model->getAttributes();
    }

    public function includePostsAttaches(Photo $model)
    {
        $relation = $model->postsAttaches;
        $transformer = new PostsAttachTransformer();
        return $this->collection($relation, $transformer, 'posts-attaches');
    }

    public function includePostsGallery(Photo $model)
    {
        $relation = $model->postsGallery;
        $transformer = new PostsGalleryTransformer();
        return $this->collection($relation, $transformer, 'posts-galleries');
    }

    public function includePhotosPosts(Photo $model)
    {
        $relation = $model->photosPosts;
        $transformer = new Photos2PostTransformer();
        return $this->collection($relation, $transformer, 'photos2-posts');
    }

    public function includeTargets(Photo $model)
    {
        $relation = $model->targets;
        $transformer = new PostTransformer();
        return $this->collection($relation, $transformer, 'posts');
    }

    public function includeArticles(Photo $model)
    {
        $relation = $model->articles;
        $transformer = new PostTransformer();
        return $this->collection($relation, $transformer, 'posts');
    }

    public function includePosts(Photo $model)
    {
        $relation = $model->posts;
        $transformer = new PostTransformer();
        return $this->collection($relation, $transformer, 'posts');
    }
}
