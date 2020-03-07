<?php
namespace common\models;

use yii\db\ActiveRecord;

class Goods extends ActiveRecord
{

    const STATE_DISABLE = 0;

    const STATE_ENABLE = 1;

    public static $stateMap = [
        self::STATE_DISABLE => '下架',
        self::STATE_ENABLE => '上架'
    ];

    const TYPE_DP = 1;

    const TYPE_CP = 2;

    const TYPE_TC = 3;

    public static $typeMap = [
        self::TYPE_DP => '单品',
        self::TYPE_CP => '菜品',
        self::TYPE_TC => '套餐'
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
        return '{{%goods}}';
    }
}
