<?php
namespace app\controllers\base;

use insolita\fractal\JsonApiController;
use Yii;

abstract class PersonWatchController extends JsonApiController
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

    abstract public function actionCreate($personId);
    /**
     * Returns the PersonWatch model based on the given attribute.
     * If the data model is not found, a 404 HTTP exception will be raised.
     * @param string $personId
     * @return \app\models\PersonWatch the model found
     * @throws \yii\web\NotFoundHttpException if the model cannot be found.
     */
    public function findPersonWatchModel(string $personId)
    {
        $model = \app\models\PersonWatch::findOne(['personId' => $personId]);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException("Object not found: $personId");
        }
        return $model;
    }
}
