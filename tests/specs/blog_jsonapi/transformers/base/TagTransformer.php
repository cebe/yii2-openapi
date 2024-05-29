<?php
namespace app\transformers\base;

use League\Fractal\TransformerAbstract;
use app\models\Tag;
use app\transformers\PostTagTransformer;

class TagTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['postTags'];
    protected array $defaultIncludes = [];

    public function transform(Tag $model)
    {
        return $model->getAttributes();
    }

    public function includePostTags(Tag $model)
    {
        $relation = $model->postTags;
        $transformer = new PostTagTransformer();
        return $this->collection($relation, $transformer, 'post-tags');
    }
}
