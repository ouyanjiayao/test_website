<?php
namespace common\models;

use yii\db\ActiveRecord;

class YouzanOrder extends ActiveRecord
{
    public static $orderStateMap = [
        0=>'已关闭',
        1=>'待支付',
        2=>'待发货',
        3=>'已发货',
        4=>'完成'
    ];
    
    public static function tableName()
    {
        return '{{%youzan_order}}';
    }
}

