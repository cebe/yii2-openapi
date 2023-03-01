<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Fakerable
 * @method static Fakerable makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Fakerable saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Fakerable[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Fakerable[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class FakerableFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Fakerable|\yii\db\ActiveRecord
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
        $model = new Fakerable();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->active = $faker->boolean;
        $model->floatval = $faker->randomFloat();
        $model->floatval_lim = $faker->randomFloat(null, 0, 1);
        $model->doubleval = $faker->randomFloat();
        $model->int_min = $faker->numberBetween(5, 1000000);
        $model->int_max = $faker->numberBetween(0, 5);
        $model->int_minmax = $faker->numberBetween(5, 25);
        $model->int_created_at = $faker->unixTime;
        $model->int_simple = $faker->numberBetween(0, 1000000);
        $model->str_text = $faker->sentence;
        $model->str_varchar = substr($faker->text(100), 0, 100);
        $model->str_date = $faker->dateTimeThisCentury->format('Y-m-d');
        $model->str_datetime = $faker->dateTimeThisYear('now', 'UTC')->format('Y-m-d H:i:s');
        $model->str_country = $faker->countryCode;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
