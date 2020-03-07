<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsAttrAssign extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%goods_attr_assign}}';
    }
}
