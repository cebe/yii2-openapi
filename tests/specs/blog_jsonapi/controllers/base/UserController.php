<?php
namespace app\controllers\base;

use insolita\fractal\JsonApiController;
use Yii;

abstract class UserController extends JsonApiController
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

    public function actionView($username)
    {
        $model = $this->findUserModel($username);
        $this->checkAccess('view', $model);
        $transformer = Yii::createObject(['class'=>\app\transformers\UserTransformer::class]);
        return new \League\Fractal\Resource\Item($model, $transformer, 'users');
    }
    /**
     * Returns the User model based on the given attribute.
     * If the data model is not found, a 404 HTTP exception will be raised.
     * @param string $username
     * @return \app\models\User the model found
     * @throws \yii\web\NotFoundHttpException if the model cannot be found.
     */
    public function findUserModel(string $username)
    {
        $model = \app\models\User::findOne(['username' => $username]);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException("Object not found: $username");
        }
        return $model;
    }
}
