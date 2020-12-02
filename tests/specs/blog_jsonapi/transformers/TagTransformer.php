<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Tag;

class TagTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['postTags'];
    protected $defaultIncludes = [];

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
