<?php
/**@var \cebe\yii2openapi\lib\items\FractalAction $action */
?>
        $model = $this-><?=$action->findModelMethodName?>($<?=$action->idParam?>);
        $this->checkAccess($action, $model);
        if ($model->delete() === false) {
            throw new \yii\web\ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }
        Yii::$app->getResponse()->setStatusCode(204);
