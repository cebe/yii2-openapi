<?php
namespace app\transformers\base;

use League\Fractal\TransformerAbstract;
use app\models\Category;
use app\transformers\PostTransformer;

class CategoryTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['posts'];
    protected array $defaultIncludes = [];

    public function transform(Category $model)
    {
        return $model->getAttributes();
    }

    public function includePosts(Category $model)
    {
        $relation = $model->posts;
        $transformer = new PostTransformer();
        return $this->collection($relation, $transformer, 'posts');
    }
}
