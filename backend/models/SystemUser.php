<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class SystemUser extends ActiveRecord implements \yii\web\IdentityInterface
{
    const PASSWORD_MIN_LENGTH = 6;
    
    const PASSWORD_MAX_LENGTH = 20;

    const TYPE_ADMIN = 2;

    const TYPE_SUPER_ADMIN = 1;
    
    const STATE_DISABLE = 0;

    const STATE_ENABLE = 1;

    public static $stateMap = [
        self::STATE_DISABLE => '禁用',
        self::STATE_ENABLE => '启用'
    ];

    public static $typeMap = [
        self::TYPE_ADMIN => '管理员',
        self::TYPE_SUPER_ADMIN => '超级管理员'
    ];

    public static function tableName()
    {
        return '{{%system_user}}';
    }

    public function getId()
    {
        return $this['id'];
    }

    public static function findIdentity($id)
    {
        return self::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public function getAuthKey()
    {
        return $this['auth_key'];
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    
}
