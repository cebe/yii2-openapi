<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Editcolumn
 * @method static Editcolumn makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Editcolumn saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Editcolumn[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Editcolumn[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class EditcolumnFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Editcolumn|\yii\db\ActiveRecord
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
        $model = new Editcolumn();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = substr($faker->text(254), 0, 254);
        $model->tag = $faker->sentence;
        $model->first_name = substr($faker->text(255), 0, 255);
        $model->string_col = $faker->sentence;
        $model->dec_col = $faker->randomFloat();
        $model->str_col_def = substr($faker->word(3), 0, 3);
        $model->json_col = $faker->sentence;
        $model->json_col_2 = [];
        $model->numeric_col = $faker->randomFloat();
        $model->json_col_def_n = [];
        $model->json_col_def_n_2 = [];
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
