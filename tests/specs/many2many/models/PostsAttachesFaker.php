<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for PostsAttaches
 * @method static PostsAttaches makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static PostsAttaches saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static PostsAttaches[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static PostsAttaches[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PostsAttachesFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return PostsAttaches|\yii\db\ActiveRecord
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
        $model = new PostsAttaches();
        //$model->id = $uniqueFaker->numberBetween(0, 2147483647);
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
