<?php
namespace app\controllers\system;

use Yii;
use app\components\BaseController;
use app\models\SystemUser;
use app\services\SystemUserService;
use common\components\ServiceException;
use yii\web\NotFoundHttpException;
use app\services\SystemAuthRuleService;

class UserController extends BaseController
{

    public $createRules = [
        [
            [
                'username',
                'password'
            ],
            'required'
        ],
        [
            'password',
            'string',
            'min' => SystemUser::PASSWORD_MIN_LENGTH,
            'max' => SystemUser::PASSWORD_MAX_LENGTH
        ]
    ];

    public $editRules = [
        [
            'password',
            'string',
            'min' => SystemUser::PASSWORD_MIN_LENGTH,
            'max' => SystemUser::PASSWORD_MAX_LENGTH
        ]
    ];

    public function behaviors()
    {
        return [
            [
                'class' => 'app\components\AccessControlFilter'
            ],
            [
                'class' => 'yii\filters\VerbFilter',
                'actions' => [
                    'delete' => [
                        'post'
                    ]
                ]
            ]
        ];
    }

    public $systemUserService = null;

    public $systemAuthRuleService = null;

    public function init()
    {
        $this->systemUserService = new SystemUserService();
        $this->systemAuthRuleService = new SystemAuthRuleService();
    }

    public function actionManage()
    {
        return $this->render('manage');
    }

    public function actionListData()
    {
        return $this->asJson($this->systemUserService->getListData());
    }

    public function actionCreate()
    {
        if (Yii::$app->request->isPost) {
            return $this->save();
        } else {
            return $this->renderSave();
        }
    }

    public function actionEdit()
    {
        $model = $this->systemUserService->getModel(Yii::$app->request->get('id'));
        if (! $model) {
            throw new NotFoundHttpException();
        }
        if (Yii::$app->request->isPost) {
            return $this->save($model);
        } else {
            return $this->renderSave($model);
        }
    }

    public function renderSave($model = null)
    {
        $saveForm = $this->systemUserService->getSaveForm($model);
        $saveForm['state'] = (string)$saveForm['state'];
        $saveFormDisable = $this->systemUserService->getSaveFormDisable($model);
        return $this->render('save', [
            'model' => $model,
            'saveForm' => $saveForm,
            'saveFormDisable' => $saveFormDisable
        ]);
    }

    public function save($model = null)
    {
        $success = false;
        try {
            $this->validateForm(! $model ? $this->createRules : $this->editRules);
            $this->systemUserService->save($model, Yii::$app->request->post());
            $success = true;
            $message = '保存成功';
        } catch (ServiceException $e) {
            $message = [
                SystemUserService::CODE_SAVE_FAIL => '保存失败',
                SystemUserService::CODE_SAVE_USERNAME_EXISTS => '当前用户名已存在'
            ][$e->getFailCode()];
        }
        return $this->asSuccess($success, $message);
    }

    public function actionSetAuth()
    {
        $model = $this->systemUserService->getModel($_GET['id']);
        if (! $model || $model['type'] == SystemUser::TYPE_SUPER_ADMIN || $model['id'] == Yii::$app->user->id) {
            throw new NotFoundHttpException();
        }
        if (Yii::$app->request->isPost) {
            $success = false;
            try {
                $this->systemUserService->setAuth($model, Yii::$app->request->post('rule_id'));
                $success = true;
                $message = '保存成功';
            } catch (ServiceException $e) {
                $message = '保存失败';
            }
            return $this->asSuccess($success, $message);
        } else{
            $authRuleList = $this->systemAuthRuleService->getBaseTreeList([
                'select' => 'name as label'
            ]);
            $authRuleList = $this->systemUserService->checkAuthRuleList($authRuleList);
            $userAuthAssigns = $this->systemUserService->getAuthAssigns($model['id']);
            return $this->render('set-auth', [
                'model' => $model,
                'authRuleList' => $authRuleList,
                'userAuthAssigns' => $userAuthAssigns
            ]);
        }
    }

    public function actionDelete()
    {
        $this->systemUserService->deleteById(Yii::$app->request->post('id'));
        return $this->asSuccess(true, "删除成功");
    }
}
