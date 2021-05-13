<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Category
 * @method static Category makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Category saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Category[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Category[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class CategoryFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Category|\yii\db\ActiveRecord
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
        $model = new Category();
        //$model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->title = substr($faker->sentence, 0, 100);
        $model->cover = $faker->sentence;
        $model->active = $faker->boolean;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
