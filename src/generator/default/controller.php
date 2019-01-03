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
            <?= var_export($action['id']) ?> => [
                'class' => \<?= $modelActions[$action['id']] ?>::class,
                'modelClass' => <?= '\\app\\models\\' . $action['modelClass'] . '::class' ?>,
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
