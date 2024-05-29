<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\PostsGallery;

class PostsGalleryTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['image', 'article'];
    protected array $defaultIncludes = [];

    public function transform(PostsGallery $model)
    {
        return $model->getAttributes();
    }

    public function includeImage(PostsGallery $model)
    {
        $relation = $model->image;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new PhotoTransformer();
        return $this->item($relation, $transformer, 'photos');
    }

    public function includeArticle(PostsGallery $model)
    {
        $relation = $model->article;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new PostTransformer();
        return $this->item($relation, $transformer, 'posts');
    }
}
