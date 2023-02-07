<?php
namespace app\models\mariafaker;

use Faker\UniqueGenerator;
use app\models\mariamodel\Pristine;

/**
 * Fake data generator for Pristine
 * @method static \app\models\mariamodel\Pristine makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\mariamodel\Pristine saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\mariamodel\Pristine[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\mariamodel\Pristine[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PristineFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\mariamodel\Pristine|\yii\db\ActiveRecord
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
        $model = new Pristine();
        $model->custom_id_col = $faker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        $model->tag = $faker->sentence;
        $model->new_col = substr($faker->text(17), 0, 17);
        $model->col_5 = $faker->randomFloat();
        $model->col_6 = $faker->randomFloat();
        $model->col_7 = $faker->randomFloat();
        $model->col_8 = [];
        $model->col_9 = substr($faker->text(9), 0, 9);
        $model->col_10 = substr($faker->text(10), 0, 10);
        $model->col_11 = $faker->sentence;
        $model->price = $faker->randomFloat();
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
