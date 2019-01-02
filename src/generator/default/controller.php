<?= '<?php' ?>


namespace <?= $namespace ?>;

class <?= $className ?> extends \yii\web\Controller
{
<?php
    foreach($actions as $action):
        $actionName = 'action' . \yii\helpers\Inflector::id2camel($action['id']);
        $actionParams = implode(', ', array_map(function($p) { return "\$$p"; }, $action['params']));
        ?>
    public function <?= $actionName ?>(<?= $actionParams ?>)
    {
        // TODO implement <?= $actionName ?>

    }

<?php endforeach; ?>
}
