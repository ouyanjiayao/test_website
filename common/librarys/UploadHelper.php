<?php
namespace common\librarys;

use Yii;
use yii\helpers\Html;
use common\components\UploadException;
use yii\web\UploadedFile;

class UploadHelper
{

    const TYPE_IMAGE = 'image';

    const SIZE_MINI = "50x0";

    const SIZE_SM = "100x0";

    const SIZE_MED = "250x0";

    const SIZE_LG = "750x0";

    const LOACTION_PREFIX = '@web';

    const CODE_FILE_NOT = 0;

    const CODE_FILE_SUCCESS = 1;

    const CODE_FILE_SIZE_INVALID = - 1;

    const CODE_FILE_EXTENSION_NAME_INVALID = - 2;

    const CODE_FILE_INVALID = - 3;

    public static $configs = [
        self::TYPE_IMAGE => [
            UPLOAD_IMAGE_MAX_SIZE,
            UPLOAD_IMAGE_EXT_NAMES
        ]
    ];

    public static function getPath($url)
    {
        $path = null;
        if (preg_match('/^' . addcslashes(APP_URL_UPLOADS, '/') . '/', $url)) {
            $url = str_replace(APP_URL_UPLOADS, self::LOACTION_PREFIX, $url);
        }
        if (preg_match('/^' . self::LOACTION_PREFIX . '/', $url)) {
            $path = str_replace(self::LOACTION_PREFIX, APP_URL_UPLOADS, $url);
            if (! file_exists($path)) {
                $path = null;
            }
        }
        return $path;
    }

    public static function getUrl($file)
    {
        if (Validator::isEmptyString($file)) {
            return '';
        }
        $file = Html::encode($file);
        if (! preg_match('/^' . self::LOACTION_PREFIX . '/', $file)) {
            return $file;
        } else {
            return str_replace(self::LOACTION_PREFIX, Yii::$app->request->hostInfo.'/uploads', $file);
        }
    }

    public static function getImageUrl($file, $size = self::SIZE_LG)
    {
        if (Validator::isEmptyString($size)) {
            return static::getUrl($file);
        }
        $url = static::getUrl($file);
        $url = preg_replace("/(.*)\.(.*)/", "$1_$size.$2", $url);
        return $url;
    }

    public static function saveAs($name, $type = self::TYPE_IMAGE)
    {
        if (is_array($name)) {
            $pname = $name[0];
            $name = $name[1];
        } else {
            $pname = $name;
            $name = $pname;
        }
        $code = static::validate($pname, $type);
        if ($code < self::CODE_FILE_NOT) {
            throw new UploadException($pname, $name, $code, null);
        }
        $file = UploadedFile::getInstanceByName($pname);
        if ($file && ! $file->getHasError()) {
            $fileName = time() . uniqid() . "." . $file->getExtension();
            $date = date("Ymd");
            $dir = ENV_DIR_UPLOADS . DIRECTORY_SEPARATOR . $date . DIRECTORY_SEPARATOR;
            if (! is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $saveFile = $dir . $fileName;
            $result = $file->saveAs($saveFile) ? "/" . $date . "/" . $fileName : false;
            if ($result) {
                $url = self::LOACTION_PREFIX . $result;
                return [
                    'fileInstance' => $file,
                    'url' => $url
                ];
            }
            return null;
        } else {
            return null;
        }
    }

    public static function validate($name, $type)
    {
        if (! key_exists($type, static::$configs)) {
            return self::CODE_FILE_INVALID;
        }
        $maxSize = static::$configs[$type][0];
        $extensionNames = static::$configs[$type][1];
        $file = UploadedFile::getInstanceByName($name);
        if ($file) {
            if ($file->size / 1024 > $maxSize) {
                return self::CODE_FILE_SIZE_INVALID;
            }
            $extensionNames = explode(",", $extensionNames);
            if (! in_array($file->getExtension(), $extensionNames)) {
                return self::CODE_FILE_EXTENSION_NAME_INVALID;
            }
            if ($file->hasError) {
                return self::CODE_FILE_INVALID;
            }
            return self::CODE_FILE_SUCCESS;
        } else {
            return self::CODE_FILE_NOT;
        }
    }
    
}

?>
