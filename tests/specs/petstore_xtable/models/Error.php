<?php

namespace app\models;

use yii\base\Model;

/**
 * An Error
 *
 */
class Error extends Model
{
    /**
     * @var int
     */
    public $code;
    /**
     * @var string
     */
    public $message;


    public function rules()
    {
        return [
            [['message'], 'trim'],
            [['code', 'message'], 'required'],
            [['message'], 'string'],
            // TODO define more concreate validation rules!
            [['code'], 'safe'],
        ];
    }
}
