<?php
namespace app\controllers\goods;

use Yii;
use app\components\BaseController;
use common\components\ServiceException;
use yii\web\NotFoundHttpException;
use app\services\GoodsTagService;
use app\services\GoodsService;
use common\models\Goods;
use app\services\GoodsCategoryService;
use common\models\GoodsTag;

class GoodsController extends BaseController
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

    public $goodsService = null;

    public $goodsCategoryService = null;

    public $goodsTagService = null;

    public function init()
    {
        $this->goodsService = new GoodsService();
        $this->goodsCategoryService = new GoodsCategoryService();
        $this->goodsTagService = new GoodsTagService();
        $type = Yii::$app->request->get('type');
        if ($type) {
            $this->goodsService->type = $type;
            $this->goodsCategoryService->type = $type;
        }
    }

    public function actionManage()
    {
        return $this->render('manage');
    }

    public function actionSearchFormData()
    {
        $this->goodsCategoryService->type = Goods::TYPE_DP;
        $dpCategoryNodes = $this->goodsCategoryService->getNodeOptions('id', 'name');
        $this->goodsCategoryService->type = Goods::TYPE_CP;
        $cpCategoryNodes = $this->goodsCategoryService->getNodeOptions('id', 'name');
        $this->goodsCategoryService->type = Goods::TYPE_TC;
        $tcCategoryNodes = $this->goodsCategoryService->getNodeOptions('id', 'name');
        $tags = $this->goodsTagService->getOptions('id', 'name',['type'=>1]);
        return $this->asJson([
            'categoryNodes' => [
                Goods::TYPE_DP => $dpCategoryNodes,
                Goods::TYPE_CP => $cpCategoryNodes,
                Goods::TYPE_TC => $tcCategoryNodes,
                
            ],
            'tags'=>$tags
        ]);
    }

    public function actionSaveFormData()
    {
        $result = [];
        $id = Yii::$app->request->get('id');
        $result['categoryNodes'] = $this->goodsCategoryService->getSaveNodeOptions();
        $result['tagOptions'] = $this->goodsTagService->getOptions('id', 'name', [
            'type' => GoodsTag::TYPE_MANUAL
        ]);
        $result['attrConfigForm'] = $this->goodsService->getAttrConfigForm($id);
        $result['attrAssignForm'] = $this->goodsService->getAttrAssignForm($id);
        if($this->goodsService->type == Goods::TYPE_DP)
        {
            $result['skuPriceForm'] = $this->goodsService->getSkuPriceForm($id);
            $result['sysAttrWeightForm'] = $this->goodsService->getSysAttrWeightForm($id);
            $result['sysAttrHandleForm'] = $this->goodsService->getSysAttrHandleForm($id);
            $result['sysAttrWashForm'] = $this->goodsService->getSysAttrWashForm($id);
        }
        $result['skuDetailForm'] = $this->goodsService->getSkuDetailForm($id);
        return $this->asJson($result);
    }

    public function actionCreate()
    {
        if (Yii::$app->request->isPost) {
            return $this->save(new Goods());
        } else {
            if (! key_exists(Yii::$app->request->get('type'), Goods::$typeMap)) {
                throw new NotFoundHttpException();
            }
            return $this->renderSave(null);
        }
    }

    public function actionEdit()
    {
        $model = $this->goodsService->getModel(Yii::$app->request->get('id'));
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
        if($model)
        {
            $this->goodsService->type = $model['type'];
        }
        $info = $this->goodsService->getSaveInfo($model);
        $saveForm = $this->goodsService->getSaveForm($model);
        $saveForm['state'] = (string) $saveForm['state'];
        $view = [
            Goods::TYPE_DP=>'save-dp',
            Goods::TYPE_CP=>'save-cp',
            Goods::TYPE_TC=>'save-tc'
        ][$this->goodsService->type];
        if(!$view)
        {
            throw new NotFoundHttpException();
        }
        return $this->render($view, [
            'model' => $model,
            'saveForm' => $saveForm,
            'info'=>$info,
            'type'=>$this->goodsService->type
        ]);
    }

    public function save($model = null)
    {
        $success = false;
        try {
            $this->goodsService->save($model, Yii::$app->request->post());
            $success = true;
            $message = '保存成功';
        } catch (ServiceException $e) {
            $message = '保存失败';
        }
        return $this->asSuccess($success, $message);
    }

    public function actionListData()
    {
        return $this->asJson($this->goodsService->getListData(Yii::$app->request->get()));
    }
    
    public function actionDpConfigListData()
    {
        return $this->asJson($this->goodsService->getDpConfigListData(Yii::$app->request->get()));
    }
    
    public function actionCpConfigListData()
    {
        return $this->asJson($this->goodsService->getCpConfigListData(Yii::$app->request->get()));
    }

    public function actionDelete()
    {
        $this->goodsService->deleteById(Yii::$app->request->post('id'));
        return $this->asSuccess(true, "删除成功");
    }
}
