<?php
namespace app\controllers;

use Yii;
use app\components\BaseController;
use common\models\BaseConfig;
use common\components\ServiceException;
use app\services\SystemUserService;
use common\components\FormDataException;

class AuthController extends BaseController
{

    public $loginRules = [
        [
            'captcha',
            'captcha',
            'captchaAction' => '/auth/captcha'
        ]
    ];

    public function actions()
    {	 
		ob_clean();
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'width' => 100,
                'padding' => 0,
                'maxLength' => 4,
                'minLength' => 4,
                'testLimit' => 1
            ]
        ];
    }

    public $systemUserService = null;

    public function init()
    {
        $this->systemUserService = new SystemUserService();
    }

    public function actionLogin()
    {
        if (Yii::$app->request->isPost) {
            $success = false;
            try {
                $this->validateForm($this->loginRules);
                $model = $this->systemUserService->validateLogin(Yii::$app->request->post('username'), Yii::$app->request->post('password'));
                Yii::$app->user->login($model, 24 * 30 * 12);
                $success = true;
            } catch (FormDataException $e) {
                if ($e->getFormModel()->hasErrors('captcha')) {
                    $message = "验证码输入错误";
                } else {
                  throw $e;
               }
            } catch (ServiceException $e) {
                $message = [
                    SystemUserService::CODE_LOGIN_FAIL => '用户名或密码输入错误',
                    SystemUserService::CODE_LOGIN_LOCK => '当前用户已被禁用'
                ][$e->getFailCode()];
            }
            return $this->asSuccess($success, $message);
        } else {
            $baseConfig = BaseConfig::getAll([
                BaseConfig::KEY_WEB_NAME,
                BaseConfig::KEY_DEV_NAME
            ]);
            return $this->render('login', [
                'baseConfig' => $baseConfig
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        Yii::$app->user->loginRequired();
    }
}
