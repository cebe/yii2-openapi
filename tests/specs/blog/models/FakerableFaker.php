<?php
namespace app\models;

/**
 * Fake data generator for Fakerable
 */
class FakerableFaker extends BaseModelFaker
{

    /**
     * @return Fakerable|\yii\db\ActiveRecord
    **/
    public function generateModel()
    {
        $faker = $this->faker;
        $uniqueFaker = $this->uniqueFaker;
        $model = new Fakerable();
        //$model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->active = $faker->boolean;
        $model->floatval = $faker->randomFloat();
        $model->floatval_lim = $faker->randomFloat(null, 0, 1);
        $model->doubleval = $faker->randomFloat();
        $model->int_min = $faker->numberBetween(5, 2147483647);
        $model->int_max = $faker->numberBetween(0, 5);
        $model->int_minmax = $faker->numberBetween(5, 25);
        $model->int_created_at = $faker->unixTime;
        $model->int_simple = $faker->numberBetween(0, 2147483647);
        $model->uuid = $faker->uuid;
        $model->str_text = $faker->sentence;
        $model->str_varchar = substr($faker->text(100), 0, 100);
        $model->str_date = $faker->dateTimeThisCentury->format('Y-m-d');
        $model->str_datetime = $faker->dateTimeThisYear('now', 'UTC')->format(DATE_ATOM);
        $model->str_country = $faker->countryCode;
        return $model;
    }

    /**
     * @param array|callable $attributes
     * @param bool  $save
     * @return Fakerable|\yii\db\ActiveRecord
     * @example MyFaker::makeOne(['user_id' => 1, 'title' => 'foo']);
     * @example MyFaker::makeOne( function($model, $faker) {
     *        $model->scenario = 'create';
     *        $model->setAttributes(['user_id' => 1, 'title' => $faker->sentence]);
     *        return $model;
     *  }, true);
     */
    public static function makeOne($attributes = [], bool $save = false)
    {
        return parent::makeOne($attributes, $save);
    }

    /**
     * @param int $number
     * @param array|callable $commonAttributes
     * @param bool  $save
     * @return array|\yii\db\ActiveRecord[]|Fakerable[]
     * @example TaskFaker::make(5, ['project_id'=>1, 'user_id' => 2]);
     * @example TaskFaker::make(5, function($model, $faker, $uniqueFaker) {
     *       $model->setAttributes(['name' => $uniqueFaker->username, 'state'=>$faker->boolean(20)]);
     *       return $model;
     * });
     */
    public static function make(int $number, $commonAttributes = [], bool $save = false):array
    {
        return parent::make($number, $commonAttributes, $save);
    }
}
