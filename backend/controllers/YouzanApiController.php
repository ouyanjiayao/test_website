<?php
namespace app\controllers;

use app\components\BaseController;

class YouzanApiController extends BaseController
{

    public function actionConfirmLogistics()
    {
        $accessToken = (new \Youzan\Open\Token(YOUZAN_CLIENT_ID, YOUZAN_SECRET))->getToken($type, $keys);
        var_dump($accessToken);
    }

}
