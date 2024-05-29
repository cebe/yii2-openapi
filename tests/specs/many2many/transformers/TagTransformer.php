<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Tag;

class TagTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['posts'];
    protected array $defaultIncludes = [];

    public function transform(Tag $model)
    {
        return $model->getAttributes();
    }

    public function includePosts(Tag $model)
    {
        $relation = $model->posts;
        $transformer = new PostTransformer();
        return $this->collection($relation, $transformer, 'posts');
    }
}
