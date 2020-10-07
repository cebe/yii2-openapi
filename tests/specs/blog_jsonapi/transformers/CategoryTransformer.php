<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Category;

class CategoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['posts'];
    protected $defaultIncludes = [];

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
