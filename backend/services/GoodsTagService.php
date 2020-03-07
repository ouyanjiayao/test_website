<?php
namespace app\services;

use common\models\GoodsTag;
use common\models\GoodsTagAssign;
use common\librarys\ArrayHelper;

class GoodsTagService extends BaseModelService
{

    public function getModelClass()
    {
        return GoodsTag::class;
    }

    public function getPreCondition()
    {
        return [
            'is_delete' => 0
        ];
    }

    public function getSaveForm($model = null)
    {
        $form = [
            'name' => ''
        ];
        if ($model) {
            $form = ArrayHelper::merge($form, $model->getAttributes([
                'name'
            ]));
        }
        return $form;
    }
    
    public function getSaveInfo($model = null)
    {
        $info = null;
        if ($model) {
            $info = [];
            $info['youzan_id'] = $model['youzan_id'];
            $info['version'] = $model['version'];
            $info['youzan_version'] = $model['youzan_version'];
            $info['youzan_syn_time'] = $model['youzan_syn_time']?date('Y/m/d H:i',$model['youzan_syn_time']):null;
            $info['youzan_syn_state'] = GoodsTag::$youzanySynStateMap[$model['youzan_syn_state']];
        }
        return $info;
    }

    public function save($model, $data)
    {
        if ($model->isNewRecord) {
            $model['type'] = GoodsTag::TYPE_MANUAL;
            $model['is_delete'] = 0;
        }
        $model['version'] = intval($model['version']) + 1;
        if($model['type'] == GoodsTag::TYPE_MANUAL)
        {
            $model['name'] = $data['name'];
        }
        $model['youzan_syn_time'] = null;
        $model['youzan_syn_state'] = GoodsTag::YOUZAN_SYN_STATE_NOT;
        $model->save();
    }

    public function deleteById($id)
    {
        GoodsTag::updateAll([
            'is_delete' => 1
        ], [
            'and',
            [
                'id' => $id
            ],
            [
                'type' => GoodsTag::TYPE_MANUAL
            ]
        ]);
        GoodsTagAssign::deleteAll([
            'tag_id' => $id
        ]);
        return $id;
    }

}
