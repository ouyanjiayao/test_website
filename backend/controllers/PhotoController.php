<?php
namespace app\controllers;

use Yii;
use app\components\BaseController;
use common\models\UploadFile;
use common\librarys\UploadHelper;
use common\librarys\Pagination;

class PhotoController extends \common\components\BaseController
{

    public function actionIndex()
    {
        echo $this->renderPartial('index');
    }

    public function actionListData()
    {
        $pagination = new Pagination();
        $query = UploadFile::find()->asArray()->select('url');
        $dir_id = Yii::$app->request->get('dir_id');
        if (! $dir_id) {
            $dir_id = [
                86,
                87,
                88
            ];
        }
        $query->andWhere([
            'dir_id' => $dir_id,
            'is_delete' => 0
        ]);
        $pagination->totalCount = (int) $query->count();
        $rows = $query->orderBy('id desc')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $images = [];
        foreach ($rows as &$row) {
            $images[] = UploadHelper::getImageUrl($row['url'], '750x0');
        }
        $this->asJson([
            'rows' => $images,
            'hasNext' => $pagination->hasNext()
        ]);
    }
}
