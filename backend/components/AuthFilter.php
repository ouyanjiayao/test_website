<?php
namespace app\components;

use Yii;
use yii\base\ActionFilter;

class AuthFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        $result = true;
        if (Yii::$app->user->isGuest) {
            $result = false;
            Yii::$app->user->loginRequired();
        }
        return $result;
    }
}
