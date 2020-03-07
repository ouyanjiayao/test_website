<?php
namespace common\librarys;

class Validator
{

    public static function isMobile($value)
    {
        return preg_match("/^[1][3,4,5,7,8][0-9]{9}$/", $value);
    }

    public static function isHttpUrl($value)
    {
        return preg_match("/^(http|https){1}(:\/\/)?/", $value);
    }

    public static function isDateTime($value)
    {
        return preg_match("/^(\d{4})\-(\d{2})\-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $value);
    }

    public static function isTime($value)
    {
        return preg_match("/^(\d{2}):(\d{2})$/", $value);
    }

    public static function isEmail($value)
    {
        return preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $value);
    }

    public static function isNumber($value)
    {
        return is_numeric($value);
    }

    public static function isEmptyString($value, $trim = false)
    {
        if (! isset($value)) {
            return true;
        }
        if (is_array($value)) {
            return true;
        }
        $value = (string)$value;
        if ($trim) {
            $value = trim($value);
        }
        if ($value === "") {
            return true;
        }
        return false;
    }

    public static function isEmptyArray($array)
    {
        if (! isset($array)) {
            return true;
        }
        if (! is_array($array)) {
            return true;
        }
        if (sizeof($array) <= 0) {
            return true;
        }
        return false;
    }

    public static function tooMaxString($value, $max)
    {
        if (mb_strlen($value, ENV_CHARSET) > $max) {
            return true;
        }
        return false;
    }

    public static function tooMinString($value, $min)
    {
        if (mb_strlen($value, ENV_CHARSET) < $min) {
            return true;
        }
        return false;
    }
}

?>
