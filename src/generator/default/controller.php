<?php echo '<?php';

$modelActions = [
    'index' => [
        'class' => yii\rest\IndexAction::class,
    ],
    'view' => [
        'class' => yii\rest\ViewAction::class,
        'implementation' => <<<'PHP'
        $model = $this->findModel($id);
        $this->checkAccess(ACTION_ID, $model);
        return $model;
PHP
    ],
    'create' => [
        'class' => yii\rest\CreateAction::class,
    ],
    'update' => [
        'class' => yii\rest\UpdateAction::class,
        'implementation' => <<<'PHP'
        $model = $this->findModel($id);
        $this->checkAccess(ACTION_ID, $model);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
PHP
    ],
    'delete' => [
        'class' => yii\rest\DeleteAction::class,
        'implementation' => <<<'PHP'
        $model = $this->findModel($id);
        $this->checkAccess(ACTION_ID, $model);

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        \Yii::$app->response->setStatusCode(204);
PHP
    ],
];
$findModel = [];

?>


namespace <?= $namespace ?>;

class <?= $className ?> extends \yii\rest\Controller
{
    public function actions()
    {
        return [
<?php

foreach ($actions as $action):
    if (isset($modelActions[$action['id']], $action['modelClass']) && ($action['idParam'] === null || $action['idParam'] === 'id')): ?>
            <?= var_export($action['id'], true) ?> => [
                'class' => \<?= $modelActions[$action['id']]['class'] ?>::class,
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
            if ($action['idParam'] === null || $action['idParam'] === 'id') {
                continue;
            }
            if (isset($modelActions[$action['id']]['implementation'])) {
                $implementation = $modelActions[$action['id']]['implementation'];
                $findModel[$action['modelClass']] = 'find' . \yii\helpers\StringHelper::basename($action['modelClass']) . 'Model';
                $implementation = str_replace('findModel', $findModel[$action['modelClass']], $implementation);
                $implementation = str_replace('$id', '$'.$action['idParam'], $implementation);
                $implementation = str_replace('ACTION_ID', var_export($action['id'], true), $implementation);
            }
        }

        $actionName = 'action' . \yii\helpers\Inflector::id2camel($action['id']);
        $actionParams = implode(', ', array_map(function ($p) {
            return "\$$p";
        }, $action['params']));
        ?>

    public function <?= $actionName ?>(<?= $actionParams ?>)
    {
<?= $implementation ?? '        // TODO implement ' . $actionName ?>

    }
<?php endforeach; ?>
<?php foreach ($findModel as $modelName => $methodName): ?>

    /**
     * Returns the <?= \yii\helpers\StringHelper::basename($modelName) ?> model based on the primary key given.
     * If the data model is not found, a 404 HTTP exception will be raised.
     * @param string $id the ID of the model to be loaded.
     * @return \<?= $modelName ?> the model found
     * @throws NotFoundHttpException if the model cannot be found.
     */
    public function <?= $methodName ?>($id)
    {
        $model = \<?= $modelName ?>::findOne($id);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException("Object not found: $id");
    }
<?php endforeach; ?>
}
