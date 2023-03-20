<?php

namespace app\models\base;

/**
 * rounting specification
 *
 * @property int $id
 * @property int $domain_id domain
 * @property string $path
 * @property bool $ssl
 * @property bool $redirect_to_ssl
 * @property string $service
 * @property string $created_at
 * @property int $d123_id desc
 * @property int $a123_id desc
 *
 * @property \app\models\Domain $domain
 * @property \app\models\D123 $d123
 * @property \app\models\A123 $a123
 */
abstract class Routing extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%routings}}';
    }

    public function rules()
    {
        return [
            'trim' => [['path', 'service'], 'trim'],
            'required' => [['domain_id'], 'required'],
            'domain_id_integer' => [['domain_id'], 'integer'],
            'domain_id_exist' => [['domain_id'], 'exist', 'targetRelation' => 'Domain'],
            'd123_id_integer' => [['d123_id'], 'integer'],
            'd123_id_exist' => [['d123_id'], 'exist', 'targetRelation' => 'D123'],
            'a123_id_integer' => [['a123_id'], 'integer'],
            'a123_id_exist' => [['a123_id'], 'exist', 'targetRelation' => 'A123'],
            'path_string' => [['path'], 'string', 'max' => 255],
            'ssl_boolean' => [['ssl'], 'boolean'],
            'redirect_to_ssl_boolean' => [['redirect_to_ssl'], 'boolean'],
            'service_string' => [['service'], 'string', 'max' => 255],
        ];
    }

    public function getDomain()
    {
        return $this->hasOne(\app\models\Domain::class, ['id' => 'domain_id']);
    }

    public function getD123()
    {
        return $this->hasOne(\app\models\D123::class, ['id' => 'd123_id']);
    }

    public function getA123()
    {
        return $this->hasOne(\app\models\A123::class, ['id' => 'a123_id']);
    }
}
