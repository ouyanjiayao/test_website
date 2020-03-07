<?php
namespace app\controllers\upload;

use Yii;
use app\components\BaseController;
use app\services\UploadDirService;
use common\components\ServiceException;
use common\models\UploadDir;
use yii\web\NotFoundHttpException;

class DirController extends BaseController
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

    public $uploadDirService = null;

    public function init()
    {
        $this->uploadDirService = new UploadDirService();
    }

    public function actionManage()
    {
        return $this->render('manage');
    }

    public function actionSaveFormData()
    {
        $nodes = $this->uploadDirService->getNodeOptions('id', 'name', Yii::$app->request->get('id'));
        return $this->asJson([
            'nodes' => $nodes
        ]);
    }

    public function actionCreate()
    {
        if (Yii::$app->request->isPost) {
            return $this->save(new UploadDir());
        } else {
            return $this->renderSave();
        }
    }
    
    public function actionEdit()
    {
        $model = $this->uploadDirService->getModel(Yii::$app->request->get('id'));
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
        $form = $this->uploadDirService->getSaveForm($model);
        $form['parent_id'] = (string)$form['parent_id'];
        return $this->render('save', [
            'model' => $model,
            'form' => $form
        ]);
    }

    public function save($model = null)
    {
        $success = false;
        try {
            $this->uploadDirService->save($model, Yii::$app->request->post());
            $success = true;
            $message = '保存成功';
        } catch (ServiceException $e) {
            $message = '保存失败';
        }
        return $this->asSuccess($success, $message);
    }

    public function actionListData()
    {
        return $this->asJson($this->uploadDirService->getBaseTreeList());
    }
    
    public function actionUpOptionsData(){
        $dirs = $this->uploadDirService->getBaseTreeList([
            'select' => 'id as value,name as label'
        ]);
        return $this->asJson($dirs);
    }

    public function actionDelete()
    {
        $this->uploadDirService->deleteById(Yii::$app->request->post('id'));
        return $this->asSuccess(true, "删除成功");
    }
}
