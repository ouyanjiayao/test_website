<?php
namespace common\components;

use yii\web\BadRequestHttpException;

class ServiceException extends BadRequestHttpException
{
    public function __construct($failCode = null,$params = [])
    {
        $this->params = $params;
        $this->failCode = $failCode;
        parent::__construct();
    }
    
    private $failCode = null;
    
    public function getFailCode()
    {
        return $this->failCode;
    }
    
    private $params = [];
    
    public function getParams()
    {
        return $this->params;
    }

}
