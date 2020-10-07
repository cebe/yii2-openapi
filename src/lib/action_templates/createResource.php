<?php
/**@var \cebe\yii2openapi\lib\items\FractalAction $action */
?>
        $model = new <?=$action->modelFqn?>();
        $model->scenario = 'default';
        $model->load(Yii::$app->getRequest()->getBodyParams()['data']['attributes'] ?? [], '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new \yii\web\ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
        if ($model->hasErrors()) {
            throw new \insolita\fractal\exceptions\ValidationException($model->getErrors());
        }
        $response = Yii::$app->getResponse();
        $response->setStatusCode(201);
        $response->getHeaders()->set('Location', Url::to(['view', 'id' => $<?=$action->idParam?>], true));
<?php if ($action->transformerFqn):?>
        $transformer = Yii::createObject(['class'=>\<?=$action->transformerFqn?>::class]);
<?php else:?>
        $transformer = Yii::createObject(['class'=>\insolita\fractal\DefaultTransformer::class]);
<?php endif;?>
        return new \League\Fractal\Resource\Item(\$model, $transformer, '<?=$action->getResourceKey()?>');
