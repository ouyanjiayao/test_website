<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsTagAssign extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%goods_tag_assign}}';
    }
    
    public function getCategory()
    {
        return $this->hasOne(GoodsTag::className(), ['id' => 'tag_id']);
    }

}
