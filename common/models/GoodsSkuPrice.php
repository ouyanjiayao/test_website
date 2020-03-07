<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsSkuPrice extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%goods_sku_price}}';
    }
}
