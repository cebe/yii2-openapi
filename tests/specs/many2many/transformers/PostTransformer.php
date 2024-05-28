<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Post;

class PostTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['postsAttaches', 'postsGallery', 'postsPhotos', 'tags', 'attaches', 'images', 'photos'];
    protected array $defaultIncludes = [];

    public function transform(Post $model)
    {
        return $model->getAttributes();
    }

    public function includePostsAttaches(Post $model)
    {
        $relation = $model->postsAttaches;
        $transformer = new PostsAttachTransformer();
        return $this->collection($relation, $transformer, 'posts-attaches');
    }

    public function includePostsGallery(Post $model)
    {
        $relation = $model->postsGallery;
        $transformer = new PostsGalleryTransformer();
        return $this->collection($relation, $transformer, 'posts-galleries');
    }

    public function includePostsPhotos(Post $model)
    {
        $relation = $model->postsPhotos;
        $transformer = new Photos2PostTransformer();
        return $this->collection($relation, $transformer, 'photos2-posts');
    }

    public function includeTags(Post $model)
    {
        $relation = $model->tags;
        $transformer = new TagTransformer();
        return $this->collection($relation, $transformer, 'tags');
    }

    public function includeAttaches(Post $model)
    {
        $relation = $model->attaches;
        $transformer = new PhotoTransformer();
        return $this->collection($relation, $transformer, 'photos');
    }

    public function includeImages(Post $model)
    {
        $relation = $model->images;
        $transformer = new PhotoTransformer();
        return $this->collection($relation, $transformer, 'photos');
    }

    public function includePhotos(Post $model)
    {
        $relation = $model->photos;
        $transformer = new PhotoTransformer();
        return $this->collection($relation, $transformer, 'photos');
    }
}
