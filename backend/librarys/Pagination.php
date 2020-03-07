<?php
namespace app\librarys;

use Yii;

class Pagination extends \common\librarys\Pagination
{
    public function __construct($config = [])
    {
        $pageSize = intval(Yii::$app->request->get('size'));
        if($pageSize<=0)
        {
            $pageSize = 20;
        }
        else if($pageSize>50)
        {
            $pageSize = 50;
        }
        $config['pageSize'] = $pageSize;
        parent::__construct($config);
    }
}
