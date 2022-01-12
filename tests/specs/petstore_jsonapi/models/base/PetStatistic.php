<?php
namespace app\models\base;

use yii\base\Model;

/**
 *  Non-Db model
 *
 */
class PetStatistic extends Model
{
    /**
    * @var int 
    */
    public $id;

    /**
    * @var string 
    */
    public $title;

    /**
    * @var int 
    */
    public $dogsCount;

    /**
    * @var int 
    */
    public $catsCount;

    /**
    * @var string 
    */
    public $summary;

    /**
    * @var Pet    */
    public $parentPet;

    /**
    * @var array|Pet[]
    */
    public $favoritePets;


    public function rules()
    {
        return [
            'trim' => [['title', 'summary'], 'trim'],
            'parentPet_id_integer' => [['parentPet_id'], 'integer'],
            'parentPet_id_exist' => [['parentPet_id'], 'exist', 'targetRelation' => 'ParentPet'],
            'title_string' => [['title'], 'string'],
            'dogsCount_integer' => [['dogsCount'], 'integer'],
            'catsCount_integer' => [['catsCount'], 'integer'],
            'summary_string' => [['summary'], 'string'],
        ];
    }
}
