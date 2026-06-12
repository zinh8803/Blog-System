<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                // send all mails to a file by default.
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'modules' => [
        'api' => [
            'class' => app\modules\api\Module::class,
        ],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => $_ENV['COOKIE_VALIDATION_KEY'] ?? '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'errorHandler' => [
            'class' => app\components\ApiErrorHandler::class,
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [

                // Auth
                'POST api/auth/register' => 'api/auth/register',
                'POST api/auth/login' => 'api/auth/login',
                'POST api/auth/logout' => 'api/auth/logout',
                'GET api/auth/me' => 'api/auth/me',

                // Category
                [
                    'class' => yii\rest\UrlRule::class,
                    'controller' => ['api/category'],
                    'pluralize' => false,
                ],

                // Post
                [
                    'class' => yii\rest\UrlRule::class,
                    'controller' => ['api/post'],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET trash' => 'trash-all',
                        'GET slug/<slug:[\w-]+>' => 'view-by-slug',
                        'POST <id:\d+>/restore' => 'restore',
                        'DELETE <id:\d+>/force' => 'force-delete',
                    ],
                ],

                // Tag
                [
                    'class' => yii\rest\UrlRule::class,
                    'controller' => ['api/tag'],
                    'pluralize' => false,
                ],

                // Comment
                [
                    'class' => yii\rest\UrlRule::class,
                    'controller' => ['api/comment'],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET post/<postId:\d+>' => 'by-post',
                    ],
                ],

                // Like
                [
                    'class' => yii\rest\UrlRule::class,
                    'controller' => ['api/like'],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST toggle/<id:\d+>' => 'toggle',
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
