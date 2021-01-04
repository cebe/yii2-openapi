<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Photos2Posts;

class Photos2PostsTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['photo', 'post'];
    protected $defaultIncludes = [];

    public function transform(Photos2Posts $model)
    {
        return $model->getAttributes();
    }

    public function includePhoto(Photos2Posts $model)
    {
        $relation = $model->photo;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new PhotoTransformer();
        return $this->item($relation, $transformer, 'photos');
    }

    public function includePost(Photos2Posts $model)
    {
        $relation = $model->post;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new PostTransformer();
        return $this->item($relation, $transformer, 'posts');
    }
}
