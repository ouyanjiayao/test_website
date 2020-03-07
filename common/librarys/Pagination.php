<?php
namespace common\librarys;

class Pagination extends \yii\data\Pagination
{
    public function __construct($config = [])
    {
        $config['validatePage'] = false;
        parent::__construct($config);
    }
    
    public function hasNext(){
        return  ($this->getPage() + 1) < $this->getPageCount();
    }
    
}
