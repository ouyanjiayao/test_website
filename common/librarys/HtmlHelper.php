<?php
namespace common\librarys;

use yii\helpers\Html;
use yii\helpers\Json;

class HtmlHelper extends Html
{

    public static function encodeJson($value, $options = JSON_UNESCAPED_UNICODE)
    {
        return Json::encode($value, $options);
    }

    public static function encodeText($value)
    {
        return addcslashes($value, '/');
    }

    public static function encodeMapOptions($map)
    {
        $options = [];
        foreach ($map as $key => $value) {
            $options[] = [
                'value' => (string) $key,
                'label' => (string) $value
            ];
        }
        return static::encodeJson($options);
    }
}

?>
