<?php
namespace app\models\base;

use yii\base\Model;

/**
 *  Non-db doctor object
 *
 */
class Doctor extends Model
{
    /**
    * @var string 
    */
    public $name;

    /**
    * @var string 
    */
    public $surname;

    /**
    * @var array 
    */
    public $phones;


    public function rules()
    {
        return [
            'trim' => [['name', 'surname'], 'trim'],
            'required' => [['name'], 'required'],
            'name_string' => [['name'], 'string', 'max' => 200],
            'surname_string' => [['surname'], 'string', 'max' => 200],
            'safe' => [['phones'], 'safe'],
        ];
    }
}
