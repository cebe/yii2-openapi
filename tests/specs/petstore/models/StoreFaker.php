<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Store
 * @method static Store makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Store saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Store[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Store[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class StoreFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Store|\yii\db\ActiveRecord
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
        $model = new Store();
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
