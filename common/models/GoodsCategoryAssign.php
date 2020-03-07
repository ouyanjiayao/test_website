<?php
namespace common\models;

use yii\db\ActiveRecord;

class GoodsCategoryAssign extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%goods_category_assign}}';
    }
    
    public function getCategory()
    {
        return $this->hasOne(GoodsCategory::className(), ['id' => 'category_id']);
    }

}
