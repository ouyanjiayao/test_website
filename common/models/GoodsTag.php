<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsTag extends ActiveRecord
{
    
    const TYPE_MANUAL = 1;
    
    const TYPE_SYSTEM = 2;
    
    public static $typeMap = [
        self::TYPE_MANUAL => '手动创建',
        self::TYPE_SYSTEM => '系统创建'
    ];
    
    const YOUZAN_SYN_STATE_NOT = 0;
    
    const YOUZAN_SYN_STATE_FAIL = 1;
    
    const YOUZAN_SYN_STATE_ERROR = 2;
    
    const YOUZAN_SYN_STATE_SUCCESS = 3;
    
    public static $youzanySynStateMap = [
        self::YOUZAN_SYN_STATE_NOT=>'未同步',
        self::YOUZAN_SYN_STATE_FAIL => '接口调用失败',
        self::YOUZAN_SYN_STATE_ERROR => '同步失败',
        self::YOUZAN_SYN_STATE_SUCCESS => '同步成功'
    ];

    public static function tableName()
    {
        return '{{%goods_tag}}';
    }
}
