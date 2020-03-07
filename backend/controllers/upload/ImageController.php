<?php
namespace app\controllers\upload;

use Yii;
use app\components\BaseController;
use app\services\UploadDirService;
use app\services\UploadFileService;
use common\librarys\UploadHelper;
use common\components\UploadException;
use yii\web\BadRequestHttpException;
use common\models\UploadFile;
use common\librarys\Validator;

class ImageController extends BaseController
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
                    'upload' => [
                        'post'
                    ],
                    'set' => [
                        'post'
                    ],
                    'record' => [
                        'post'
                    ],
                    'delete' => [
                        'post'
                    ]
                ]
            ]
        ];
    }

    public $uploadDirService = null;

    public $uploadFileService = null;

    public function init()
    {
        $this->uploadDirService = new UploadDirService();
        $this->uploadFileService = new UploadFileService(UploadFile::TYPE_IMAGE);
    }

    public function actionManage()
    {
        $dirs = $this->uploadDirService->getBaseTreeList([
            'select' => 'id as value,name as label'
        ]);
        return $this->render('manage', [
            'dirs' => $dirs
        ]);
    }

    public function actionListData()
    {
        return $this->asJson($this->uploadFileService->getBaseListData([
            'select' => 'id,name,url,dir_id',
            'search' => [
                'node_id' => [
                    '=',
                    'dir_id'
                ]
            ],
            'each' => function ($row) {
                $row['src_url'] = UploadHelper::getImageUrl($row['url'], null);
                $row['thumb_url'] = UploadHelper::getImageUrl($row['url'], UploadHelper::SIZE_MED);
                return $row;
            }
        ]));
    }

    public function actionUpListData()
    {
        return $this->asJson($this->uploadFileService->getBaseListData([
            'select' => 'id,name,url,dir_id',
            'search' => [
                'node_id' => [
                    '=',
                    'dir_id'
                ]
            ],
            'each' => function ($row) {
                $row['src_url'] = UploadHelper::getImageUrl($row['url'], null);
                $row['thumb_url'] = UploadHelper::getImageUrl($row['url'], UploadHelper::SIZE_MED);
                return $row;
            }
        ]));
    }

    public function actionDelete()
    {
        $this->uploadFileService->deleteById(Yii::$app->request->post('id'));
        return $this->asSuccess(true, "删除成功");
    }

    public function actionUpload()
    {
        set_time_limit(10);
        $uploadInfo = UploadHelper::saveAs('file');
        if (! $uploadInfo) {
            throw new UploadException();
        }
        return $this->asSuccess(true, null, [
            'name' => $uploadInfo['fileInstance']->name,
            'url' => $uploadInfo['url'],
            'size' => $uploadInfo['fileInstance']->size
        ]);
    }

    public function actionRecord()
    {
        $records = Yii::$app->request->post('record');
        if (Validator::isEmptyArray($records)) {
            throw new BadRequestHttpException();
        }
        $records = array_reverse($records);
        $list = [];
        foreach ($records as $record) {
            $model = $this->uploadFileService->record(Yii::$app->request->post('dir_id'), $record);
            $list[] = [
                'id' => $model['id'],
                'url' => $model['url'],
                'src_url' => UploadHelper::getImageUrl($model['url']),
                'thumb_url' => UploadHelper::getImageUrl($model['url'], UploadHelper::SIZE_MED)
            ];
        }
        return $this->asSuccess(true, null, [
            'list' => array_reverse($list)
        ]);
    }

    public function actionSet()
    {
        $model = $this->uploadFileService->getModel(Yii::$app->request->post('id'));
        if (! $model) {
            throw new BadRequestHttpException();
        }
        $setType = Yii::$app->request->post('set_type');
        if ($setType == 1) {
            $model['name'] = Yii::$app->request->post('name');
        } else if ($setType == 2) {
            $model['dir_id'] = Yii::$app->request->post('dir_id');
        }
        $model->save();
        return $this->asSuccess(true);
    }
}
