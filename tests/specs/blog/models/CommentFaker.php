<?php
namespace app\models;

/**
 * Fake data generator for Comment
 */
class CommentFaker extends BaseModelFaker
{

    /**
     * @return Comment|\yii\db\ActiveRecord
    **/
    public function generateModel()
    {
        $faker = $this->faker;
        $uniqueFaker = $this->uniqueFaker;
        $model = new Comment();
        //$model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->message = [];
        $model->meta_data = [];
        $model->created_at = $faker->unixTime;
        return $model;
    }

    /**
     * @param array|callable $attributes
     * @param bool  $save
     * @return Comment|\yii\db\ActiveRecord
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
     * @return array|\yii\db\ActiveRecord[]|Comment[]
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
