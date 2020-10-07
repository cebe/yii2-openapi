<?php
namespace app\controllers\base;

use insolita\fractal\JsonApiController;
use Yii;

abstract class PostController extends JsonApiController
{
    public function actions()
    {
        return [
            'list-for-category' => [
                'class' => \insolita\fractal\actions\ListAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\PostTransformer::class,
                'modelClass' => \app\models\Post::class,
                'resourceKey' => 'posts',
                'parentIdParam' => 'categoryId',
                'parentIdAttribute' => 'category_id',
                'dataFilter' => null,
                'prepareDataProvider' => null
            ],
            'create-for-category' => [
                'class' => \insolita\fractal\actions\CreateAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\PostTransformer::class,
                'modelClass' => \app\models\Post::class,
                'resourceKey' => 'posts',
                'parentIdParam' => 'categoryId',
                'parentIdAttribute' => 'category_id',
                'findModelFor' => null,
                'scenario' => 'default',
                'viewRoute' => 'view'
            ],
            'list' => [
                'class' => \insolita\fractal\actions\ListAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\PostTransformer::class,
                'modelClass' => \app\models\Post::class,
                'resourceKey' => 'posts',
                'dataFilter' => null,
                'prepareDataProvider' => null
            ],
            'create' => [
                'class' => \insolita\fractal\actions\CreateAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\PostTransformer::class,
                'modelClass' => \app\models\Post::class,
                'resourceKey' => 'posts',
                'allowedRelations'=>[],
                'viewRoute' => 'view',
                'scenario' => 'default'
            ],
            'view' => [
                'class' => \insolita\fractal\actions\ViewAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\PostTransformer::class,
                'modelClass' => \app\models\Post::class,
                'resourceKey' => 'posts',
                'findModel' => null
            ],
            'delete' => [
                'class' => \insolita\fractal\actions\DeleteAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'modelClass' => \app\models\Post::class,
                'findModel' => null
          ],
            'update' => [
                'class' => \insolita\fractal\actions\UpdateAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\PostTransformer::class,
                'modelClass' => \app\models\Post::class,
                'resourceKey' => 'posts',
                'findModel' => null,
                'allowedRelations'=>[],
                'scenario' => 'default'
            ],
            'view-related-author' => [
                'class' => \insolita\fractal\actions\ViewRelationshipAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\UserTransformer::class,
                'modelClass' => \app\models\Post::class,
                'resourceKey' => 'users',
                'relationName' => 'author'
            ],
            'list-related-comments' => [
                'class' => \insolita\fractal\actions\ViewRelationshipAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\CommentTransformer::class,
                'modelClass' => \app\models\Post::class,
                'resourceKey' => 'comments',
                'relationName' => 'comments'
            ],
            'list-related-tags' => [
                'class' => \insolita\fractal\actions\ViewRelationshipAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\TagTransformer::class,
                'modelClass' => \app\models\Post::class,
                'resourceKey' => 'tags',
                'relationName' => 'tags'
            ],
            'update-related-tags' => [
                'class' => \insolita\fractal\actions\UpdateRelationshipAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'modelClass' => \app\models\Post::class,
                'pkType' => 'integer',
                'unlinkOnly' => true,
                'relationName' => 'tags'
            ],
            'options' => [
                'class' => \yii\rest\OptionsAction::class,
            ],
        ];
    }

    /**
     * Checks the privilege of the current user.
     *
     * This method checks whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws \yii\web\ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        // TODO implement checkAccess
    }

    public function actionUpdateUploadCover($id)
    {
        // TODO implement actionUpdateUploadCover
    }
}

