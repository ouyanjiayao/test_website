<?php
namespace app\services;

use common\librarys\ArrayHelper;
use common\librarys\Validator;

abstract class BaseTreeService extends BaseModelService
{

    public $sortColumn = "sort";

    public $parentColumn = "parent_id";

    public $maxLevel = null;

    public function getBaseTreeList($options = [])
    {
        $query = $options['query'] ? $options['query'] : $this->getQuery();
        $select = $options['select'] ? $options['select'] : null;
        $maxLevel = $options['maxLevel'] ? $options['maxLevel'] : $this->maxLevel;
        $each = $options['each'] ? $options['each'] : null;
        $where = $options['where'] ? $options['where'] : [];
        $params = $options['params'] ? $options['params'] : null;
        $orderBy = $this->sortColumn . " asc," . $this->idColumn . " asc";
        if ($select) {
            $query->select[] = $this->idColumn;
            $query->select[] = $this->sortColumn;
            $query->select[] = $this->parentColumn;
            $query->select = ArrayHelper::merge(explode(',', $select), $query->select);
        }
        $preCondition = $this->getPreCondition();
        if ($where) {
            $query->andWhere($where);
        }
        if ($params) {
            $query->params = $params;
        }
        $rows = $query->asArray()
            ->orderBy($orderBy)
            ->all();
        if ($each) {
            foreach ($rows as &$row) {
                $row = $each($row);
            }
        }
        $rows = ArrayHelper::toTree($rows, 0, $this->idColumn, $this->parentColumn, $this->sortColumn, "children", $maxLevel);
        return $rows;
    }

    public function getNodeOptions($valueColumn, $labelColumn, $eliminateId = null, $setMaxLevel = true)
    {
        $maxLevel = $setMaxLevel ? $this->maxLevel : null;
        $options = $this->getBaseTreeList([
            'select' => "{$valueColumn} as value,{$labelColumn} as label",
            'where' => $eliminateId ? [
                '!=',
                'id',
                $eliminateId
            ] : null,
            'maxLevel' => $maxLevel === null ? null : $maxLevel - 1
        ]);
        return $options;
    }

    public function traversalNode($id, $select, $nodes = [])
    {
        $node = $this->getQuery()
            ->asArray()
            ->select("{$this->idColumn},$select,{$this->parentColumn}")
            ->andWhere([
            'id' => $id
        ])
            ->one();
        if ($node) {
            $nodes[] = $node;
            if ($node[$this->parentColumn]) {
                $nodes = $this->traversalNode($node['parent_id'], $select, $nodes);
            }
        }
        return $nodes;
    }

    public function newSort($parentId = 0)
    {
        $query = $this->getQuery();
        $max = $query->asArray()
            ->andWhere([
            $this->parentColumn => $parentId
        ])
            ->max($this->sortColumn);
        return $max + 1;
    }

    public function saveNode(&$model, $sort, $parentId, $preCondition = [])
    {
        if (Validator::isEmptyString($parentId)) {
            $parentId = 0;
        }
        $class = $this->getModelClass();
        $table = $class::tableName();
        $preCondition = $this->getPreCondition();
        if ($parentId != $model['parent_id']) {
            $class::updateAllCounters([
                $this->sortColumn => - 1
            ], [
                'and',
                $preCondition,
                [
                    $this->parentColumn => $model[$this->parentColumn]
                ],
                [
                    '>',
                    $this->sortColumn,
                    $model[$this->sortColumn]
                ]
            ]);
        }
        if (! Validator::isEmptyString($sort)) {
            $isNewNode = $model->isNewRecord || $parentId != $model['parent_id'];
            $newSort = $this->newSort($parentId);
            $sort = intval($sort);
            if ($sort <= 0) {
                $sort = 1;
            }
            $maxSort = $newSort;
            if (! $isNewNode && $model['parent_id'] == $parentId) {
                $maxSort -= 1;
            }
            if ($sort > $maxSort) {
                $sort = $maxSort;
            }
            if (($isNewNode && $sort < $newSort) || $model[$this->sortColumn] != $sort) {
                if ($isNewNode || $sort < $model[$this->sortColumn]) {
                    $condition = [
                        'and',
                        $preCondition,
                        [
                            $this->parentColumn => $parentId
                        ],
                        [
                            '>=',
                            $this->sortColumn,
                            $sort
                        ]
                    ];
                    if (! $isNewNode) {
                        $condition[] = [
                            '<',
                            $this->sortColumn,
                            $model[$this->sortColumn]
                        ];
                    }
                    $class::updateAllCounters([
                        $this->sortColumn => + 1
                    ], $condition);
                } else {
                    $class::updateAllCounters([
                        $this->sortColumn => - 1
                    ], [
                        'and',
                        $preCondition,
                        [
                            $this->parentColumn => $parentId
                        ],
                        [
                            '<=',
                            $this->sortColumn,
                            $sort
                        ],
                        [
                            '>',
                            $this->sortColumn,
                            $model[$this->sortColumn]
                        ]
                    ]);
                }
            }
            $model[$this->sortColumn] = $sort;
        } else if ($model->isNewRecord || $model[$this->parentColumn] != $parentId) {
            $model[$this->sortColumn] = $this->newSort($parentId);
        }
        $model[$this->parentColumn] = $parentId;
        $model->save();
        return $model;
    }

    public function deleteById($id)
    {
        return $this->deleteAll([
            $this->idColumn => $id
        ]);
    }

    public function deleteAll($condition = [], $params = [])
    {
        $class = $this->getModelClass();
        $table = $class::tableName();
        $query = $this->getQuery();
        $rows = $query->asArray()
            ->select("{$this->parentColumn},count(1) as count,min({$this->sortColumn}) as min")
            ->andWhere($condition)
            ->groupBy($this->parentColumn)
            ->all();
        foreach ($rows as $row) {
            $class::updateAllCounters([
                $this->sortColumn => - $row['count']
            ], [
                'and',
                $this->getPreCondition(),
                [
                    $this->parentColumn => $row[$this->parentColumn]
                ],
                [
                    '>',
                    $this->sortColumn,
                    $row['min']
                ]
            ]);
        }
        return $this->deleteDepth($condition, $params);
    }

    public function deleteDepth($condition = [], $params = [])
    {
        $query = $this->getQuery();
        $nodes = $query->asArray()
            ->select($this->idColumn)
            ->andWhere($condition, $params)
            ->all();
        if (! $nodes) {
            return [];
        }
        $id = ArrayHelper::getColumn($nodes, $this->idColumn);
        $id = ArrayHelper::merge($id, $this->deleteDepth([
            $this->parentColumn => $id
        ]));
        $class = $this->getModelClass();
        $condition = [
            'and',
            $condition,
            $this->getPreCondition()
        ];
        $class::deleteAll($condition, $params);
        return $id;
    }
}
