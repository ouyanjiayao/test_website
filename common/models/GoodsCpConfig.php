<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsCpConfig extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%goods_cp_config}}';
    }
}
