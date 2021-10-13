<?php

namespace app\models\base;

/**
 * A Pet
 *
 * @property int $id
 * @property string $name
 * @property string $tag
 *
 * @property array|\app\models\Pet[] $duplicates
 */
abstract class Pet extends \yii\db\ActiveRecord
{
    protected $virtualAttributes = ['guests_count', 'petCode'];

    /**
     * @var int
    */
    public $guests_count;

    /**
     * @var string
    */
    public $petCode;

    public static function tableName()
    {
        return '{{%pets}}';
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), $this->virtualAttributes);
    }

    public function afterFind()
    {
        parent::afterFind();
        foreach ($this->virtualAttributes as $attr) {
            $this->$attr = $this->getAttribute($attr);
        }
    }

    public function rules()
    {
        return [
            'trim' => [['name', 'tag', 'petCode'], 'trim'],
            'required' => [['name'], 'required'],
            'name_string' => [['name'], 'string'],
            'tag_string' => [['tag'], 'string'],
            'petCode_string' => [['petCode'], 'string', 'max' => 50],
        ];
    }

    public function getDuplicates()
    {
        return $this->hasMany(\app\models\Pet::class, ['tag' => 'tag']);
    }
}
