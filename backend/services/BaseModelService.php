<?php
namespace app\services;

use Yii;
use app\librarys\Pagination;
use common\librarys\Validator;

abstract class BaseModelService extends BaseService
{

    public $idColumn = 'id';

    abstract public function getModelClass();

    public function getPreCondition()
    {
        return [];
    }

    public function buildQuery($query)
    {
        $condition = $this->getPreCondition();
        if ($condition) {
            $query->andWhere($condition);
        }
        return $query;
    }

    public function getQuery()
    {
        $class = $this->getModelClass();
        $query = $class::find();
        $query = $this->buildQuery($query);
        return $query;
    }

    public function getModel($id)
    {
        $query = $this->getQuery();
        $model = $query->andWhere([
            $this->idColumn => $id
        ])->one();
        return $model;
    }

    public function getOptions($valueColumn, $labelColumn,$where=[],$orderBy=null)
    {
        if(!$orderBy)
        {
           $orderBy = "{$this->idColumn} desc";
        }
        return $this->getQuery()
            ->asArray()
            ->select("{$valueColumn} as value,{$labelColumn} as label")
            ->andWhere($where)
            ->orderBy($orderBy)
            ->all();
    }

    public function getBaseListData($options = [])
    {
        $pagination = new Pagination();
        $query = $options['query'] ? $options['query'] : $this->getQuery();
        $select = $options['select'] ? $options['select'] : null;
        $orderBy = $options['orderBy'] ? $options['orderBy'] : "{$this->idColumn} desc";
        $search = $options['search'] ? $options['search'] : null;
        $each = $options['each'] ? $options['each'] : null;
        $where = $options['where'] ? $options['where'] : [];
        $params = $options['params'] ? $options['params'] : null;
        $preCondition = $this->getPreCondition();
        if ($where) {
            $query->andWhere($where);
        }
        if ($params) {
            $query->params = $params;
        }
        if ($search) {
            foreach ($search as $name => $conditionParams) {
                $op = $conditionParams[0];
                $columns = explode(',', $conditionParams[1]);
                if (! Validator::isEmptyString(Yii::$app->request->get($name))) {
                    $conditionWheres = [
                        'or'
                    ];
                    foreach ($columns as $column) {
                        $conditionWheres[] = [
                            $op,
                            $column,
                            trim(Yii::$app->request->get($name))
                        ];
                    }
                    $query->andWhere($conditionWheres);
                }
            }
        }
        $pagination->totalCount = (int) $query->count();
        $rows = $query->asArray()
            ->orderBy($orderBy)
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        if ($each) {
            foreach ($rows as &$row) {
                $row = $each($row);
            }
        }
        return [
            'rows' => $rows,
            'total' => $pagination->totalCount,
            'hasNext' => $pagination->hasNext()
        ];
    }

    public function deleteById($id)
    {
        $class = $this->getModelClass();
        $condition = [
            'and',
            [
                $this->idColumn => $id
            ],
            $this->getPreCondition()
        ];
        $class::deleteAll($condition);
        return $id;
    }
}
