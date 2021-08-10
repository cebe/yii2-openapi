<?php
/**@var \cebe\yii2openapi\lib\items\FractalAction $action */
?>
        $model = $this-><?=$action->findModelMethodName?>($<?=$action->idParam?>);
        $model->scenario = 'default';
        $this->checkAccess($action, $model);
        $model->load(Yii::$app->getRequest()->getBodyParams()['data']['attributes'] ?? [], '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new \yii\web\ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
        if ($model->hasErrors()) {
            throw new \insolita\fractal\exceptions\ValidationException($model->getErrors());
        }
<?php if ($action->transformerFqn):?>
        $transformer = Yii::createObject(['class'=>\<?=$action->transformerFqn?>::class]);
<?php else:?>
        $transformer = Yii::createObject(['class'=>\insolita\fractal\DefaultTransformer::class]);
<?php endif;?>
        return new \League\Fractal\Resource\Item($model, $transformer, '<?=$action->getResourceKey()?>');
