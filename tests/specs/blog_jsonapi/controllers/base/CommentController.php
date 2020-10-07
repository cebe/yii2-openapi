<?php
namespace app\controllers\base;

use insolita\fractal\JsonApiController;
use Yii;

abstract class CommentController extends JsonApiController
{
    public function actions()
    {
        return [
            'view-for-post' => [
                'class' => \insolita\fractal\actions\ViewAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\CommentTransformer::class,
                'modelClass' => \app\models\Comment::class,
                'resourceKey' => 'comments',
                'parentIdParam' => 'postId',
                'parentIdAttribute' => 'post_id',
                'findModelFor' => null
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
}
