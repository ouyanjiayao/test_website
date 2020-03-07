<?php
namespace app\components;

use Yii;
use app\models\SystemUser;
use app\models\SystemUserAuthAssign;
use yii\helpers\Json;
use common\librarys\Validator;
use app\models\SystemAuthRule;
use common\librarys\ArrayHelper;
use common\librarys\Url;

class User extends \yii\web\User
{

    protected $checkAccessCache = [];

    public function init()
    {
        parent::init();
        $this->on(static::EVENT_BEFORE_LOGIN, [
            $this,
            'onBeforeLogin'
        ]);
        $this->on(static::EVENT_AFTER_LOGIN, [
            $this,
            'onAfterLogin'
        ]);
    }

    public function onBeforeLogin($identity)
    {
        $model = $identity->identity;
        $model['auth_key'] = Yii::$app->security->generateRandomString(32);
        $model->save();
    }

    public function onAfterLogin($identity)
    {
        $model = $identity->identity;
        if ($model['type'] == SystemUser::TYPE_ADMIN) {
            $allowRoutes = [];
            $denyRoutes = [];
            $authRules = SystemAuthRule::find()->asArray()
                ->select('id,route')
                ->all();
            $authRules = ArrayHelper::map($authRules, 'id', 'route');
            $assigns = SystemUserAuthAssign::find()->asArray()
                ->select('rule_id')
                ->andWhere([
                'user_id' => $model['id']
            ])
                ->all();
            $allowRuleIds = [];
            foreach ($assigns as $assign) {
                $allowRuleIds[] = $assign['rule_id'];
                if (key_exists($assign['rule_id'], $authRules)) {
                    $routes = $authRules[$assign['rule_id']];
                    if (! Validator::isEmptyString($routes)) {
                        $routes = Json::decode($routes);
                        foreach ($routes as $route) {
                            if (! in_array($route, $allowRoutes, true))
                                $allowRoutes[] = $route;
                        }
                    }
                }
            }
            foreach ($authRules as $id => $routes) {
                if (! in_array($id, $allowRuleIds)) {
                    if (! Validator::isEmptyString($routes)) {
                        $routes = Json::decode($routes);
                        foreach ($routes as $route) {
                            if (! in_array($route, $denyRoutes, true))
                                $denyRoutes[] = $route;
                        }
                    }
                }
            }
            Yii::$app->getSession()->set('system_user_deny_routes', $denyRoutes);
            Yii::$app->getSession()->set('system_user_allow_routes', $allowRoutes);
        }
        Yii::$app->getSession()->set('system_user_type', $model['type']);
        Yii::$app->getSession()->set('system_user_name', $model['username']);
    }

    public function getDenyRoutes()
    {
        return Yii::$app->getSession()->get('system_user_deny_routes');
    }

    public function getAllowRoutes()
    {
        return Yii::$app->getSession()->get('system_user_allow_routes');
    }

    public function getUserType()
    {
        return Yii::$app->getSession()->get('system_user_type');
    }

    public function getUserName()
    {
        return Yii::$app->getSession()->get('system_user_name');
    }

    public function checkAccess($route)
    {
        if ($this->isGuest) {
            return false;
        }
        if (Yii::$app->getSession()->get('system_user_type') == SystemUser::TYPE_SUPER_ADMIN) {
            return true;
        }
        $route = Url::normalizeRoute($route);
        if (key_exists($route, $this->checkAccessCache)) {
            return $this->checkAccessCache[$route];
        }
        $result = true;
        $allowRoutes = Yii::$app->getSession()->get('system_user_allow_routes');
        if (! in_array($route, $allowRoutes, true)) {
            $result = false;
        }
        $this->checkAccessCache[$route] = $result;
        return $result;
    }

    public function isSuperAdmin()
    {
        if ($this->isGuest) {
            return false;
        }
        return $this->getUserType() == SystemUser::TYPE_SUPER_ADMIN;
    }
}
