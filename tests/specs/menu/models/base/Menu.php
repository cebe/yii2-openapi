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
            [['name'], 'trim'],
            [['name'], 'required'],
            [['parent_id'], 'integer'],
            [['parent_id'], 'exist', 'targetRelation' => 'Parent'],
            [['name'], 'string', 'min' => 3, 'max' => 100],
            [['args', 'kwargs'], 'safe'],
        ];
    }

    public function getParent()
    {
        return $this->hasOne(\app\models\Menu::class,['id' => 'parent_id']);
    }
    public function getChildes()
    {
        return $this->hasMany(\app\models\Menu::class,['parent_id' => 'id']);
    }
}
