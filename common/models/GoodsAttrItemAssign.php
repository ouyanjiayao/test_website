<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsAttrItemAssign extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%goods_attr_item_assign}}';
    }
}
