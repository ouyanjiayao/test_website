<?php
namespace app\controllers;

use Yii;
use app\components\BaseController;

class CommonController extends BaseController
{

    public function actionJsConfig()
    {
        $jsConfig = require_once Yii::getAlias('@app/configs/js-config.php');
        $maxAge = 60 * 60 * 24;
        Yii::$app->getResponse()
            ->getHeaders()
            ->set('cache-control', "max-age={$maxAge}");
        return $this->renderPartial('js-config', [
            'jsConfig' => $jsConfig
        ]);
    }
}
