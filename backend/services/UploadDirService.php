<?php
namespace app\services;

use common\models\UploadDir;
use common\librarys\ArrayHelper;

class UploadDirService extends BaseTreeService
{

    public function getModelClass()
    {
        return UploadDir::className();
    }

    public function getSaveForm($model = null)
    {
        $form = [
            'name' => '',
            'parent_id' => null
        ];
        if ($model) {
            $form = ArrayHelper::merge($form, $model->getAttributes([
                'name',
                'parent_id'
            ]));
        }
        return $form;
    }

    public function save($model, $data)
    {
        $model->setAttributes(ArrayHelper::persist([
            'name'
        ], $data), false);
        $model['created_time'] = time();
        $model['type'] = UploadDir::TYPE_CUSTOM;
        $model = $this->saveNode($model, $data['sort'], $data['parent_id']);
    }

    public function setDirByType($name, $type, $createId, $sort, $parentId)
    {
        $model = UploadDir::find()->andWhere([
            'type' => $type,
            'create_id' => $createId
        ])->one();
        if (! $model) {
            $model = new UploadDir();
            $model['type'] = $type;
            $model['created_time'] = time();
            $model['create_id'] = $createId;
        }
        $model['name'] = $name;
        $model = $this->saveNode($model, $sort, $parentId);
        return $model;
    }

    public function deleteById($id)
    {
        return $this->deleteAll([
            $this->idColumn => $id,
            'type' => UploadDir::TYPE_CUSTOM
        ]);
    }
}
