<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Fruit
 * @method static Fruit makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Fruit saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Fruit[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Fruit[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class FruitFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Fruit|\yii\db\ActiveRecord
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
        $model = new Fruit();
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
