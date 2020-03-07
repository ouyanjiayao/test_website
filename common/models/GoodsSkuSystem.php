<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsSkuSystem extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%goods_sku_system}}';
    }
}
