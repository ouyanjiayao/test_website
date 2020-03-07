<?php
namespace app\services;

use Yii;
use common\models\Goods;
use common\librarys\ArrayHelper;
use common\librarys\Validator;
use common\models\GoodsCategoryAssign;
use common\models\GoodsTagAssign;
use common\librarys\UploadHelper;
use yii\helpers\Json;
use common\models\GoodsAttrConfig;
use common\models\GoodsAttrAssign;
use common\models\GoodsAttrItemAssign;
use common\models\GoodsAttr;
use common\models\GoodsAttrItem;
use common\models\GoodsSkuPrice;
use common\models\GoodsSkuSystem;
use common\models\GoodsSkuDetail;
use common\models\GoodsCpConfig;
use common\models\GoodsTcConfig;

class GoodsService extends BaseModelService
{

    public $type = null;

    public function getModelClass()
    {
        return Goods::class;
    }

    public function getPreCondition()
    {
        $condition = [
            'is_delete' => 0
        ];
        if ($this->type) {
            $condition['type'] = $this->type;
        }
        return $condition;
    }

    public function getListData($queryParams)
    {
        $query = $this->getQuery();
        if (! Validator::isEmptyString($queryParams['category_id'])) {
            $joinWhere = [
                'category_id = :categoryId'
            ];
            $joinParams = [
                ':categoryId' => $queryParams['category_id']
            ];
            if ($this->type) {
                $joinWhere[] = 'type = :type';
                $joinParams[':type'] = $this->type;
            }
            $joinWhere = implode(" and ", $joinWhere);
            $query->innerJoin("(select goods_id FROM {{%goods_category_assign}} where $joinWhere group by goods_id) as t2", '{{%goods}}.id = t2.goods_id', $joinParams);
        }
        if (! Validator::isEmptyString($queryParams['tag_id'])) {
            $joinWhere = [
                'tag_id = :tagId'
            ];
            $joinParams = [
                ':tagId' => $queryParams['tag_id']
            ];
            if ($this->type) {
                $joinWhere[] = 'type = :type';
                $joinParams[':type'] = $this->type;
            }
            $joinWhere = implode(" and ", $joinWhere);
            $query->innerJoin("(select goods_id FROM {{%goods_tag_assign}} where $joinWhere group by goods_id) as t2", '{{%goods}}.id = t2.goods_id', $joinParams);
        }
        return $this->getBaseListData([
            'query' => $query,
            'select' => 'id,name,first_image,state,created_time',
            'search' => [
                'state' => [
                    '=',
                    'state'
                ],
                'keyword' => [
                    'like',
                    'name'
                ]
            ],
            'each' => function ($row) {
                $row['state'] = [
                    'value' => $row['state'],
                    'label' => Goods::$stateMap[$row['state']]
                ];
                $row['created_time'] = date('Y/m/d H:i', $row['created_time']);
                $firstImage = $row['first_image'] ? Json::decode($row['first_image']) : null;
                $row['first_image'] = $row['first_image'] ? UploadHelper::getImageUrl($firstImage['url'], UploadHelper::SIZE_SM) : null;
                return $row;
            }
        ]);
    }

    public function getDpConfigListData($queryParams)
    {
        return $this->getBaseListData([
            'select' => 'id,name,first_image',
            'search' => [
                'keyword' => [
                    'like',
                    'name'
                ]
            ],
            'each' => function ($row) {
                $firstImage = $row['first_image'] ? Json::decode($row['first_image']) : null;
                $row['first_image'] = $row['first_image'] ? UploadHelper::getImageUrl($firstImage['url'], UploadHelper::SIZE_SM) : null;
                $row['attr_config'] = GoodsAttrConfig::find()->asArray()
                    ->andWhere([
                    'goods_id' => $row['id']
                ])
                    ->one();
                $row['attr_assign'] = [];
                $attrAssigns = GoodsAttrAssign::find()->asArray()
                    ->andWhere([
                    'goods_id' => $row['id']
                ])
                    ->andWhere('attr_id != 1')
                    ->orderBy('id asc')
                    ->all();
                foreach ($attrAssigns as $attrAssign) {
                    $goodsAttr = GoodsAttr::find()->asArray()
                        ->andWhere([
                        'id' => $attrAssign['attr_id']
                    ])
                        ->orderBy('id asc')
                        ->one();
                    $goodsAttr['items'] = [];
                    if ($goodsAttr) {
                        $itemAssigns = GoodsAttrItemAssign::find()->asArray()
                            ->andWhere([
                            'goods_id' => $row['id'],
                            'attr_id' => $goodsAttr['id']
                        ])
                            ->orderBy('id asc')
                            ->all();
                        foreach ($itemAssigns as $itemAssign) {
                            $goodsItem = GoodsAttrItem::find()->asArray()
                                ->andWhere([
                                'id' => $itemAssign['item_id']
                            ])
                                ->one();
                            if ($goodsItem)
                                $goodsAttr['items'][] = $goodsItem;
                        }
                    }
                    $row['attr_assign'][] = $goodsAttr;
                }
                $row['sku_price'] = GoodsSkuPrice::find()->asArray()
                    ->andWhere([
                    'goods_id' => $row['id']
                ])
                    ->orderBy('id asc')
                    ->all();
                $row['sku_handle_price']  = GoodsSkuSystem::find()->asArray()
                    ->andWhere([
                        'goods_id' => $row['id'],
                        'type'     => GoodsAttr::SYSTEM_HANDLE_ID
                    ])
                    ->orderBy('id asc')
                    ->all();
                return $row;
            }
        ]);
    }

    public function getSkuDetailPrice($skuDetail, $attrConfig)
    {
        $cost = 0;
        $goodsCpConfigs = GoodsCpConfig::find()->asArray()
            ->andWhere([
            'goods_id' => $skuDetail['goods_id'],
            'sku_detail_id' => $skuDetail['id']
        ])
            ->orderBy('id asc')
            ->all();
        foreach ($goodsCpConfigs as &$goodsCpConfig) {
            $d_cost = 0;
            $content = Json::decode($goodsCpConfig['content']);
            $dp_attrConfig = GoodsAttrConfig::find()->asArray()
                ->andWhere([
                'goods_id' => $goodsCpConfig['dp_goods_id']
            ])
                ->one();
            if ($content['attr']) {
                $s_sku_id = [];
                foreach ($content['attr'] as $attrItem) {
                    $attr_t = GoodsAttr::find()->asArray()
                        ->andWhere([
                        'id' => $attrItem['attr_id']
                    ])
                        ->one();
                    if ($attr_t && $attr_t['type'] == 1) {
                        $s_sku_id[] = $attrItem['attr_id'] . '_' . $attrItem['item_id'];
                    }
                }
                $s_sku_id = implode(":", $s_sku_id);
                $skuPrice = GoodsSkuPrice::find()->asArray()
                    ->andWhere([
                    'goods_id' => $goodsCpConfig['dp_goods_id'],
                    'sku_id' => $s_sku_id
                ])
                    ->one();
                if ($skuPrice) {
                    $d_cost = $skuPrice['cost'];
                }
            } else {
                $d_cost = $dp_attrConfig['cost'];
            }
            if ($dp_attrConfig['price_count_type'] == 1) {
                $cost += $d_cost * floatval($content['unit']);
            } else if ($dp_attrConfig['price_count_type'] == 2) {
                $cost += ($d_cost / 500) * floatval($content['unit']);
            }
        }
        return [
            'sale_price' => $cost * $attrConfig['sale_scale'],
            'cost' => $cost
        ];
    }

    public function getCpConfigListData($queryParams)
    {
        return $this->getBaseListData([
            'select' => 'id,name,first_image',
            'search' => [
                'keyword' => [
                    'like',
                    'name'
                ]
            ],
            'each' => function ($row) {
                $firstImage = $row['first_image'] ? Json::decode($row['first_image']) : null;
                $row['first_image'] = $row['first_image'] ? UploadHelper::getImageUrl($firstImage['url'], UploadHelper::SIZE_SM) : null;
                $row['attr_config'] = GoodsAttrConfig::find()->asArray()
                    ->andWhere([
                    'goods_id' => $row['id']
                ])
                    ->one();
                $row['attr_assign'] = [];
                $attrAssigns = GoodsAttrAssign::find()->asArray()
                    ->andWhere([
                    'goods_id' => $row['id']
                ])
                    ->andWhere('attr_id != 1')
                    ->orderBy('id asc')
                    ->all();
                foreach ($attrAssigns as $attrAssign) {
                    $goodsAttr = GoodsAttr::find()->asArray()
                        ->andWhere([
                        'id' => $attrAssign['attr_id']
                    ])
                        ->orderBy('id asc')
                        ->one();
                    $goodsAttr['items'] = [];
                    if ($goodsAttr) {
                        $itemAssigns = GoodsAttrItemAssign::find()->asArray()
                            ->andWhere([
                            'goods_id' => $row['id'],
                            'attr_id' => $goodsAttr['id']
                        ])
                            ->orderBy('id asc')
                            ->all();
                        foreach ($itemAssigns as $itemAssign) {
                            $goodsItem = GoodsAttrItem::find()->asArray()
                                ->andWhere([
                                'id' => $itemAssign['item_id']
                            ])
                                ->one();
                            if ($goodsItem)
                                $goodsAttr['items'][] = $goodsItem;
                        }
                    }
                    $row['attr_assign'][] = $goodsAttr;
                }
                $skuDetails = GoodsSkuDetail::find()->asArray()
                    ->andWhere([
                    'goods_id' => $row['id']
                ])
                    ->orderBy('id asc')
                    ->all();
                foreach ($skuDetails as &$skuDetail) {
                    $price = $this->getSkuDetailPrice($skuDetail, $row['attr_config']);
                    $skuDetail['cost'] = $price['cost'];
                    $skuDetail['sale_price'] = $price['sale_price'];
                }
                $row['sku_price'] = $skuDetails;
                return $row;
            }
        ]);
    }

    public function getSaveInfo($model)
    {
        $info = [];
        if ($model) {
            $info['created_time'] = date('Y/m/d H:i', $model['created_time']);
            $info['youzan_id'] = $model['youzan_id'];
            $info['version'] = $model['version'];
            $info['youzan_version'] = $model['youzan_version'];
            $info['youzan_syn_time'] = $model['youzan_syn_time'] ? date('Y/m/d H:i', $model['youzan_syn_time']) : null;
            $info['youzan_syn_state'] = Goods::$youzanySynStateMap[$model['youzan_syn_state']];
        }
        return $info;
    }

    public function getSaveForm($model = null)
    {
        $form = [
            'name' => '',
            'adorn_text' => '',
            'images' => [],
            'state' => Goods::STATE_ENABLE,
            'categorys' => [],
            'tags' => [],
            'youzan_id' => '',
            'youzan_sell_point' => '',
            'youzan_origin_price' => '',
            'youzan_join_level_discount' => 1,
            'print_config' => ''
        ];
        if ($model) {
            $form = ArrayHelper::merge($form, $model->getAttributes([
                'name',
                'images',
                'state',
                'adorn_text',
                'youzan_id',
                'youzan_sell_point',
                'youzan_origin_price',
                'youzan_join_level_discount',
                'print_config'
            ]));
            $form['images'] = $this->getImageListValue(Json::decode($form['images']));
            $form['categorys'] = $this->getCategoryAssigns($model['id']);
            $form['tags'] = $this->getGroupAssigns($model['id']);
        }
        return $form;
    }

    public function save($model, $data)
    {
        if ($model->isNewRecord) {
            $model['type'] = $this->type;
            $model['created_time'] = time();
        }
        $model->setAttributes(ArrayHelper::persist([
            'name',
            'adorn_text',
            'category_id',
            'state',
            'youzan_id',
            'youzan_sell_point',
            'youzan_origin_price',
            'youzan_join_level_discount',
            'print_config'
        ], $data), false);
        $images = [];
        if (! Validator::isEmptyArray($data['images'])) {
            $images = $data['images'];
        }
        $model['images'] = $images ? Json::encode($images) : null;
        $model['first_image'] = $images ? Json::encode($images[0]) : null;
        $model['is_delete'] = 0;
        $model['version'] = intval($model['version']) + 1;
        $model['youzan_syn_time'] = null;
        $model['youzan_syn_state'] = Goods::YOUZAN_SYN_STATE_NOT;
        $model->save();
        if ($model['type'] == Goods::TYPE_DP) {
            $goodsCpConfigs = GoodsCpConfig::find()->asArray()
                ->andWhere([
                'dp_goods_id' => $model['id']
            ])
                ->all();
            foreach ($goodsCpConfigs as $goodsCpConfig) {
                $s_goods = Goods::find()->andWhere([
                    'id' => $goodsCpConfig['goods_id']
                ])->one();
                if ($s_goods) {
                    $s_goods['version'] += 1;
                    $s_goods->save();
                }
                $tc_goodsConfigs = GoodsTcConfig::find()->andWhere(['dp_goods_id'=>$goodsCpConfig['goods_id']])->all();
                foreach($tc_goodsConfigs as $tc_goodsConfig)
                {
                    $ts_goods = Goods::find()->andWhere(['id'=>$tc_goodsConfig['goods_id']])->one();
                    if($ts_goods){
                        $ts_goods['version'] += 1;
                        $ts_goods->save();
                    }
                }
            }
        }
        $this->setCategoryAssign($model, $data['categorys']);
        $this->setTagAssign($model, $data['tags']);
        $this->setAttrAssign($model, $data['attr_assign']);
        $this->setAttrConfig($model, $data['attr_config']);
        if ($model['type'] == Goods::TYPE_DP) {
            $this->setSkuPrice($model, $data['sku_price']);
            $this->setSysAttrWeight($model, $data['sys_attr_weight']);
            $this->setSysAttrHandle($model, $data['sys_attr_handle']);
            $this->setSysAttrWash($model, $data['sys_attr_wash']);
        }
        $this->setSkuDetail($model, $data['sku_detail']);
    }

    public function deleteById($id)
    {
        Goods::updateAll([
            'is_delete' => 1
        ], [
            'id' => $id
        ]);
        GoodsCategoryAssign::deleteAll([
            'goods_id' => $id
        ]);
        GoodsTagAssign::deleteAll([
            'goods_id' => $id
        ]);
        GoodsAttrConfig::deleteAll([
            'goods_id' => $id
        ]);
        GoodsAttrAssign::deleteAll([
            'goods_id' => $id
        ]);
        GoodsAttrItemAssign::deleteAll([
            'goods_id' => $id
        ]);
        GoodsSkuPrice::deleteAll([
            'goods_id' => $id
        ]);
        GoodsSkuSystem::deleteAll([
            'goods_id' => $id
        ]);
        GoodsSkuDetail::deleteAll([
            'goods_id' => $id
        ]);
        return $id;
    }

    public function setSysAttrWeight($model, $sysAttrWeight)
    {
        $skus = $sysAttrWeight['sku'];
        $itemIds = $sysAttrWeight['assign'];
        GoodsSkuSystem::deleteAll([
            'goods_id' => $model['id'],
            'type' => GoodsAttr::SYSTEM_WEIGHT_ID
        ]);
        if (! Validator::isEmptyArray($skus)) {
            foreach ($skus as $sku) {
                $goodsSkuSystem = new GoodsSkuSystem();
                $goodsSkuSystem['goods_id'] = $model['id'];
                $goodsSkuSystem['sku_id'] = $sku['sku_id'];
                $goodsSkuSystem['sale_price'] = $sku['sale_price'];
                $goodsSkuSystem['type'] = GoodsAttr::SYSTEM_WEIGHT_ID;
                $goodsSkuSystem->save();
            }
        }
        if (! Validator::isEmptyArray($itemIds)) {
            $attrAssign = new GoodsAttrAssign();
            $attrAssign['goods_id'] = $model['id'];
            $attrAssign['attr_id'] = GoodsAttr::SYSTEM_WEIGHT_ID;
            $attrAssign['attr_type'] = 2;
            $attrAssign->save();
            foreach ($itemIds as $itemId) {
                $goodsAttrItemAssign = new GoodsAttrItemAssign();
                $goodsAttrItemAssign['goods_id'] = $model['id'];
                $goodsAttrItemAssign['attr_id'] = GoodsAttr::SYSTEM_WEIGHT_ID;
                $goodsAttrItemAssign['item_id'] = $itemId;
                $goodsAttrItemAssign['attr_type'] = GoodsAttr::TYPE_SYSTEM;
                $goodsAttrItemAssign->save();
            }
        }
    }

    public function setSysAttrHandle($model, $sysAttrHandle)
    {
        $skus = $sysAttrHandle['sku'];
        $itemIds = $sysAttrHandle['assign'];
        GoodsSkuSystem::deleteAll([
            'goods_id' => $model['id'],
            'type' => GoodsAttr::SYSTEM_HANDLE_ID
        ]);
        if (! Validator::isEmptyArray($skus)) {
            foreach ($skus as $sku) {
                $goodsSkuSystem = new GoodsSkuSystem();
                $goodsSkuSystem['goods_id'] = $model['id'];
                $goodsSkuSystem['sku_id'] = $sku['sku_id'];
                $goodsSkuSystem['handle_price'] = $sku['handle_price'];
                $goodsSkuSystem['type'] = GoodsAttr::SYSTEM_HANDLE_ID;
                $goodsSkuSystem->save();
            }
        }
        if (! Validator::isEmptyArray($itemIds)) {
            $attrAssign = new GoodsAttrAssign();
            $attrAssign['goods_id'] = $model['id'];
            $attrAssign['attr_id'] = GoodsAttr::SYSTEM_HANDLE_ID;
            $attrAssign['attr_type'] = 2;
            $attrAssign->save();
            foreach ($itemIds as $itemId) {
                $goodsAttrItemAssign = new GoodsAttrItemAssign();
                $goodsAttrItemAssign['goods_id'] = $model['id'];
                $goodsAttrItemAssign['attr_id'] = GoodsAttr::SYSTEM_HANDLE_ID;
                $goodsAttrItemAssign['item_id'] = $itemId;
                $goodsAttrItemAssign['attr_type'] = GoodsAttr::TYPE_SYSTEM;
                $goodsAttrItemAssign->save();
            }
        }
    }

    public function setSysAttrWash($model, $skus)
    {
        GoodsSkuSystem::deleteAll([
            'goods_id' => $model['id'],
            'type' => GoodsAttr::SYSTEM_WASH_ID
        ]);
        if (! Validator::isEmptyArray($skus)) {
            foreach ($skus as $sku) {
                $goodsSkuSystem = new GoodsSkuSystem();
                $goodsSkuSystem['goods_id'] = $model['id'];
                $goodsSkuSystem['sku_id'] = $sku['sku_id'];
                $goodsSkuSystem['wash_price'] = $sku['wash_price'];
                $goodsSkuSystem['type'] = GoodsAttr::SYSTEM_WASH_ID;
                $goodsSkuSystem->save();
            }
        }
    }

    public function setSkuDetail($model, $skus)
    {
        GoodsSkuDetail::deleteAll([
            'goods_id' => $model['id'],
            'type' => $model['type']
        ]);
        if ($model['type'] == Goods::TYPE_CP) {
            GoodsCpConfig::deleteAll([
                'goods_id' => $model['id']
            ]);
        }
        if ($model['type'] == Goods::TYPE_TC) {
            GoodsTcConfig::deleteAll([
                'goods_id' => $model['id']
            ]);
        }
        if (! Validator::isEmptyArray($skus)) {
            foreach ($skus as $sku) {
                $goodSkuDetail = new GoodsSkuDetail();
                $goodSkuDetail['goods_id'] = $model['id'];
                $goodSkuDetail['type'] = $model['type'];
                $goodSkuDetail['sku_id'] = $sku['sku_id'];
                $goodSkuDetail['cost'] = $sku['cost'];
                $goodSkuDetail['sale_price'] = $sku['sale_price'];
                $goodSkuDetail['stock'] = $sku['stock'];
                $goodSkuDetail->save();
                if ($model['type'] == Goods::TYPE_CP) {
                    if ($sku['cpConfig']) {
                        foreach ($sku['cpConfig'] as $c) {
                            $cpConfig = new GoodsCpConfig();
                            $cpConfig['goods_id'] = $model['id'];
                            $cpConfig['sku_detail_id'] = $goodSkuDetail['id'];
                            $cpConfig['dp_goods_id'] = $c['goods_id'];
                            $cpConfig['content'] = Json::encode([
                                'unit' => $c['unit'],
                                'attr' => $c['attr']
                            ]);
                            $cpConfig->save();
                        }
                    }
                }
                if ($model['type'] == Goods::TYPE_TC) {
                    if ($sku['cpConfig']) {
                        foreach ($sku['cpConfig'] as $c) {
                            $cpConfig = new GoodsTcConfig();
                            $cpConfig['goods_id'] = $model['id'];
                            $cpConfig['sku_detail_id'] = $goodSkuDetail['id'];
                            $cpConfig['dp_goods_id'] = $c['goods_id'];
                            $cpConfig['content'] = Json::encode([
                                'attr' => $c['attr']
                            ]);
                            $cpConfig->save();
                        }
                    }
                }
            }
        }
    }

    public function setAttrAssign($model, $attrAssigns)
    {
        GoodsAttrAssign::deleteAll([
            'goods_id' => $model['id']
        ]);
        GoodsAttrItemAssign::deleteAll([
            'goods_id' => $model['id']
        ]);
        if (! Validator::isEmptyArray($attrAssigns)) {
            foreach ($attrAssigns as $attrAssign) {
                $goodsAttrAssign = new GoodsAttrAssign();
                $goodsAttrAssign['goods_id'] = $model['id'];
                $goodsAttrAssign['attr_id'] = $attrAssign['attr_id'];
                $goodsAttrAssign['attr_type'] = GoodsAttr::TYPE_CUSTOM;
                $goodsAttrAssign->save();
                if (! Validator::isEmptyArray($attrAssign['item_id'])) {
                    foreach ($attrAssign['item_id'] as $itemId) {
                        $goodsAttrItemAssign = new GoodsAttrItemAssign();
                        $goodsAttrItemAssign['goods_id'] = $model['id'];
                        $goodsAttrItemAssign['attr_id'] = $attrAssign['attr_id'];
                        $goodsAttrItemAssign['item_id'] = $itemId;
                        $goodsAttrItemAssign['attr_type'] = GoodsAttr::TYPE_CUSTOM;
                        $goodsAttrItemAssign->save();
                    }
                }
            }
        }
    }

    public function setAttrConfig($model, $attrConfig)
    {
        $config = GoodsAttrConfig::find()->andWhere([
            'goods_id' => $model['id']
        ])->one();
        if (! $config) {
            $config = new GoodsAttrConfig();
            $config['goods_id'] = $model['id'];
        }
        $config->setAttributes(ArrayHelper::persist([
            'auto_create_sale_price',
            'price_count_type',
            'cost',
            'sale_scale',
            'sale_price',
            'wash_price',
            'sys_attr_weight',
            'sys_attr_handle',
            'sys_attr_wash',
            'has_custom_attr',
            'stock'
        ], $attrConfig), false);
        $config->save();
    }

    public function setCategoryAssign($model, $categoryIds)
    {
        GoodsCategoryAssign::deleteAll([
            'goods_id' => $model['id']
        ]);
        if (! Validator::isEmptyArray($categoryIds)) {
            $insert = [];
            foreach ($categoryIds as $categoryId) {
                $insert[] = [
                    $model['id'],
                    $categoryId,
                    $model['type']
                ];
            }
            Yii::$app->db->createCommand()
                ->batchInsert('{{%goods_category_assign}}', [
                'goods_id',
                'category_id',
                'type'
            ], $insert)
                ->execute();
        }
    }

    public function setTagAssign($model, $tagIds)
    {
        GoodsTagAssign::deleteAll([
            'goods_id' => $model['id']
        ]);
        if (! Validator::isEmptyArray($tagIds)) {
            $insert = [];
            foreach ($tagIds as $tagId) {
                $insert[] = [
                    $model['id'],
                    $tagId,
                    $model['type']
                ];
            }
            Yii::$app->db->createCommand()
                ->batchInsert('{{%goods_tag_assign}}', [
                'goods_id',
                'tag_id',
                'type'
            ], $insert)
                ->execute();
        }
    }

    public function setSkuPrice($model, $skus)
    {
        GoodsSkuPrice::deleteAll([
            'goods_id' => $model['id']
        ]);
        if (! Validator::isEmptyArray($skus)) {
            foreach ($skus as $sku) {
                $goodsSkuPrice = new GoodsSkuPrice();
                $goodsSkuPrice['goods_id'] = $model['id'];
                $goodsSkuPrice['sku_id'] = $sku['sku_id'];
                $goodsSkuPrice['cost'] = $sku['cost'];
                $goodsSkuPrice['sale_scale'] = $sku['sale_scale'];
                $goodsSkuPrice['sale_price'] = $sku['sale_price'];
                $goodsSkuPrice->save();
            }
        }
    }

    public function getCategoryAssigns($id)
    {
        $categoryAssigns = GoodsCategoryAssign::find()->asArray()
            ->select('category_id')
            ->andWhere([
            'goods_id' => $id
        ])
            ->orderBy('id asc')
            ->all();
        return ArrayHelper::getColumn($categoryAssigns, 'category_id');
    }

    public function getGroupAssigns($id)
    {
        $groupAssigns = GoodsTagAssign::find()->asArray()
            ->select('tag_id')
            ->andWhere([
            'goods_id' => $id
        ])
            ->orderBy('id asc')
            ->all();
        return ArrayHelper::getColumn($groupAssigns, 'tag_id');
    }

    public function getAttrConfigForm($id = null)
    {
        $form = null;
        if ($id) {
            $config = GoodsAttrConfig::find()->andWhere([
                'goods_id' => $id
            ])->one();
            $form = $config ? $config->getAttributes([
                'has_custom_attr',
                'auto_create_sale_price',
                'price_count_type',
                'cost',
                'sale_scale',
                'sale_price',
                'wash_price',
                'sys_attr_weight',
                'sys_attr_handle',
                'sys_attr_wash',
                'stock'
            ]) : null;
        }
        return $form;
    }

    public function getAttrAssignForm($id)
    {
        $form = null;
        if ($id) {
            $form = [];
            $attrAssigns = GoodsAttrAssign::find()->asArray()
                ->andWhere([
                'goods_id' => $id,
                'attr_type' => GoodsAttr::TYPE_CUSTOM
            ])
                ->orderBy('id asc')
                ->all();
            foreach ($attrAssigns as $attrAssign) {
                $attrItems = [];
                $attrItemAssigns = GoodsAttrItemAssign::find()->asArray()
                    ->andWhere([
                    'goods_id' => $id,
                    'attr_id' => $attrAssign['attr_id'],
                    'attr_type' => GoodsAttr::TYPE_CUSTOM
                ])
                    ->orderBy('id asc')
                    ->all();
                foreach ($attrItemAssigns as $attrItemAssign) {
                    $attrItem = GoodsAttrItem::find()->asArray()
                        ->andWhere([
                        'id' => $attrItemAssign['item_id']
                    ])
                        ->one();
                    $attrItems[] = [
                        'id' => $attrItem['id'],
                        'name' => $attrItem['name']
                    ];
                }
                $attr = GoodsAttr::find()->asArray()
                    ->andWhere([
                    'id' => $attrAssign['attr_id']
                ])
                    ->one();
                $form[] = [
                    'attr' => [
                        'id' => $attr['id'],
                        'name' => $attr['name']
                    ],
                    'items' => $attrItems
                ];
            }
        }
        return $form;
    }

    public function getSkuPriceForm($id)
    {
        $form = null;
        if ($id) {
            $form = GoodsSkuPrice::find()->asArray()
                ->andWhere([
                'goods_id' => $id
            ])
                ->orderBy('id asc')
                ->all();
        }
        return $form;
    }

    public function getSkuDetailForm($id)
    {
        $form = null;
        if ($id) {
            $model = Goods::find()->asArray()
                ->andWhere([
                'id' => $id
            ])
                ->one();
            $form = GoodsSkuDetail::find()->asArray()
                ->andWhere([
                'goods_id' => $id
            ])
                ->orderBy('id asc')
                ->all();

                if ($model && $model['type'] == Goods::TYPE_CP) {
                    foreach ($form as &$detail) {
                        $dpc = GoodsCpConfig::find()->asArray()
                        ->orderBy('id asc')
                        ->andWhere([
                            'goods_id' => $id,
                            'sku_detail_id' => $detail['id']
                        ])
                        ->all();

                        $dpc_arr = [];
                        foreach ($dpc as $item) {
                            $content = Json::decode($item['content']);
                            $dpGoods = Goods::find()->asArray()
                            ->andWhere([
                                'id' => $item['dp_goods_id']
                            ])
                            ->one();
                            $attrConfig = GoodsAttrConfig::find()->asArray()
                            ->andWhere([
                                'goods_id' => $item['dp_goods_id']
                            ])
                            ->one();
                            $selectAttrs = [];
                            $skuPrice = null;
                            $handlePrice = null;

                            if ($content['attr']) {
                                $skuId = [];
                                foreach ($content['attr'] as $atrkey=>$attr) {
                                    $skuJgId = [];
                                    $attrm = GoodsAttr::find()->asArray()
                                    ->andWhere([
                                        'id' => $attr['attr_id']
                                    ])
                                    ->one();
                                    $itemm = GoodsAttrItem::find()->asArray()
                                    ->andWhere([
                                        'id' => $attr['item_id']
                                    ])
                                    ->one();
                                    $selectAttrs[] = [
                                        'attr' => $attrm,
                                        'item' => $itemm
                                    ];

                                    if ($attrm['type'] == 1) {//自定义
                                        $skuId[] = "{$attrm['id']}_{$itemm['id']}";
                                    }

                                    $skuJgId = array_merge($skuId,["{$attrm['id']}_{$itemm['id']}"]);

                                }
                                $skuId = implode(':', $skuId);

                                $skuPrice = GoodsSkuPrice::find()->asArray()
                                ->andWhere([
                                    'goods_id' => $item['dp_goods_id'],
                                    'sku_id' => $skuId
                                ])
                                ->one();
                                $skuJgIds = implode(':', $skuJgId);
                                $handlePrice = GoodsSkuSystem::find()->asArray()
                                    ->andWhere([
                                        'goods_id' => $item['dp_goods_id'],
                                        'sku_id'   => $skuJgIds,
                                        'type'     => GoodsAttr::SYSTEM_HANDLE_ID
                                    ])
                                    ->orderBy('id asc')
                                    ->all();
                            }

                            $dpc_arr[] = [
                                'id' => $dpGoods['id'],
                                'name' => $dpGoods['name'],
                                'price_count_type' => $attrConfig['price_count_type'],
                                'unit' => $content['unit'],
                                'selectAttrs' => $selectAttrs,
                                'cost' => $skuPrice ? $skuPrice['cost'] : $attrConfig['cost'],
                                'handle_price' => $handlePrice ? $handlePrice[0]['handle_price'] : "0.00"
                            ];
                        }
                        $detail['dpc'] = $dpc_arr;
                    }
                }
                if ($model && $model['type'] == Goods::TYPE_TC) {
                    foreach ($form as &$detail) {
                        $dpc = GoodsTcConfig::find()->asArray()
                        ->orderBy('id asc')
                        ->andWhere([
                            'goods_id' => $id,
                            'sku_detail_id' => $detail['id']
                        ])
                        ->all();
                        $dpc_arr = [];
                        foreach ($dpc as $item) {
                            $content = Json::decode($item['content']);
                            $dpGoods = Goods::find()->asArray()
                            ->andWhere([
                                'id' => $item['dp_goods_id']
                            ])
                            ->one();
                            $attrConfig = GoodsAttrConfig::find()->asArray()
                            ->andWhere([
                                'goods_id' => $item['dp_goods_id']
                            ])
                            ->one();
                            $selectAttrs = [];
                            $skuPrice = null;
                            if ($content['attr']) {
                                $skuId = [];
                                foreach ($content['attr'] as $attr) {
                                    $attrm = GoodsAttr::find()->asArray()
                                    ->andWhere([
                                        'id' => $attr['attr_id']
                                    ])
                                    ->one();
                                    $itemm = GoodsAttrItem::find()->asArray()
                                    ->andWhere([
                                        'id' => $attr['item_id']
                                    ])
                                    ->one();
                                    $selectAttrs[] = [
                                        'attr' => $attrm,
                                        'item' => $itemm
                                    ];
                                    if ($attrm['type'] == 1) {
                                        $skuId[] = "{$attrm['id']}_{$itemm['id']}";
                                    }
                                }
                                $skuId = implode(':', $skuId);
                                $skuPrice = GoodsSkuDetail::find()->asArray()
                                ->andWhere([
                                    'goods_id' => $item['dp_goods_id'],
                                    'sku_id' => $skuId
                                ])
                                ->one();
                                $price = $this->getSkuDetailPrice($skuPrice, $attrConfig);
                            }
                            $dpc_arr[] = [
                                'id' => $dpGoods['id'],
                                'name' => $dpGoods['name'],
                                'price_count_type' => $attrConfig['price_count_type'],
                                'selectAttrs' => $selectAttrs,
                                'cost' => $price?$price['cost']:0
                            ];
                        }
                        $detail['dpc'] = $dpc_arr;
                    }
                }
        }
        return $form;
    }

    public function getSysAttrWeightForm($id)
    {
        $form = null;
        if ($id) {
            $form = [
                'assign' => [],
                'sku' => []
            ];
            $attrItemAssigns = GoodsAttrItemAssign::find()->asArray()
                ->andWhere([
                'goods_id' => $id,
                'attr_id' => GoodsAttr::SYSTEM_WEIGHT_ID,
                'attr_type' => GoodsAttr::TYPE_SYSTEM
            ])
                ->orderBy('id asc')
                ->all();
            foreach ($attrItemAssigns as $attrItemAssign) {
                $attrItem = GoodsAttrItem::find()->asArray()
                    ->andWhere([
                    'id' => $attrItemAssign['item_id']
                ])
                    ->one();
                $form['assign'][] = [
                    'id' => $attrItem['id'],
                    'name' => $attrItem['name'],
                    'weight' => $attrItem['weight']
                ];
            }
            $form['sku'] = GoodsSkuSystem::find()->asArray()
                ->andWhere([
                'goods_id' => $id,
                'type' => GoodsAttr::SYSTEM_WEIGHT_ID
            ])
                ->all();
        }
        return $form;
    }

    public function getSysAttrHandleForm($id)
    {
        $form = null;
        if ($id) {
            $form = [
                'assign' => [],
                'sku' => []
            ];
            $attrItemAssigns = GoodsAttrItemAssign::find()->asArray()
                ->andWhere([
                'goods_id' => $id,
                'attr_id' => GoodsAttr::SYSTEM_HANDLE_ID,
                'attr_type' => GoodsAttr::TYPE_SYSTEM
            ])
                ->orderBy('id asc')
                ->all();
            foreach ($attrItemAssigns as $attrItemAssign) {
                $attrItem = GoodsAttrItem::find()->asArray()
                    ->andWhere([
                    'id' => $attrItemAssign['item_id']
                ])
                    ->one();
                $form['assign'][] = [
                    'id' => $attrItem['id'],
                    'name' => $attrItem['name']
                ];
            }
            $form['sku'] = GoodsSkuSystem::find()->asArray()
                ->andWhere([
                'goods_id' => $id,
                'type' => GoodsAttr::SYSTEM_HANDLE_ID
            ])
                ->all();
        }
        return $form;
    }

    public function getSysAttrWashForm($id)
    {
        $form = null;
        if ($id) {
            $form = GoodsSkuSystem::find()->asArray()
                ->andWhere([
                'goods_id' => $id,
                'type' => GoodsAttr::SYSTEM_WASH_ID
            ])
                ->all();
        }
        return $form;
    }
}
