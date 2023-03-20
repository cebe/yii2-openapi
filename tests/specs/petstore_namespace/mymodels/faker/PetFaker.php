<?php
namespace app\mymodels\faker;

use Faker\UniqueGenerator;
use app\mymodels\Pet;

/**
 * Fake data generator for Pet
 * @method static \app\mymodels\Pet makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\mymodels\Pet saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\mymodels\Pet[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\mymodels\Pet[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PetFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\mymodels\Pet|\yii\db\ActiveRecord
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
        $model = new \app\mymodels\Pet();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        $model->store_id = $faker->randomElement(\app\mymodels\Store::find()->select("id")->column());
        $model->tag = $faker->randomElement(['one', 'two', 'three', 'four']);
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }

    public static function dependentOn()
    {
        return [
            // just model class names
            'Store',

        ];
    }
}
