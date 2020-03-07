<?php
namespace app\controllers;

use Yii;
use app\components\BaseController;
use common\components\ServiceException;
use app\services\SystemUserService;
use app\models\SystemUser;
use yii\helpers\Json;
use common\librarys\DbHelper;

class SiteController extends BaseController
{

    public $editLoginPassowrdRules = [
        [
            [
                'old_password',
                'new_password'
            ],
            'required'
        ],
        [
            'new_password',
            'string',
            'min' => SystemUser::PASSWORD_MIN_LENGTH,
            'max' => SystemUser::PASSWORD_MAX_LENGTH
        ]
    ];

    public function behaviors()
    {
        return [
            [
                'class' => 'app\components\AuthFilter'
            ]
        ];
    }

    public $systemUserService = null;

    public function init()
    {
        $this->systemUserService = new SystemUserService();
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionEditLoginPassword()
    {
        if (Yii::$app->request->isPost) {
            $success = false;
            try {
                $this->validateForm($this->editLoginPassowrdRules);
                $model = $this->systemUserService->editLoginPassword(Yii::$app->user->getIdentity(), Yii::$app->request->post('old_password'), Yii::$app->request->post('new_password'));
                $success = true;
                $message = '保存成功';
            } catch (ServiceException $e) {
                $message = [
                    SystemUserService::CODE_EDIT_PASSWORD_FAIL => '旧密码输入不正确'
                ][$e->getFailCode()];
            }
            return $this->asSuccess($success, $message);
        } else {
            return $this->render('edit-login-password');
        }
    }

    public function actionTest()
    {
        $rows = DbHelper::db()->executeQuery('select * from tbl_goods_tc_config where id = 11');
        $content = Json::decode($rows[0]['content']);
        $test = array(
            0 => array(
                'cp_name' => '炒四季豆',
                'count' => 1,
                'dp_config' => array(
                    0 => array(
                        'dp_name' => '四季豆',
                        'count' => 1,
                        'desc' => '重量:500g,加工方式:抽丝切段'
                    ),
                    1 => array(
                        'dp_name' => '蒜头',
                        'count' => 1,
                        'desc' => '重量:20g,加工方式:掰粒'
                    )
                )
            ),
            1 => array(
                'cp_name' => '红烧鲫鱼',
                'count' => 1,
                'dp_config' => array(
                    0 => array(
                        'dp_name' => '鲫鱼',
                        'count' => 1,
                        'desc' => '重量:500g,加工方式:开花'
                    ),
                    1 => array(
                        'dp_name' => '姜',
                        'count' => 1,
                        'desc' => '重量:20g,加工方式:切丝'
                    ),
                    2 => array(
                        'dp_name' => '小米红辣椒',
                        'count' => 1,
                        'desc' => '重量:20g,加工方式:切丝'
                    ),
                    3 => array(
                        'dp_name' => '蒜头',
                        'count' => 1,
                        'desc' => '重量:20g,加工方式:掰粒'
                    ),
                    array(
                        'dp_name' => '葱花',
                        'count' => 1,
                        'desc' => '重量:20g'
                    )
                )
            ),
            2 => array(
                'cp_name' => '山药猪脚筒汤',
                'count' => 1,
                'dp_config' => array(
                    0 => array(
                        'dp_name' => '铁棍山药',
                        'count' => 1,
                        'desc' => '重量:500g,加工方式:切块'
                    ),
                    1 => array(
                        'dp_name' => '猪脚筒',
                        'count' => 1,
                        'desc' => '重量:250g'
                    ),
                    array(
                        'dp_name' => '芫须',
                        'count' => 1,
                        'desc' => '重量:20g,加工方式:切段'
                    )
                )
            )
        );
        print_r(Json::encode($test));
    }
}
