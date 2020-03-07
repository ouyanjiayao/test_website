<?php
namespace common\librarys;

class ArrayHelper extends \yii\helpers\ArrayHelper
{    

    public static function persist($keys, $data)
    {
        $result = [];
        foreach ($keys as $key) {
            if (key_exists($key, $data)) {
                $result[$key] = $data[$key];
            }else{
                $result[$key] = null;
            }
        }
        return $result;
    }

    public static function replaceKey($datas, $srcKey, $newKey)
    {
        for ($i = 0; $i < sizeof($datas); $i ++) {
            $datas[$i][$newKey] = $datas[$i][$srcKey];
            unset($datas[$i][$srcKey]);
        }
        return $datas;
    }

    public static function toTree($datas, $rootValue = 0, $idKey = "id", $parentKey = "parent_id", $sortKey = "sort", $childKey = "children",$maxLevel=null,$currLevel = 0)
    {
        $resultDatas = array();
        $currLevel++;
        if($maxLevel && $currLevel>$maxLevel)
        {
            return $resultDatas;
        }
        foreach ($datas as $index => $data) {
            $resultData = array();
            if ($data[$parentKey] == $rootValue) {
                $resultData = $data;
                $childs = static::toTree($datas, $data[$idKey],$idKey,$parentKey,$sortKey,$childKey,$maxLevel,$currLevel);
                if ($childs) {
                    static::multisort($childs, $sortKey, SORT_ASC);
                }
                if($childs)
                {
                    $resultData[$childKey] = $childs;
                }
                $resultDatas[] = $resultData;
            }
        }
        return $resultDatas;
    }

}

?>
