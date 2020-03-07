<?php
namespace app\services;

use common\models\YouzanSynLog;

class YouzanSynLogService extends BaseModelService
{
    public function getModelClass()
    {
        return YouzanSynLog::class;
    }
}
