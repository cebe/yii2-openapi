<?php
namespace app\models\base;

use yii\base\Model;

/**
 *  Information about a user watching a Person
 *
 */
class PersonWatch extends Model
{
    /**
    * @var string The MongoDB Identifier
    */
    public $personId;

    /**
    * @var string The MongoDB Identifier
    */
    public $userId;

    /**
    * @var int 
    */
    public $someProp;


    public function rules()
    {
        return [
            'trim' => [['personId', 'userId'], 'trim'],
            'personId_string' => [['personId'], 'string'],
            'userId_string' => [['userId'], 'string'],
            'someProp_integer' => [['someProp'], 'integer'],
        ];
    }
}
