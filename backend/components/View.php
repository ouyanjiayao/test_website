<?php
namespace app\components;

use Yii;
use common\models\BaseConfig;
use common\librarys\Validator;
use common\librarys\Url;

class View extends \yii\web\View
{

    public $includeBaseAsset = true;

    public $frameConfigs = [];

    public function setFrameConfigs($configs)
    {
        $this->frameConfigs = $configs;
    }

    protected function generateMenu($menus, $level = 0)
    {
        $result = [];
        foreach ($menus as &$menu) {
            if ($menu['sub']) {
                $menu['sub'] = $this->generateMenu($menu['sub'], $level + 1);
            }
            if (! $menu['sub'] && Validator::isEmptyString($menu['route'])) {
                continue;
            }
            if (! Validator::isEmptyString($menu['route'])) {
                $menu['url'] = Url::toRoute('/'.$menu['route']);
            }
            if ($menu['sub'] || $menu['allow'] || Yii::$app->user->checkAccess($menu['route'])) {
                $result[] = $menu;
            }
        }
        return $result;
    }

    public function getMenus()
    {
        $menus = $this->generateMenu(require_once Yii::getAlias('@app/configs/menus.php'));
        return $menus;
    }

    public function beginFrameContent($frameConfigs = [])
    {
        $baseConfig = BaseConfig::getAll([
            BaseConfig::KEY_WEB_NAME,
            BaseConfig::KEY_DEV_NAME
        ]);
        $menus = $this->getMenus();
        $this->beginContent('@app/views/layouts/frame.php', [
            'baseConfig' => $baseConfig,
            'menus' => $menus,
            'paths' => $frameConfigs['paths'],
            'activeMenu' => $frameConfigs['activeMenu']
        ]);
    }

}
