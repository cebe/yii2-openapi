<?php
namespace app\controllers\base;

use insolita\fractal\JsonApiController;
use Yii;

abstract class PetController extends JsonApiController
{
    public function actions()
    {
        return [
            'list' => [
                'class' => \insolita\fractal\actions\ListAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\PetTransformer::class,
                'modelClass' => \app\models\Pet::class,
                'resourceKey' => 'pets',
                'dataFilter' => null,
                'prepareDataProvider' => null
            ],
            'create' => [
                'class' => \insolita\fractal\actions\CreateAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\PetTransformer::class,
                'modelClass' => \app\models\Pet::class,
                'resourceKey' => 'pets',
                'allowedRelations'=>[],
                'viewRoute' => 'view',
                'scenario' => 'default'
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

    public function actionView($petId)
    {
        $model = $this->findPetModel($petId);
        $this->checkAccess('view', $model);
        $transformer = Yii::createObject(['class'=>\app\transformers\PetTransformer::class]);
        return new \League\Fractal\Resource\Item($model, $transformer, 'pets');
    }

    public function actionDelete($petId)
    {
        $model = $this->findPetModel($petId);
        $this->checkAccess('delete', $model);
        if ($model->delete() === false) {
            throw new \yii\web\ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }
        Yii::$app->getResponse()->setStatusCode(204);
    }

    public function actionUpdate($petId)
    {
        $model = $this->findPetModel($petId);
        $model->scenario = 'default';
        $this->checkAccess('update', $model);
        $model->load(Yii::$app->getRequest()->getBodyParams()['data']['attributes'] ?? [], '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new \yii\web\ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
        if ($model->hasErrors()) {
            throw new \insolita\fractal\exceptions\ValidationException($model->getErrors());
        }
        $transformer = Yii::createObject(['class'=>\app\transformers\PetTransformer::class]);
        return new \League\Fractal\Resource\Item($model, $transformer, 'pets');
    }
    /**
     * Returns the Pet model based on the given attribute.
     * If the data model is not found, a 404 HTTP exception will be raised.
     * @param string $petId
     * @return \app\models\Pet the model found
     * @throws \yii\web\NotFoundHttpException if the model cannot be found.
     */
    public function findPetModel(string $petId)
    {
        $model = \app\models\Pet::findOne(['petId' => $petId]);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException("Object not found: $petId");
        }
        return $model;
    }
}
