<?php
namespace common\components;

use yii\web\BadRequestHttpException;

class FormDataException extends BadRequestHttpException
{

    public function __construct($formModel = null)
    {
        $this->formModel = $formModel;
        $message = null;
        if ($formModel) {
            $errors = $formModel->getErrors();
            if($errors)
                $message = reset(reset($errors));
        }
        parent::__construct($message);
    }

    private $formModel = null;

    public function getFormModel()
    {
        return $this->formModel;
    }
}
