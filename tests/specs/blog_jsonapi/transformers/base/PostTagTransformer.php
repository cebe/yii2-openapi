<?php
namespace app\transformers\base;

use League\Fractal\TransformerAbstract;
use app\models\PostTag;
use app\transformers\PostTransformer;
use app\transformers\TagTransformer;

class PostTagTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['post', 'tag'];
    protected array $defaultIncludes = [];

    public function transform(PostTag $model)
    {
        return $model->getAttributes();
    }

    public function includePost(PostTag $model)
    {
        $relation = $model->post;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new PostTransformer();
        return $this->item($relation, $transformer, 'posts');
    }

    public function includeTag(PostTag $model)
    {
        $relation = $model->tag;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new TagTransformer();
        return $this->item($relation, $transformer, 'tags');
    }
}
