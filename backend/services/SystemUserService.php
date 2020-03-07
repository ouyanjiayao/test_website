<?php
namespace app\services;

use Yii;
use app\models\SystemUser;
use common\librarys\ArrayHelper;
use app\models\SystemUserAuthAssign;
use common\librarys\Validator;

class SystemUserService extends BaseModelService
{

    const CODE_LOGIN_FAIL = 1;

    const CODE_LOGIN_LOCK = 2;

    const CODE_EDIT_PASSWORD_FAIL = 1;

    const CODE_SAVE_FAIL = 1;

    const CODE_SAVE_USERNAME_EXISTS = 2;

    public $currentUser = null;

    public function __construct($currentUser = null)
    {
        if (! $currentUser) {
            $currentUser = [
                'id' => Yii::$app->user->id,
                'type' => Yii::$app->user->getUserType()
            ];
        }
        $this->currentUser = $currentUser;
    }

    public function getModelClass()
    {
        return SystemUser::class;
    }

    public function getModel($id)
    {
        $model = parent::getModel($id);
        if ($this->currentUser['type'] == SystemUser::TYPE_ADMIN && ($model['id'] != $this->currentUser['id'] && $model['create_by'] != $this->currentUser['id'])) {
            return null;
        }
        return $model;
    }

    public function validatePassword($password, $hash)
    {
        return Yii::$app->security->validatePassword($password, $hash);
    }

    public function hashPassword($password)
    {
        return Yii::$app->security->generatePasswordHash($password);
    }

    public function validateLogin($username, $password)
    {
        $model = SystemUser::find()->where([
            "username" => $username
        ])->one();
        if (! $model) {
            $this->fail(static::CODE_LOGIN_FAIL);
        }
        if ($model['type'] == SystemUser::TYPE_ADMIN && $model['state'] == SystemUser::STATE_DISABLE) {
            $this->fail(static::CODE_LOGIN_LOCK);
        }
        if (! $this->validatePassword($password, $model['password'])) {
            $this->fail(static::CODE_LOGIN_FAIL);
        }
        return $model;
    }

    public function editLoginPassword($user, $oldPassword, $newPassword)
    {
        if (! $this->validatePassword($oldPassword, $user['password'])) {
            $this->fail(static::CODE_EDIT_PASSWORD_FAIL);
        }
        $user['password'] = $this->hashPassword($newPassword);
        $user->save();
        return $user;
    }

    public function isUserNameExists($username, $id = null)
    {
        $query = SystemUser::find();
        if (isset($id)) {
            $query->andWhere("id != :id", [
                ":id" => $id
            ]);
        }
        $query->andWhere([
            'username' => $username
        ]);
        return $query->exists();
    }

    public function getListData()
    {
        $options = [
            'select' => 'id,username,state,created_time,remark,type',
            'search' => [
                'keyword' => [
                    'like',
                    'username'
                ]
            ],
            'each' => function ($row) {
                $row['created_time'] = $row['created_time'] ? date('Y/m/d H:i', $row['created_time']) : null;
                $row['state'] = [
                    'value' => $row['state'],
                    'label' => SystemUser::$stateMap[$row['state']]
                ];
                return $row;
            }
        ];
        if ($this->currentUser['type'] == SystemUser::TYPE_ADMIN) {
            $where = [
                'or',
                'id = :userId',
                'create_by =:userId'
            ];
            $params = [
                ':userId' => $this->currentUser['id']
            ];
            $options['where'] = $where;
            $options['params'] = $params;
        }
        return parent::getBaseListData($options);
    }

    public function getSaveForm($model = null)
    {
        $form = [
            'username' => '',
            'password' => '',
            'remark' => '',
            'state' => SystemUser::STATE_ENABLE
        ];
        if ($model) {
            $form = ArrayHelper::merge($form, $model->getAttributes([
                'username',
                'remark',
                'state'
            ]));
        }
        return $form;
    }

    public function getSaveFormDisable($model = null)
    {
        return [
            'username' => $model ? true : false,
            'state' => $model && ($model['type'] == SystemUser::TYPE_SUPER_ADMIN || $model['id'] == $this->currentUser['id']),
            'password' => $model && $model['id'] == $this->currentUser['id'],
            'remark' => $model && $model['id'] == $this->currentUser['id'] && $model['type'] == SystemUser::TYPE_ADMIN,
            'save' => $model && $model['id'] == $this->currentUser['id'] && $model['type'] == SystemUser::TYPE_ADMIN
        ];
    }

    public function save($model = null, $data)
    {
        $setPassword = false;
        $disableForm = $this->getSaveFormDisable($model);
        if (! $model) {
            $model = new SystemUser();
            if ($this->isUserNameExists($data['username'])) {
                $this->fail(static::CODE_SAVE_USERNAME_EXISTS);
            }
            $model['username'] = $data['username'];
            $model['type'] = SystemUser::TYPE_ADMIN;
            $model['created_time'] = time();
            $model['create_by'] = $this->currentUser['id'];
            $setPassword = true;
        } else {
            if ($this->currentUser['type'] == SystemUser::TYPE_ADMIN && ($model['id'] == $this->currentUser['id'] || $model['create_by'] != $this->currentUser['id'])) {
                $this->fail(static::CODE_SAVE_FAIL);
            }
            if (! $disableForm['password']) {
                $setPassword = true;
            }
        }
        if ($setPassword && ! Validator::isEmptyString($data['password'])) {
            $model['password'] = $this->hashPassword($data['password']);
        }
        if (! $disableForm['remark']) {
            $model['remark'] = $data['remark'];
        }
        if (! $disableForm['state'] && $model['type'] == SystemUser::TYPE_ADMIN) {
            $model['state'] = $data['state'];
        }
        $model->save();
    }

    public function deleteById($id)
    {
        $condition = [
            'and',
            [
                'id' => $id
            ],
            'type = :type'
        ];
        $params = [
            ':type' => SystemUser::TYPE_ADMIN
        ];
        if ($this->currentUser['type'] == SystemUser::TYPE_ADMIN) {
            $condition[] = 'create_by = :createBy';
            $params[':createBy'] = $this->currentUser['id'];
        }
        $models = SystemUser::find()->asArray()
            ->select('id')
            ->andWhere($condition, $params)
            ->all();
        $in = ArrayHelper::getColumn($models, 'id');
        SystemUser::deleteAll([
            'id' => $in
        ]);
        SystemUserAuthAssign::deleteAll([
            'user_id' => $in
        ]);
    }

    public function getAuthAssigns($userId)
    {
        $assigns = SystemUserAuthAssign::find()->asArray()
            ->andWhere([
            'user_id' => $userId
        ])
            ->all();
        return ArrayHelper::getColumn($assigns, 'rule_id');
    }

    public function checkAuthRuleList($authRuleList)
    {
        if ($this->currentUser['type'] == SystemUser::TYPE_SUPER_ADMIN) {
            return $authRuleList;
        }
        $userAuthAssigns = $this->getAuthAssigns($this->currentUser['id']);
        return $this->eachCheckAuthRuleList($authRuleList, $userAuthAssigns);
    }

    protected function eachCheckAuthRuleList($authRuleList, $userAuthAssigns)
    {
        $checkResult = [];
        foreach ($authRuleList as $authRule) {
            if ($authRule['children']) {
                $tmpCheckResult = $this->eachCheckAuthRuleList($authRule['children'], $userAuthAssigns);
                if ($tmpCheckResult) {
                    $authRule['children'] = $tmpCheckResult;
                }
            }
            $allow = true;
            if (! in_array($authRule['id'], $userAuthAssigns)) {
                $allow = false;
            }
            if ($allow) {
                $checkResult[] = $authRule;
            }
        }
        return $checkResult;
    }

    public function setAuth($model, $ruleIds)
    {
        if ($model['type'] == SystemUser::TYPE_SUPER_ADMIN) {
            return;
        }
        if ($this->currentUser['type'] == SystemUser::TYPE_ADMIN) {
            if ($model['id'] == $this->currentUser['id']) {
                $this->fail(static::CODE_SAVE_FAIL);
            }
            if ($model['create_by'] != $this->currentUser['id']) {
                $this->fail(static::CODE_SAVE_FAIL);
            }
        }
        SystemUserAuthAssign::deleteAll([
            'user_id' => $model['id']
        ]);
        if (! Validator::isEmptyArray($ruleIds)) {
            $userAuthAssigns = [];
            if ($this->currentUser['type'] == SystemUser::TYPE_ADMIN) {
                $userAuthAssigns = $this->getAuthAssigns($this->currentUser['id']);
            }
            $insert = [];
            foreach ($ruleIds as $id) {
                if ($this->currentUser['type'] == SystemUser::TYPE_SUPER_ADMIN) {
                    $insert[] = [
                        $model['id'],
                        $id
                    ];
                } else if (in_array($id, $userAuthAssigns)) {
                    $insert[] = [
                        $model['id'],
                        $id
                    ];
                }
            }
            if ($insert) {
                Yii::$app->db->createCommand()
                    ->batchInsert('{{%system_user_auth_assign}}', [
                    'user_id',
                    'rule_id'
                ], $insert)
                    ->execute();
            }
        }
    }
}
