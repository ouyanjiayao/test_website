<?php
return [
    'id' => RUN_APP_ID,
    'basePath' => ENV_DIR_APP_MAIN,
    'components' => [
        'user' => [
            'class'=>'app\components\User',
            'idParam' => 'backend',
            'enableAutoLogin' => true,
            'identityClass' => 'app\models\SystemUser',
            'loginUrl' => [
                '/auth/login'
            ]
        ],
        'view'=>[
            'class'=>'app\components\View'
        ],
        'request' => [
            'enableCsrfValidation' => false,
            'enableCookieValidation' => false
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index'
            ]
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
            'username' => DB_USERNAME,
            'password' => DB_PASSWORD,
            'charset' => DB_CHARSET,
            'tablePrefix' => DB_TABLE_PREFIX,
            /*'enableSchemaCache' => true,
            'schemaCacheDuration' => 86400*/
        ]
    ],
    
];

