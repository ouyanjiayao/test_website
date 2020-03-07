<?php
namespace app\components;

use Yii;
use yii\web\ForbiddenHttpException;

class AccessControlFilter extends AuthFilter
{

    public $requireSuperAdmin = false;

    public function beforeAction($action)
    {
        $result = parent::beforeAction($action);
        if ($result) {
            if (($this->requireSuperAdmin && ! Yii::$app->user->isSuperAdmin()) || ! Yii::$app->user->checkAccess(Yii::$app->controller->route)) {
                throw new ForbiddenHttpException();
            }
        }
        return $result;
    }
}
