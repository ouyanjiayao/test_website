<?php
namespace app\services;

use common\librarys\ArrayHelper;
use common\models\GoodsCategory;
use common\models\Goods;
use common\models\GoodsTag;
use common\models\GoodsTagSystemMap;
use common\models\UploadDir;

class GoodsCategoryService extends BaseTreeService
{

    public $type = null;

    public function getModelClass()
    {
        return GoodsCategory::className();
    }

    public function getPreCondition()
    {
        if ($this->type) {
            return [
                'type' => $this->type
            ];
        }
        return [];
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

    public function getSaveNodeOptions()
    {
        $query = GoodsCategory::find()->asArray()
            ->select('id,parent_id,sort,id as value,name as label')
            ->orderBy('sort asc,id desc');
        if($this->type)
        {
            $query->andWhere(['type'=>$this->type]);
        }
        $rows = $query->all();
        foreach ($rows as &$row) {
            $uploadDir = UploadDir::find()->asArray()
                ->select('id')
                ->andWhere([
                'create_id' => $row['id']
            ])
                ->one();
            if ($uploadDir) {
                $row['upload_dir_id'] = $uploadDir['id'];
            }
        }
        $rows = ArrayHelper::toTree($rows, 0, 'id', 'parent_id', 'sort', "children");
        return $rows;
    }

    public function save($model, $data)
    {
        if ($model->isNewRecord) {
            $model['type'] = $this->type;
        }
        $model->setAttributes(ArrayHelper::persist([
            'name'
        ], $data), false);
        $model = $this->saveNode($model, $data['sort'], $data['parent_id']);
        $this->autoSetTag($model);
    }

    public function autoSetTag($model)
    {
        $tag = GoodsTag::find()->andWhere([
            'system_create_id' => $model['id']
        ])->one();
        if (! $tag) {
            $tag = new GoodsTag();
        }
        $categoryNodes = array_reverse($this->traversalNode($model['id'], 'name'));
        $tag['name'] = $this->getTagName($model['type'], $categoryNodes);
        $tag['type'] = GoodsTag::TYPE_SYSTEM;
        $tag['is_delete'] = 0;
        $tag['system_create_id'] = $model['id'];
        $tag['version'] = intval($tag['version']) + 1;
        $tag['youzan_syn_state'] = GoodsTag::YOUZAN_SYN_STATE_NOT;
        $tag->save();
        GoodsTagSystemMap::deleteAll([
            'tag_id' => $tag['id']
        ]);
        foreach ($categoryNodes as $node) {
            $map = new GoodsTagSystemMap();
            $map['tag_id'] = $tag['id'];
            $map['category_id'] = $node['id'];
            $map->save();
        }
        $updateMaps = GoodsTagSystemMap::find()->asArray()
            ->andWhere([
            'category_id' => $model['id']
        ])
            ->andWhere([
            '!=',
            'tag_id',
            $tag['id']
        ])
            ->all();
        foreach ($updateMaps as $updateMap) {
            $updateGoodsTag = GoodsTag::find()->andWhere([
                'id' => $updateMap['tag_id']
            ])->one();
            if ($updateGoodsTag) {
                $updateNodes = array_reverse($this->traversalNode($updateGoodsTag['system_create_id'], 'name'));
                $updateGoodsTag['name'] = $this->getTagName($model['type'], $updateNodes);
                $updateGoodsTag['version'] = intval($updateGoodsTag['version']) + 1;
                $updateGoodsTag['youzan_syn_state'] = GoodsTag::YOUZAN_SYN_STATE_NOT;
                $updateGoodsTag->save();
            }
        }
    }

    public function getTagName($type, $categoryNodes)
    {
        $names = ArrayHelper::getColumn($categoryNodes, 'name');
        return [
            Goods::TYPE_DP => 'DP.',
            Goods::TYPE_CP => 'CP.',
            Goods::TYPE_TC => 'TC.'
        ][$type] . implode(".", $names);
    }

    public function deleteById($id)
    {
        $ids = parent::deleteById($id);
        UploadDir::deleteAll([
            'create_id' => $ids
        ]);
        GoodsTag::updateAll([
            'is_delete' => 1
        ], [
            'system_create_id' => $ids
        ]);
        GoodsTagSystemMap::deleteAll([
            'category_id' => $ids
        ]);
        return $ids;
    }
}
