<?php
namespace app\transformers;

use League\Fractal\TransformerAbstract;
use app\models\Post;

class PostTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['category', 'author', 'comments', 'post_tags'];
    protected $defaultIncludes = [];

    public function transform(Post $model)
    {
        return $model->getAttributes();
    }

    public function includeCategory(Post $model)
    {
        $relation = $model->category;
        $transformer = new CategoryTransformer();
        return $this->item($relation, $transformer, 'categories');
    }

    public function includeAuthor(Post $model)
    {
        $relation = $model->author;
        $transformer = new UserTransformer();
        return $this->item($relation, $transformer, 'users');
    }

    public function includeComments(Post $model)
    {
        $relation = $model->comments;
        $transformer = new CommentTransformer();
        return $this->collection($relation, $transformer, 'comments');
    }

    public function includePostTags(Post $model)
    {
        $relation = $model->postTags;
        $transformer = new PostTagTransformer();
        return $this->collection($relation, $transformer, 'post-tags');
    }
}
