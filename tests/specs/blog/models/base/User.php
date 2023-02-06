<?php

namespace app\models\base;

/**
 * The User
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $role
 * @property int $flags
 * @property string $created_at
 *
 */
abstract class User extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%users}}';
    }

    public function rules()
    {
        return [
            'trim' => [['username', 'email', 'password', 'role', 'created_at'], 'trim'],
            'required' => [['username', 'email', 'password'], 'required'],
            'username_unique' => [['username'], 'unique'],
            'email_unique' => [['email'], 'unique'],
            'username_string' => [['username'], 'string', 'max' => 200],
            'email_string' => [['email'], 'string', 'max' => 200],
            'email_email' => [['email'], 'email'],
            'password_string' => [['password'], 'string'],
            'role_string' => [['role'], 'string', 'max' => 20],
            'role_default' => [['role'], 'default', 'value' => 'reader'],
            'flags_integer' => [['flags'], 'integer'],
            'flags_default' => [['flags'], 'default', 'value' => 0],
            'created_at_datetime' => [['created_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }
}
