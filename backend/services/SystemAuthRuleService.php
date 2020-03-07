<?php
namespace app\services;

use app\models\SystemAuthRule;

class SystemAuthRuleService extends BaseTreeService
{

    public $maxLevel = 3;

    public function getModelClass()
    {
        return SystemAuthRule::className();
    }

}
