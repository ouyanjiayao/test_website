<?php
namespace app\services;

use common\models\GoodsAttrItem;
use common\models\GoodsAttr;

class GoodsAttrItemService extends BaseModelService
{

    public function getModelClass()
    {
        return GoodsAttrItem::class;
    }

    public function fastCreate($name, $attrId)
    {
        $model = GoodsAttrItem::find()->andWhere([
            'name' => $name,
            'attr_id' => $attrId,
            'type' => GoodsAttrItem::TYPE_CUSTOM
        ])->one();
        if (! $model) {
            $model = new GoodsAttrItem();
            $model['name'] = $name;
            $model['attr_id'] = $attrId;
            $model['type'] = GoodsAttrItem::TYPE_CUSTOM;
            $model->save();
        }
        return $model;
    }

    public function fastCreate_weight($value,$desc)
    {
        $name = "约{$value}g";
        $descArray = [];
        if($value<500)
        {
            if($value == 250)
            {
                $descArray[] = '半斤';
            }else{
                $descArray[] = ($value/50) .'两';
            }
        }else{
            $jin = number_format($value/500,1);
            $jinA = explode('.',(string)$jin);
            if($jinA[1]==5)
            {
                $descArray[] = $jinA[0]. "斤半";
            }else{
                $descArray[] = floatval($jin). "斤";
            }
        }
        if($desc)
        {
            $descArray[] = $desc;
        }
        $desc = implode(',',$descArray);
        if($desc)
        {
            $name.= "({$desc})";
        }
        $model = GoodsAttrItem::find()->andWhere([
            'name' => $name,
            'attr_id' => GoodsAttr::SYSTEM_WEIGHT_ID,
            'type' => GoodsAttrItem::TYPE_CUSTOM
        ])->one();
        if (! $model) {
            $model = new GoodsAttrItem();
            $model['attr_id'] = GoodsAttr::SYSTEM_WEIGHT_ID;
            $model['name'] = $name;
            $model['weight'] = $value;
            $model['type'] = GoodsAttrItem::TYPE_CUSTOM;
            $model->save();
        }
        return $model;
    }
}
