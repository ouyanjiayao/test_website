<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsAttrConfig extends ActiveRecord
{

    const ENABLE_ATTR_FALSE = 0;

    const ENABLE_ATTR_TRUE = 1;

    const AUTO_CREATE_SALE_PRICE_FALSE = 0;

    const AUTO_CREATE_SALE_PRICE_TRUE = 1;

    const PRICE_COUNT_TYPE_ITEM = 1;

    const PRICE_COUNT_TYPE_WEIGHT = 2;

    const SYS_ATTR_WEIGHT_ENABLE = 1;

    const SYS_ATTR_WEIGHT_DISABLE = 0;

    const SYS_ATTR_HANDLE_ENABLE = 1;

    const SYS_ATTR_HANDLE_DISABLE = 0;

    const SYS_ATTR_WASH_ENABLE = 1;

    const SYS_ATTR_WASH_DISABLE = 0;
    
    const HAS_CUSTOM_ATTR_FALSE = 0;
    
    const HAS_CUSTOM_ATTR_TRUE = 1;

    public static function tableName()
    {
        return '{{%goods_attr_config}}';
    }
}
