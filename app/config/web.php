<?php

use app\services\MeetingsService;
use app\services\UsersMeetingsService;
use app\services\UsersService;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

Yii::$container->set('app\services\interfaces\IUsersMeetingsService', UsersMeetingsService::class);
Yii::$container->set('app\services\interfaces\IUsersService', UsersService::class);
Yii::$container->set('app\services\interfaces\IMeetingsService', MeetingsService::class);

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'jK4hGFsuS89r6ahzrtc8MChAsB_v1jme',
        ],
        'response' => [
            'class' => 'app\components\CustomResponse',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'user',
                    'extraPatterns' => [
                        'GET <id:\d+>/schedule' => 'schedule',
                        'POST <id:\d+>/attach' => 'attach',
                        'PUT <id:\d+>/detach' => 'detach',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'meeting',
                    'extraPatterns' => [
                        'POST <id:\d+>/attach' => 'attach',
                        'PUT <id:\d+>/detach' => 'detach',
                    ]
                ]
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
