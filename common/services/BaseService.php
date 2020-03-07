<?php
namespace common\services;

use common\components\ServiceException;

abstract class BaseService
{

    protected function fail($failCode, $params = [])
    {
        throw new ServiceException($failCode, $params);
    }

}
