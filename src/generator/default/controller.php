<?php echo '<?php';

$modelActions = [
    'index' => yii\rest\IndexAction::class,
    'view' => yii\rest\ViewAction::class,
    'create' => yii\rest\CreateAction::class,
    'update' => yii\rest\UpdateAction::class,
    'delete' => yii\rest\UpdateAction::class,
];

?>


namespace <?= $namespace ?>;

class <?= $className ?> extends \yii\rest\Controller
{
    public function actions()
    {
        return [
<?php

foreach ($actions as $action):
    if (isset($modelActions[$action['id']], $action['modelClass'])): ?>
            <?= var_export($action['id'], true) ?> => [
                'class' => \<?= $modelActions[$action['id']] ?>::class,
                'modelClass' => <?= '\\' . $action['modelClass'] . '::class' ?>,
                'checkAccess' => [$this, 'checkAccess'],
            ],
<?php endif;
    // TODO model scenario for 'create' and 'update'
endforeach;
    ?>
            'options' => [
                'class' => \yii\rest\OptionsAction::class,
            ],
        ];
    }
<?php
    $serializerConfigs = [];
    foreach ($actions as $action) {
        if (isset($modelActions[$action['id']]) && !empty($action['responseWrapper'])) {
            if (!empty($action['responseWrapper'][0])) {
                $serializerConfigs[] = '        if ($action->id === ' . var_export($action['id'], true) . ") {\n"
                    . '            return ['.var_export($action['responseWrapper'][0], true).' => $serializer->serialize($result)];' . "\n"
                    . '        }';
            } elseif (!empty($action['responseWrapper'][1])) {
                $serializerConfigs[] = '        if ($action->id === ' . var_export($action['id'], true) . ") {\n"
                    . '            $serializer->collectionEnvelope = ' . var_export($action['responseWrapper'][1], true) . ";\n"
                    . '            return $serializer->serialize($result);' . "\n"
                    . '        }';
            }
        }
    }
    if (!empty($serializerConfigs)): ?>

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
    public function checkAccess($action, $model = null, $params = [])
    {
        // TODO implement checkAccess
    }

<?php
    foreach ($actions as $action):
        if (isset($modelActions[$action['id']], $action['modelClass'])) {
            continue;
        }

        $actionName = 'action' . \yii\helpers\Inflector::id2camel($action['id']);
        $actionParams = implode(', ', array_map(function ($p) {
            return "\$$p";
        }, $action['params']));
        ?>
    public function <?= $actionName ?>(<?= $actionParams ?>)
    {
        // TODO implement <?= $actionName ?>

    }

<?php endforeach; ?>
}
