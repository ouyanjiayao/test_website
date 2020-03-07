<?php
namespace app\controllers\dev;

use Yii;
use app\components\BaseController;

class AuthRuleController extends BaseController
{

    public function actionManage()
    {
        return $this->render('manage');
    }
}
