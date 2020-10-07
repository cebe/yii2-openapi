<?php
/**@var \cebe\yii2openapi\lib\items\FractalAction $action */

?>
        $model = $this-><?=$action->findModelMethodName?>($<?=$action->idParam?>);
        $this->checkAccess('<?=$action->id?>', $model);
<?php if ($action->transformerFqn):?>
        $transformer = Yii::createObject(['class'=>\<?=$action->transformerFqn?>::class]);
<?php else:?>
        $transformer = Yii::createObject(['class'=>\insolita\fractal\DefaultTransformer::class]);
<?php endif;?>
        return new \League\Fractal\Resource\Item(\$model, $transformer, '<?=$action->getResourceKey()?>');
