<?php

namespace app\api\v1\controllers\base;

abstract class PetController extends \yii\rest\Controller
{
    public function actions()
    {
        return [
            'list' => [
                'class' => \yii\rest\IndexAction::class,
                'modelClass' => \app\models\Pet::class,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'create' => [
                'class' => \yii\rest\CreateAction::class,
                'modelClass' => \app\models\Pet::class,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => \yii\rest\ViewAction::class,
                'modelClass' => \app\models\Pet::class,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'delete' => [
                'class' => \yii\rest\DeleteAction::class,
                'modelClass' => \app\models\Pet::class,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'update' => [
                'class' => \yii\rest\UpdateAction::class,
                'modelClass' => \app\models\Pet::class,
                'checkAccess' => [$this, 'checkAccess'],
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
    abstract public function checkAccess($action, $model = null, $params = []);

}
