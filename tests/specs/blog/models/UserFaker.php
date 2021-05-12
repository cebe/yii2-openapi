<?php
namespace app\models;

/**
 * Fake data generator for User
 */
class UserFaker extends BaseModelFaker
{

    /**
     * @return User|\yii\db\ActiveRecord
    **/
    public function generateModel()
    {
        $faker = $this->faker;
        $uniqueFaker = $this->uniqueFaker;
        $model = new User();
        //$model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->username = substr($faker->userName, 0, 200);
        $model->email = substr($faker->safeEmail, 0, 200);
        $model->password = $faker->password;
        $model->role = $faker->randomElement(['admin', 'editor', 'reader']);
        $model->flags = $faker->numberBetween(0, 2147483647);
        $model->created_at = $faker->dateTimeThisYear('now', 'UTC')->format(DATE_ATOM);
        return $model;
    }

    /**
     * @param array|callable $attributes
     * @param bool  $save
     * @return User|\yii\db\ActiveRecord
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
     * @return array|\yii\db\ActiveRecord[]|User[]
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
