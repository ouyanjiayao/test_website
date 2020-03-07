<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsAttrItem extends ActiveRecord
{
    CONST TYPE_CUSTOM = 1;
    
    CONST TYPE_SYSTEM = 2;
    
    const HANDLE_NOT_ID = 1;
    
    const NEED_WASH_NOT_ID = 2;
    
    const NEED_WASH_YES_ID = 3;
    
    public static function tableName()
    {
        return '{{%goods_attr_item}}';
    }
}
