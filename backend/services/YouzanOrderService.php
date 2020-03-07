<?php
namespace app\services;

use common\models\YouzanOrder;

class YouzanOrderService extends BaseModelService
{
    public function getModelClass()
    {
        return YouzanOrder::class;
    }
    
    public function save($model,$data){
        if($data['reset_zt_print'])
        {
            $model['zt_print_state'] = 0;
        }
        if($data['reset_fk_print'])
        {
            $model['fk_print_state'] = 0;
        }
        $model->save();
    }
    
    public function getInfoData($model){
        $data = [
            'order_num'=>$model['order_num']?$model['order_num']:'未生成',
            'tid'=>$model['tid'],
            'order_state'=>YouzanOrder::$orderStateMap[$model['order_state']],
            'created_time'=>Date('Y-m-d H:i:s',$model['created_time']),
            'update_time'=>Date('Y-m-d H:i:s',$model['update_time'])
        ];
        if ($model['zt_print_state'] == 0) {
            $data['zt_print_state'] = '未打印';
        } else if ($model['zt_print_state'] == 1) {
            $data['zt_print_state'] = '打印失败';
        } else if ($model['zt_print_state'] == 2) {
            $data['zt_print_state'] = date('Y-m-d H:i:s', $model['zt_print_time']);
        }
        if ($model['fk_print_state'] == 0) {
            $data['fk_print_state'] = '未打印';
        } else if ($model['fk_print_state'] == 1) {
            $data['fk_print_state'] = '打印失败';
        } else if ($model['fk_print_state'] == 2) {
            $data['fk_print_state'] = date('Y-m-d H:i:s', $model['fk_print_time']);
        }
        return $data;
    }
}
