<?php
namespace app\services;

use common\models\UploadFile;

class UploadFileService extends BaseModelService
{

    public $uploadType = null;

    public function __construct($uploadType)
    {
        $this->uploadType = $uploadType;
    }
    

    public function getModelClass()
    {
        return UploadFile::className();
    }

    public function getPreCondition()
    {
        return [
            'type' => $this->uploadType,
            'is_delete' => 0
        ];
    }

    public function record($dirId, $item)
    {
        $model = new UploadFile();
        $model['name'] = $item['name'];
        $model['url'] = $item['url'];
        $model['dir_id'] = $dirId;
        $model['created_time'] = time();
        $model['size'] = $item['size'];
        $model['type'] = $this->uploadType;
        $model['version'] = intval($model['version']) + 1;
        $model['youzan_syn_state'] = UploadFile::YOUZAN_SYN_STATE_NOT;
        $model['is_delete'] = 0;
        $model->save();
        return $model;
    }
    
    public function deleteById($id)
    {
        UploadFile::updateAll([
            'is_delete' => 1
        ], [
            'id' => $id
        ]);
        return $id;
    }
}
