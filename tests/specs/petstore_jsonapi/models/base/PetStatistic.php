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
    * @var array|Pet[]
    */
    public $favoritePets;


    public function rules()
    {
        return [
            'trim' => [['title', 'summary'], 'trim'],
            'title_string' => [['title'], 'string'],
            'dogsCount_integer' => [['dogsCount'], 'integer'],
            'catsCount_integer' => [['catsCount'], 'integer'],
            'summary_string' => [['summary'], 'string'],
        ];
    }
}
