<?php
namespace common\models;

use yii\db\ActiveRecord;

class BaseConfig extends ActiveRecord
{

    const KEY_DEV_NAME = 'dev_name';

    const KEY_WEB_NAME = 'web_name';

    const KEY_WEB_LOGO = 'web_logo';

    const KEY_MAINTAIN_STATE = "maintain_state";

    const KEY_MAINTAIN_WHITE_LIST = "maintain_white_list";

    const VAL_MAINTAIN_STATE_ENABLE = 1;

    const VAL_MAINTAIN_STATE_DISABLE = 0;

    const TYPE_VAL = 1;

    const TYPE_IMAGE = 2;

    public static function tableName()
    {
        return '{{%base_config}}';
    }

    private static $keyCache = [];

    public static function getOne($key)
    {
        $config = static::map([
            $key
        ]);
        return $config[$key];
    }

    public static function getAll($keys)
    {
        $result = [];
        $selectKey = [];
        foreach ($keys as $key) {
            if (key_exists($key, static::$keyCache)) {
                $result[$key] = static::$keyCache[$key];
            } else {
                $selectKey[] = $key;
            }
        }
        if ($selectKey) {
            $models = static::find()->asArray()
                ->andWhere([
                'key' => $selectKey
            ])
                ->all();
            foreach ($models as $model) {
                $result[$model['key']] = $model['value'];
                static::$keyCache[$model['key']] = $model['value'];
            }
        }
        return $result;
    }
}
