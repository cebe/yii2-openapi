<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Menu
 * @method static Menu makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Menu saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Menu[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Menu[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class MenuFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Menu|\yii\db\ActiveRecord
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
        $model = new Menu();
        //$model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->name = substr($faker->text(100), 0, 100);
        $model->args = [];
        $model->kwargs = [];
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
