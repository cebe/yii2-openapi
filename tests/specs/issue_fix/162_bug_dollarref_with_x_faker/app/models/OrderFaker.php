<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Order
 * @method static Order makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Order saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Order[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Order[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class OrderFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Order|\yii\db\ActiveRecord
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
        $model = new Order();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
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
            'Invoice',

        ];
    }
}
