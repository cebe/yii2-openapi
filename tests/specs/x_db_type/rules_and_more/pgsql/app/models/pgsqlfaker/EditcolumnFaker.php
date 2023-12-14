<?php
namespace app\models\pgsqlfaker;

use Faker\UniqueGenerator;
use app\models\pgsqlmodel\Editcolumn;

/**
 * Fake data generator for Editcolumn
 * @method static \app\models\pgsqlmodel\Editcolumn makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\pgsqlmodel\Editcolumn saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\pgsqlmodel\Editcolumn[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\pgsqlmodel\Editcolumn[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class EditcolumnFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\pgsqlmodel\Editcolumn|\yii\db\ActiveRecord
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
        $model = new \app\models\pgsqlmodel\Editcolumn();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = substr($faker->text(254), 0, 254);
        $model->tag = $faker->sentence;
        $model->first_name = $faker->sentence;
        $model->string_col = $faker->sentence;
        $model->dec_col = $faker->randomFloat();
        $model->str_col_def = $faker->sentence;
        $model->json_col = $faker->sentence;
        $model->json_col_2 = ["a" => "b"];
        $model->numeric_col = $faker->randomFloat();
        $model->json_col_def_n = [];
        $model->json_col_def_n_2 = [];
        $model->text_col_array = [];
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
