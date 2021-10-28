<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Pet
 * @method static Pet makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Pet saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Pet[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Pet[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PetFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Pet|\yii\db\ActiveRecord
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
        $model = new Pet();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        $model->tag = $faker->randomElement(['one', 'two', 'three', 'four']);
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
