<?php
namespace common\components;

use yii\web\BadRequestHttpException;
use common\librarys\UploadHelper;

class UploadException extends BadRequestHttpException
{

    public function __construct($pname = null, $name = null,$code = null, $previous = null)
    {
        $this->pname = $pname;
        $this->name = $name;
        $message = "";
        switch ($code) {
            case UploadHelper::CODE_FILE_EXTENSION_NAME_INVALID:
                $message = "{$name}扩展名无效";
                break;
            case UploadHelper::CODE_FILE_SIZE_INVALID:
                $message = "{$name}容量超过最大限制";
                break;
            case UploadHelper::CODE_FILE_INVALID:
                $message =  "{$name}上传失败";
                break;
            case UploadHelper::CODE_FILE_NOT:
                $message = "请上传{$name}";
                break;
        }
        parent::__construct($message, $code, $previous);
    }

    private $name;

    public function getName()
    {
        return $this->name;
    }

    private $pname;

    public function getPname()
    {
        return $this->pname;
    }
}
