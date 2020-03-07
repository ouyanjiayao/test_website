<?php
namespace app\models;

use yii\db\ActiveRecord;

class SystemAuthRule extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%system_auth_rule}}';
    }
    
}

