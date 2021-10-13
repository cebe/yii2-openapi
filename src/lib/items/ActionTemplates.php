<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use function in_array;

class ActionTemplates
{
    public const ACTIONS = [
        'list' => [
            'class' => '\yii\rest\IndexAction::class',
        ],
        'view' => [
            'class' => '\yii\rest\ViewAction::class',
            'implementation' => <<<'PHP'
        $model = $this->findModel($id);
        $this->checkAccess(ACTION_ID, $model);
        return $model;
PHP
    ,
        ],
        'create' => [
            'class' => '\yii\rest\CreateAction::class',
        ],
        'update' => [
            'class' => '\yii\rest\UpdateAction::class',
            'implementation' => <<<'PHP'
        $model = $this->findModel($id);
        $this->checkAccess(ACTION_ID, $model);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new \yii\web\ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
PHP
    ,
        ],
        'delete' => [
            'class' => '\yii\rest\DeleteAction::class',
            'implementation' => <<<'PHP'
        $model = $this->findModel($id);
        $this->checkAccess(ACTION_ID, $model);

        if ($model->delete() === false) {
            throw new \yii\web\ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        \Yii::$app->response->setStatusCode(204);
PHP
    ,
        ],
    ];

    public static function hasTemplate(string $actionId):bool
    {
        return isset(self::ACTIONS[$actionId]);
    }

    public static function hasImplementation(string $actionId):bool
    {
        return in_array($actionId, ['view', 'update', 'delete']);
    }

    public static function getTemplate(string $actionId):?array
    {
        return self::ACTIONS[$actionId] ?? null;
    }
}
