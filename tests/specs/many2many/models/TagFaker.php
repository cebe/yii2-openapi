<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Tag
 * @method static Tag makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Tag saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Tag[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Tag[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class TagFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Tag|\yii\db\ActiveRecord
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
        $model = new Tag();
        //$model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->name = $faker->sentence;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
