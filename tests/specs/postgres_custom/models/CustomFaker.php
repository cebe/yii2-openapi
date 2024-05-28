<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Custom
 * @method static Custom makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Custom saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Custom[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Custom[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class CustomFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Custom|\yii\db\ActiveRecord
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
        $model = new Custom();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->num = $faker->numberBetween(0, 1000000);
        $model->json1 = [];
        $model->json2 = [];
        $model->json3 = [];
        $model->json4 = [];
        $model->status = $faker->randomElement(['active','draft']);
        $model->status_x = $faker->randomElement(['active','draft']);
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
