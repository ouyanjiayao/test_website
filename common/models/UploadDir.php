<?php
namespace common\models;

use yii\db\ActiveRecord;

class UploadDir extends ActiveRecord
{
    CONST TYPE_CUSTOM = 1;
    
    CONST TYPE_GOODS_DP = 2;
    
    CONST TYPE_GOODS_CP = 3;
    
    CONST TYPE_GOODS_TC = 4;
    
    CONST ID_DP = 1;
    
    CONST ID_CP = 2;
    
    CONST ID_TC = 3;
    
    public static function tableName()
    {
        return '{{%upload_dir}}';
    }
}

