<?php
namespace app\controllers\goods;

use Yii;
use app\components\BaseController;
use app\services\GoodsAttrService;
use common\models\GoodsAttr;

class AttrController extends BaseController
{

    public function behaviors()
    {
        return [
            [
                'class' => 'app\components\AccessControlFilter'
            ],
            [
                'class' => 'yii\filters\VerbFilter',
                'actions' => [
                    'fast-create' => [
                        'post'
                    ]
                ]
            ]
        ];
    }

    public $goodsAttrService = null;

    public function init()
    {
        $this->goodsAttrService = new GoodsAttrService();
        $type = Yii::$app->request->get('type');
        if ($type) {
            $this->goodsAttrService->goodsType = $type;
        }
    }

    public function actionGetOptions()
    {
        return $this->asJson($this->goodsAttrService->getOptions('id', 'name', [
            'type' => GoodsAttr::TYPE_CUSTOM
        ]));
    }

    public function actionFastCreate()
    {
        $model = $this->goodsAttrService->fastCreate(Yii::$app->request->post('value'));
        return $this->asJson([
            'value' => $model['id'],
            'label' => $model['name']
        ]);
    }
}
