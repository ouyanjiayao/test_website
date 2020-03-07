<?php
namespace common\librarys;

use Yii;
use Youzan\Open\Token;
use Youzan\Open\Client;

class YouzanApi
{
    public static function getAccessToken()
    {
        $accessToken = Yii::$app->redis->get('youzan_access_token_v1');
        if(!$accessToken)
        {
            $accessToken = (new Token(YOUZAN_CLIENT_ID, YOUZAN_SECRET))->getToken('silent',['kdt_id'=>YOUZAN_KID]);
            $accessToken = $accessToken['access_token'];
            Yii::$app->redis->set('youzan_access_token_v1',$accessToken);
            Yii::$app->redis->expire('youzan_access_token_v1',60*60*1);
        }
        return $accessToken;
    }
    
    public static function getApiClient(){
        $accessToken = self::getAccessToken();
        $client = new Client($accessToken);
        return $client;
    }
    
    public static function validateSign($msg,$sign)
    {
        $signString = YOUZAN_CLIENT_ID . "" . $msg . "" . YOUZAN_SECRET;
        $signString = md5($signString);
        if ($signString != $sign) {
            return false;
        }
        return true;
    }
}
