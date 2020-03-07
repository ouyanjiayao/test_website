<?php
namespace app\controllers\system;

use app\components\BaseController;
use app\services\YouzanSynLogService;
use common\models\GoodsAttrConfig;
use common\models\YouzanSynLog;

class YouzanSynLogController extends BaseController
{

    public function behaviors()
    {
        return [
            [
                'class' => 'app\components\AccessControlFilter'
            ]
        ];
    }

    public $youzanSynLogService = null;

    public function init()
    {
        $this->youzanSynLogService = new YouzanSynLogService();
    }

    public function actionManage()
    {
        return $this->render('manage');
    }

    public function actionListData()
    {
        return $this->asJson($this->youzanSynLogService->getBaseListData([
            'select' => 'id,response_content,created_time,api_name,syn_state',
            'each' => function ($row) {
                $row['created_time'] = date('Y/m/d H:i',$row['created_time']);
                $row['syn_state'] = YouzanSynLog::$youzanySynStateMap[$row['syn_state']];
                return $row;
            }
        ]));
    }
    public function actionRestart()
    {
        exec("taskkill /f /im py.exe"); 
		//exec("start C:/Users/Administrator/Desktop/启动/restart.bat");
        print_r('已关闭服务');
		print_r('等待重启...');
        exit();
    }
}  
