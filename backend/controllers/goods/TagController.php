<?php
namespace app\controllers\goods;

use Yii;
use app\components\BaseController;
use common\components\ServiceException;
use yii\web\NotFoundHttpException;
use app\services\GoodsTagService;
use common\models\GoodsTag;

class TagController extends BaseController
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
                    'delete' => [
                        'post'
                    ]
                ]
            ]
        ];
    }

    public $goodsTagService = null;

    public function init()
    {
        $this->goodsTagService = new GoodsTagService();
    }

    public function actionManage()
    {
        return $this->render('manage');
    }

    public function actionSaveFormData()
    {
        $nodes = $this->goodsCategoryService->getNodeOptions('id', 'name', Yii::$app->request->get('id'));
        return $this->asJson([
            'nodes' => $nodes
        ]);
    }

    public function actionCreate()
    {
        if (Yii::$app->request->isPost) {
            return $this->save(new GoodsTag());
        } else {
            return $this->renderSave();
        }
    }

    public function actionEdit()
    {
        $model = $this->goodsTagService->getModel(Yii::$app->request->get('id'));
        if (! $model) {
            throw new NotFoundHttpException();
        }
        if (Yii::$app->request->isPost) {
            return $this->save($model);
        } else {
            return $this->renderSave($model);
        }
    }

    public function renderSave($model = null)
    {
        $saveForm = $this->goodsTagService->getSaveForm($model);
        $info = $this->goodsTagService->getSaveInfo($model);
        return $this->render('save', [
            'model' => $model,
            'saveForm' => $saveForm,
            'info'=>$info
        ]);
    }

    public function save($model = null)
    {
        $success = false;
        try {
            $this->goodsTagService->save($model, Yii::$app->request->post());
            $success = true;
            $message = '保存成功';
        } catch (ServiceException $e) {
            $message = '保存失败';
        }
        return $this->asSuccess($success, $message);
    }

    public function actionListData()
    {
        return $this->asJson($this->goodsTagService->getBaseListData([
            'select' => 'id,name',
            'search' => [
                'type' => [
                    '=',
                    'type'
                ],
                'keyword' => [
                    'like',
                    'name'
                ]
            ]
        ]));
    }

    public function actionDelete()
    {
        $this->goodsTagService->deleteById(Yii::$app->request->post('id'));
        return $this->asSuccess(true, "删除成功");
    }
}
