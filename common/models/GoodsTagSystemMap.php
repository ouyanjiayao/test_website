<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsTagSystemMap extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%goods_tag_system_map}}';
    }
}
