<?php

namespace app\controllers;

class PersonWatchController extends \app\controllers\base\PersonWatchController
{

    public function actions()
    {
        $actions = parent::actions();
        return $actions;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        //TODO implement checkAccess
    }

    public function actionCreate($personId)
    {
        //TODO implement actionCreate
    }


}

