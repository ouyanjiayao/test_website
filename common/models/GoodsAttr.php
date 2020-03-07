<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsAttr extends ActiveRecord
{
    CONST TYPE_CUSTOM = 1;
    
    CONST TYPE_SYSTEM = 2;
    
    CONST SYSTEM_WEIGHT_ID = 1;
    
    CONST SYSTEM_HANDLE_ID = 2;
    
    CONST SYSTEM_WASH_ID = 3;
    
    public static function tableName()
    {
        return '{{%goods_attr}}';
    }
}
