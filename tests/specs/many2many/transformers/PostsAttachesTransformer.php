<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\PostsAttaches;

class PostsAttachesTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['attach', 'target'];
    protected array $defaultIncludes = [];

    public function transform(PostsAttaches $model)
    {
        return $model->getAttributes();
    }

    public function includeAttach(PostsAttaches $model)
    {
        $relation = $model->attach;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new PhotoTransformer();
        return $this->item($relation, $transformer, 'photos');
    }

    public function includeTarget(PostsAttaches $model)
    {
        $relation = $model->target;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new PostTransformer();
        return $this->item($relation, $transformer, 'posts');
    }
}
