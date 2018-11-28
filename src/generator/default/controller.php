<?= '<?php' ?>


namespace <?= $namespace ?>;

class <?= $className ?> extends \yii\web\Controller
{
<?php
    foreach($actions as $action):
        $actionName = 'action' . \yii\helpers\Inflector::id2camel($action);
        // TODO add action params to function
        ?>
    public function <?= $actionName ?>()
    {
        // TODO implement <?= $actionName ?>

    }
<?php endforeach; ?>
}
