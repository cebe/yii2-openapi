<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $lang
 *
 * @property array|\app\models\PostTag[] $post_tags
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
            'trim' => [['name', 'lang'], 'trim'],
            'required' => [['name', 'lang'], 'required'],
            'name_unique' => [['name'], 'unique'],
            'name_string' => [['name'], 'string', 'max' => 100],
            'lang_string' => [['lang'], 'string'],
            'lang_in' => [['lang'], 'in', 'range' => ['ru', 'eng']],
        ];
    }

    public function getPostTags()
    {
        return $this->hasMany(\app\models\PostTag::class, ['tag_id' => 'id']);
    }
}
