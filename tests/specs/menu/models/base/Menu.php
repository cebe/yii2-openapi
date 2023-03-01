<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property int $parent_id
 * @property array $args
 * @property array $kwargs
 *
 * @property \app\models\Menu $parent
 * @property array|\app\models\Menu[] $childes
 */
abstract class Menu extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%menus}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'required' => [['name'], 'required'],
            'parent_id_integer' => [['parent_id'], 'integer'],
            'parent_id_exist' => [['parent_id'], 'exist', 'targetRelation' => 'Parent'],
            'name_string' => [['name'], 'string', 'min' => 3, 'max' => 100],
            'args_default' => [['args'], 'default', 'value' => [
                'foo',
                'bar',
                'baz',
            ]],
            'kwargs_default' => [['kwargs'], 'default', 'value' => [
                [
                    'foo' => 'bar',
                ],
                [
                    'buzz' => 'fizz',
                ],
            ]],
            'safe' => [['args', 'kwargs'], 'safe'],
        ];
    }

    public function getParent()
    {
        return $this->hasOne(\app\models\Menu::class, ['id' => 'parent_id']);
    }

    public function getChildes()
    {
        return $this->hasMany(\app\models\Menu::class, ['parent_id' => 'id']);
    }
}
