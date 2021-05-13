<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Photo
 * @method static Photo makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Photo saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Photo[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Photo[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PhotoFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Photo|\yii\db\ActiveRecord
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
        $model = new Photo();
        //$model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->filename = $faker->sentence;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
