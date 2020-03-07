<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsTcConfig extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%goods_tc_config}}';
    }
}
