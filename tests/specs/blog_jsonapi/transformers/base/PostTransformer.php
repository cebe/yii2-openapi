<?php
namespace app\transformers\base;

use League\Fractal\TransformerAbstract;
use app\models\Post;
use app\transformers\CategoryTransformer;
use app\transformers\UserTransformer;
use app\transformers\CommentTransformer;
use app\transformers\PostTagTransformer;

class PostTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['category', 'author', 'comments', 'postTags'];
    protected array $defaultIncludes = [];

    public function transform(Post $model)
    {
        return $model->getAttributes();
    }

    public function includeCategory(Post $model)
    {
        $relation = $model->category;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new CategoryTransformer();
        return $this->item($relation, $transformer, 'categories');
    }

    public function includeAuthor(Post $model)
    {
        $relation = $model->author;
        if ($relation === null) {
            return $this->null();
        }
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
