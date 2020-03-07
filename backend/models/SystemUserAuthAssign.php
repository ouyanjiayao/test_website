<?php
namespace app\models;

use yii\db\ActiveRecord;

class SystemUserAuthAssign extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%system_user_auth_assign}}';
    }

}
