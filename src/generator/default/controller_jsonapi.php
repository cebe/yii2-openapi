<?php
use cebe\yii2openapi\lib\items\FractalAction;

/**
 * @var string                                            $namespace
 * @var string                                            $className
 * @var array|\cebe\yii2openapi\lib\items\FractalAction[] $actions
 **/

$findModels = [];
$findModelsFor = [];
echo '<?php';?>

namespace <?= $namespace ?>;

use insolita\fractal\JsonApiController;
use Yii;

abstract class <?= $className ?> extends JsonApiController
{
    public function actions()
    {
        return [
<?php foreach ($actions as $action):?>
<?php if ($action->shouldUseTemplate()): ?>
            <?=$action->template?>,
<?php endif;?>
<?php endforeach;?>
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
<?php foreach ($actions as $action): ?>
<?php if (!$action->shouldUseTemplate()):?>
<?php if (!$action->shouldBeAbstract()):?>

    public function <?= $action->actionMethodName ?>(<?= $action->parameterList ?>)
    {
<?=$action->getImplementation()?>
    }
<?php else:?>

    abstract public function <?= $action->actionMethodName ?>(<?= $action->parameterList ?>);
<?php endif;?>
<?php endif;?>
<?php endforeach;?>
<?php foreach ($actions as $action): ?>
<?php if ($action->shouldUseCustomFindModel() && !in_array($action->findModelMethodName, $findModels, true)):?>
<?php $findModels[] = $action->findModelMethodName;?>
    /**
     * Returns the <?= $action->baseModelName ?> model based on the primary key given.
     * If the data model is not found, a 404 HTTP exception will be raised.
     * @param string $id the ID of the model to be loaded.
     * @return \<?= $action->modelFqn ?> the model found
     * @throws \yii\web\NotFoundHttpException if the model cannot be found.
     */
    public function <?= $action->findModelMethodName ?>($id)
    {
        $model = \<?= $action->modelFqn ?>::findOne($id);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException("Object not found: $id");
        }
        return $model;
    }
<?php endif;?>
<?php if ($action->shouldUseCustomFindForModel() && !in_array($action->findModelForMethodName, $findModelsFor, true)):?>
<?php $findModelsFor[] = $action->findModelForMethodName;?>

    public function <?= $action->findModelForMethodName ?>($id, $parentId)
    {
        $model = \<?= $action->modelFqn ?>::findOne(['id' => $id, '<?=$action->parentIdAttribute?>' => $parentId]);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException("Object not found: $id");
        }
        return $model;
    }
<?php endif;?>
<?php endforeach;?>
}
