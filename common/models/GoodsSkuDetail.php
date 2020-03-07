<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsSkuDetail extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%goods_sku_detail}}';
    }
}
