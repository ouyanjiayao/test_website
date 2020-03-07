<?php
namespace common\librarys;

use Yii;

class Url extends \yii\helpers\Url
{

    public static function normalizeRoute($route)
    {
        $currRoute = Yii::$app->controller->route;
        if (Validator::isEmptyString($route)) {
            $route = $currRoute;
        } else {
            $route_arr = explode("/", $route);
            if (! Validator::isEmptyString($route_arr[0])) {
                $currRoute_arr = explode("/", $currRoute);
                if (($j = sizeof($currRoute_arr)) >= ($i = sizeof($route_arr))) {
                    for ($i -= 1, $j -= 1; $i >= 0; $i --, $j --) {
                        $currRoute_arr[$j] = $route_arr[$i];
                    }
                    $route = implode('/', $currRoute_arr);
                }
            }
        }
        return $route;
    }

    public static function toRoute($route = '', $scheme = false)
    {
        if (! is_array($route) && Validator::isHttpUrl($route)) {
            return $route;
        }
        if (is_array($route)) {
            $sroute = $route[0];
        } else {
            $sroute = $route;
        }
        $sroute = static::normalizeRoute($sroute);
        if (is_array($route)) {
            $route[0] = $sroute;
        } else {
            $route = $sroute;
        }
        return parent::toRoute($route, $scheme);
    }
}
