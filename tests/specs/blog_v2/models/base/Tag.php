<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property string $name
 *
 * @property array|\app\models\Post[] $posts
 */
abstract class Tag extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%v2_tags}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'required' => [['name'], 'required'],
            'name_unique' => [['name'], 'unique'],
            'name_string' => [['name'], 'string', 'max' => 100],
        ];
    }

    public function getPosts()
    {
        return $this->hasMany(\app\models\Post::class, ['id' => 'post_id'])
                    ->viaTable('posts2tags', ['tag_id' => 'id']);
    }
}
