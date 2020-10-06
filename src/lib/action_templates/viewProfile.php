<?php
/**@var \cebe\yii2openapi\lib\items\FractalAction $action */
?>
        if(Yii::$app->user->isGuest){
            throw new \yii\web\NotFoundHttpException();
        }
        $user  = Yii::$app->user->getIdentity();
<?php if ($action->transformerFqn):?>
        $transformer = Yii::createObject(['class'=>\<?=$action->transformerFqn?>::class]);
<?php else:?>
        $transformer = Yii::createObject(['class'=>\insolita\fractal\DefaultTransformer::class]);
<?php endif;?>
        return new \League\Fractal\Resource\Item($user, $transformer, 'me');
