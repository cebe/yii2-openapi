<?php

namespace app\controllers;

class AuthController extends \app\controllers\base\AuthController
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

    public function actionPasswordRecovery()
    {
        //TODO implement actionPasswordRecovery
    }

    public function actionCreatePasswordRecovery()
    {
        //TODO implement actionCreatePasswordRecovery
    }

    public function actionPasswordConfirmRecovery($token)
    {
        //TODO implement actionPasswordConfirmRecovery
    }

    public function actionCreateNewPassword()
    {
        //TODO implement actionCreateNewPassword
    }


}

