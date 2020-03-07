<?php
namespace common\components;

use Yii;
use common\librarys\Validator;
use common\librarys\ArrayHelper;
use yii\web\Response;
use yii\base\DynamicModel;

class BaseController extends \yii\web\Controller
{

    public function createFormModel($rules, $datas = null)
    {
        if ($datas === null) {
            $datas = Yii::$app->request->post();
        }
        $model = new DynamicModel($datas);
        foreach ($rules as $rule) {
            $options = [];
            foreach ($rule as $key => $value) {
                if ($key == '0' || $key == '1') {
                    continue;
                }
                $options[$key] = $value;
            }
            $model->addRule($rule[0], $rule[1], $options);
        }
        return $model;
    }

    public function validateForm($formConfigs, $datas = null)
    {
        $formModel = $this->createFormModel($formConfigs, $datas);
        if ($formModel->validate()) {
            return $formModel;
        } else {
            throw new FormDataException($formModel);
        }
    }

    public function asSuccess($success = false, $message = null, $merges = [])
    {
        $data = [
            'success' => $success
        ];
        if (! Validator::isEmptyString($message)) {
            $data['message'] = $message;
        }
        $data = ArrayHelper::merge($data, $merges);
        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }
}
