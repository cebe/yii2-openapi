<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Alldbdatatype
 * @method static Alldbdatatype makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Alldbdatatype saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Alldbdatatype[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Alldbdatatype[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class AlldbdatatypeFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Alldbdatatype|\yii\db\ActiveRecord
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
        $model = new Alldbdatatype();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->string_col = substr($faker->text(255), 0, 255);
        $model->varchar_col = substr($faker->text(132), 0, 132);
        $model->text_col = $faker->sentence;
        $model->varchar_4_col = substr($faker->text(4), 0, 4);
        $model->char_4_col = substr($faker->text(4), 0, 4);
        $model->char_5_col = $faker->sentence;
        $model->char_6_col = $faker->sentence;
        $model->char_7_col = substr($faker->text(6), 0, 6);
        $model->char_8_col = $faker->sentence;
        $model->decimal_col = $faker->randomFloat();
        $model->numeric_col = $faker->randomFloat();
        $model->float_col = $faker->randomFloat();
        $model->float_2 = $faker->randomFloat();
        $model->float_3 = $faker->randomFloat();
        $model->double_col = $faker->randomFloat();
        $model->double_p = $faker->randomFloat();
        $model->double_p_2 = $faker->randomFloat();
        $model->real_col = $faker->randomFloat();
        $model->date_col = $faker->sentence;
        $model->time_col = $faker->sentence;
        $model->datetime_col = $faker->sentence;
        $model->timestamp_col = $faker->unixTime;
        $model->year_col = $faker->sentence;
        $model->json_col = [];
        $model->json_col_def = [];
        $model->json_col_def_2 = [];
        $model->text_def = $faker->sentence;
        $model->json_def = [];
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
