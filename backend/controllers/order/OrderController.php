<?php
namespace app\controllers\order;

use Yii;
use app\components\BaseController;
use app\services\YouzanOrderService;
use common\models\YouzanOrder;
use yii\web\NotFoundHttpException;

class OrderController extends BaseController
{

    public function behaviors()
    {
        return [
            [
                'class' => 'app\components\AccessControlFilter'
            ]
        ];
    }

    public $youzanOrderService = null;

    public function init()
    {
        $this->youzanOrderService = new YouzanOrderService();
    }

    public function actionManage()
    {
        return $this->render('manage');
    }

    public function actionListData()
    {
        return $this->asJson($this->youzanOrderService->getBaseListData([
            'select' => 'id,order_num,push_content,tid,order_state,zt_print_state,zt_print_time,fk_print_state,fk_print_time',
            'search' => [
                'keyword' => [
                    'like',
                    'tid,order_num'
                ]
            ],
            'each' => function ($row) {
                $row['order_num'] = $row['order_num']?$row['order_num']:'未生成';
                $row['order_state'] = YouzanOrder::$orderStateMap[$row['order_state']];
                if ($row['zt_print_state'] == 0) {
                    $row['zt_print_state'] = '未打印';
                } else if ($row['zt_print_state'] == 1) {
                    $row['zt_print_state'] = '打印失败';
                } else if ($row['zt_print_state'] == 2) {
                    $row['zt_print_state'] = date('Y-m-d H:i:s', $row['zt_print_time']);
                }
                if ($row['fk_print_state'] == 0) {
                    $row['fk_print_state'] = '未打印';
                } else if ($row['fk_print_state'] == 1) {
                    $row['fk_print_state'] = '打印失败';
                } else if ($row['fk_print_state'] == 2) {
                    $row['fk_print_state'] = date('Y-m-d H:i:s', $row['fk_print_time']);
                }
                return $row;
            }
        ]));
    }

    public function actionEdit()
    {
        $model = $this->youzanOrderService->getModel(Yii::$app->request->get('id'));
        if (! $model) {
            throw new NotFoundHttpException();
        }
        if (Yii::$app->request->isPost) {
            $this->youzanOrderService->save($model,Yii::$app->request->post());
            return $this->asSuccess(true, '保存成功');
        } else {
            $infoData = $this->youzanOrderService->getInfoData($model);
            return $this->render('save', [
                'model' => $model,
                'infoData'=>$infoData
            ]);
        }
    }
}
