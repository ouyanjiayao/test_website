<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsCategory extends ActiveRecord
{

    const TYPE_DP = 1;

    const TYPE_CP = 2;

    const TYPE_TC = 3;

    public static $typeMap = [
        self::TYPE_DP => '单品',
        self::TYPE_CP => '菜品',
        self::TYPE_TC => '套餐'
    ];

    public static function tableName()
    {
        return '{{%goods_category}}';
    }
}

