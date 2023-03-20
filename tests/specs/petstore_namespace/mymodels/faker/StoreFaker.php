<?php
namespace app\mymodels\faker;

use Faker\UniqueGenerator;
use app\mymodels\Store;

/**
 * Fake data generator for Store
 * @method static \app\mymodels\Store makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\mymodels\Store saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\mymodels\Store[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\mymodels\Store[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class StoreFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\mymodels\Store|\yii\db\ActiveRecord
     * @example
     *  $model = (new PostFaker())->generateModels(['author_id' => 1]);
     *  $model = (new PostFaker())->generateModels(function($model, $faker, $uniqueFaker) {
     *            $model->scenario = 'create';
     *            $model->author_id = 1;
     *            return $model;
     *  });
    **/
    public function generateModel($attributes = [])
    {
        $faker = $this->faker;
        $uniqueFaker = $this->uniqueFaker;
        $model = new \app\mymodels\Store();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
