<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Delivery
 * @method static Delivery makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Delivery saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Delivery[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Delivery[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class DeliveryFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Delivery|\yii\db\ActiveRecord
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
        $model = new Delivery();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->title = $faker->sentence;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
