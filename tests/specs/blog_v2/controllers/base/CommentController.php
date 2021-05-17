<?php

namespace app\controllers\base;

abstract class CommentController extends \yii\rest\Controller
{
    public function actions()
    {
        return [
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
    abstract public function checkAccess($action, $model = null, $params = []);

    abstract public function actionListForPost($postId);

    abstract public function actionCreateForPost($postId);

    abstract public function actionViewForPost($slug, $id);

    abstract public function actionDeleteForPost($slug, $id);

    abstract public function actionUpdateForPost($slug, $id);

}
