<?php
use cebe\yii2openapi\lib\items\RestAction;

/**
 * @var string                                         $namespace
 * @var string                                         $className
 * @var array|\cebe\yii2openapi\lib\items\RestAction[] $actions
 **/

$serializerConfigs = array_filter(
    array_map(function (RestAction $act) {
        return $act->serializerConfig;
    }, $actions)
);
$findModels = [];
echo '<?php';
?>


namespace <?= $namespace ?>;

abstract class <?= $className ?> extends \yii\rest\Controller
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
<?php if (!empty($serializerConfigs)): ?>

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        /** @var $serializer \yii\rest\Serializer */
        $serializer = \Yii::createObject($this->serializer);
<?= implode("\n", $serializerConfigs) ?>

        return $serializer->serialize($result);
    }
<?php endif; ?>

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
        if ($model !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException("Object not found: $id");
    }
<?php endif;?>
<?php endforeach; ?>
}
