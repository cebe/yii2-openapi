<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for ColumnNameChange
 * @method static ColumnNameChange makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static ColumnNameChange saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static ColumnNameChange[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static ColumnNameChange[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class ColumnNameChangeFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return ColumnNameChange|\yii\db\ActiveRecord
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
        $model = new ColumnNameChange();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        $model->updated_at_2 = $faker->sentence;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
