<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Comment;

class CommentTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['post', 'user'];
    protected $defaultIncludes = [];

    public function transform(Comment $model)
    {
        return $model->getAttributes();
    }

    public function includePost(Comment $model)
    {
        $relation = $model->post;
        $transformer = new PostTransformer();
        return $this->item($relation, $transformer, 'posts');
    }

    public function includeUser(Comment $model)
    {
        $relation = $model->user;
        $transformer = new UserTransformer();
        return $this->item($relation, $transformer, 'users');
    }
}
