<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Post
 * @method static Post makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Post saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Post[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Post[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PostFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Post|\yii\db\ActiveRecord
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
        $model = new Post();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->title = substr($faker->sentence, 0, 255);
        $model->slug = substr($uniqueFaker->slug, 0, 200);
        $model->active = $faker->boolean;
        $model->created_at = $faker->dateTimeThisCentury->format('Y-m-d');
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
