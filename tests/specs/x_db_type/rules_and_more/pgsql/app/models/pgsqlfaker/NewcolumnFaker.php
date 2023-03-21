<?php
namespace app\models\pgsqlfaker;

use Faker\UniqueGenerator;
use app\models\pgsqlmodel\Newcolumn;

/**
 * Fake data generator for Newcolumn
 * @method static \app\models\pgsqlmodel\Newcolumn makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\pgsqlmodel\Newcolumn saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\pgsqlmodel\Newcolumn[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\pgsqlmodel\Newcolumn[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class NewcolumnFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\pgsqlmodel\Newcolumn|\yii\db\ActiveRecord
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
        $model = new \app\models\pgsqlmodel\Newcolumn();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        $model->first_name = $faker->sentence;
        $model->last_name = $faker->sentence;
        $model->dec_col = $faker->randomFloat();
        $model->json_col = [];
        $model->varchar_col = $faker->sentence;
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
