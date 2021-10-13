<?php

namespace app\controllers;

class MeController extends \app\controllers\base\MeController
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


}

