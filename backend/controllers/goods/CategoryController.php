<?php
namespace app\controllers\goods;

use Yii;
use app\components\BaseController;
use common\components\ServiceException;
use yii\web\NotFoundHttpException;
use app\services\GoodsCategoryService;
use common\models\GoodsCategory;
use app\services\UploadDirService;
use common\models\Goods;
use common\models\UploadDir;

class CategoryController extends BaseController
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

    public $goodsCategoryService = null;

    public $uploadDirService = null;

    public function init()
    {
        $this->goodsCategoryService = new GoodsCategoryService();
        $this->uploadDirService = new UploadDirService();
        $type = Yii::$app->request->get('type');
        if ($type)
            $this->goodsCategoryService->type = $type;
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
            return $this->save(new GoodsCategory());
        } else {
            return $this->renderSave();
        }
    }

    public function actionEdit()
    {
        $model = $this->goodsCategoryService->getModel(Yii::$app->request->get('id'));
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
        $form = $this->goodsCategoryService->getSaveForm($model);
        $form['parent_id'] = (string) $form['parent_id'];
        $type = null;
        if ($model) {
            $type = $model['type'];
        }
        return $this->render('save', [
            'model' => $model,
            'form' => $form,
            'type' => $type
        ]);
    }

    public function save($model = null)
    {
        $success = false;
        try {
            $data = Yii::$app->request->post();
            $this->goodsCategoryService->save($model, $data);
            $dirTypeMap = [
                Goods::TYPE_DP => UploadDir::TYPE_GOODS_DP,
                Goods::TYPE_CP => UploadDir::TYPE_GOODS_CP,
                Goods::TYPE_TC => UploadDir::TYPE_GOODS_TC
            ];
            $dirRootIdMap = [
                Goods::TYPE_DP => UploadDir::ID_DP,
                Goods::TYPE_CP => UploadDir::ID_CP,
                Goods::TYPE_TC => UploadDir::ID_TC
            ];
            $parentId = $dirRootIdMap[$model['type']];
            $dirType = $dirTypeMap[$model['type']];
            if ($model['parent_id']) {
                $pGoodsCategory = GoodsCategory::find()->asArray()
                    ->andWhere([
                    'id' => $model['parent_id']
                ])
                    ->one();
                $pUploadDir = UploadDir::find()->asArray()
                    ->andWhere([
                    'type' => $dirType,
                    'create_id' => $pGoodsCategory['id']
                ])
                    ->one();
                $parentId = $pUploadDir['id'];
            }
            $this->uploadDirService->setDirByType($model['name'], $dirType, $model['id'], $data['sort'], $parentId);
            $success = true;
            $message = '保存成功';
        } catch (ServiceException $e) {
            $message = '保存失败';
        }
        return $this->asSuccess($success, $message);
    }

    public function actionListData()
    {
        return $this->asJson($this->goodsCategoryService->getBaseTreeList());
    }

    public function actionDelete()
    {
        $this->goodsCategoryService->deleteById(Yii::$app->request->post('id'));
        return $this->asSuccess(true, "删除成功");
    }
}
