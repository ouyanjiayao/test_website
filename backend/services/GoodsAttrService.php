<?php
namespace app\services;

use common\models\GoodsAttr;

class GoodsAttrService extends BaseModelService
{

    public $goodsType;

    public function getModelClass()
    {
        return GoodsAttr::class;
    }
    
    public function getPreCondition()
    {
        $condition = [];
        if($this->goodsType)
        {
            $condition = [
                'goods_type' => $this->goodsType
            ];
        }
        return $condition;
    }
    

    public function fastCreate($name)
    {
        $model = GoodsAttr::find()->andWhere([
            'name' => $name
        ])->one();
        if (! $model) {
            $model = new GoodsAttr();
            $model['goods_type'] = $this->goodsType;
            $model['name'] = $name;
            $model['type'] = GoodsAttr::TYPE_CUSTOM;
            $model->save();
        }
        return $model;
    }
}
