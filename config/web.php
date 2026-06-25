<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$cacheConfig = match ($_ENV['CACHE_DRIVER'] ?? 'file') {
    'redis' => [
        'class' => yii\redis\Cache::class,
        'redis' => 'redis',
        'keyPrefix' => 'blog:',
    ],

    default => [
        'class' => yii\caching\FileCache::class,
        'keyPrefix' => 'blog:',
    ],
};
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'en-US',
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
        'r2' => [
            'class' => \app\components\R2Component::class,
            'account' => $_ENV['R2_ACCOUNT_ID'] ?? '',
            'key' => $_ENV['R2_ACCESS_KEY_ID'] ?? '',
            'secret' => $_ENV['R2_SECRET_ACCESS_KEY'] ?? '',
            'bucket' => $_ENV['R2_BUCKET'] ?? '',
            'public_url' => $_ENV['R2_PUBLIC_URL'] ?? '',
        ],
        'Ai' => [
            'class' => \app\components\AiWorkerComponent::class,
            'accountId' => $_ENV['CF_ACCOUNT_ID'],
            'apiToken' => $_ENV['CF_API_TOKEN'],
            'model' => $_ENV['CF_AI_MODEL'],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'cache' => $cacheConfig,

        'redis' => [
            'class' => yii\redis\Connection::class,
            'hostname' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['REDIS_PORT'] ?? 6379,
            'database' => $_ENV['REDIS_DATABASE'] ?? 0,
            'password' => !empty($_ENV['REDIS_PASSWORD'])
                ? $_ENV['REDIS_PASSWORD']
                : null,
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                ],
            ],
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'class' => app\components\ApiErrorHandler::class,
        ],
        'mail' => [
            'class' => \app\components\MailComponent::class,
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'transport' => [
                'scheme' => 'smtp',
                'host' => $_ENV['MAIL_HOST'],
                'username' => $_ENV['MAIL_USERNAME'],
                'password' => $_ENV['MAIL_PASSWORD'],
                'port' => (int) $_ENV['MAIL_PORT'],
                'encryption' => $_ENV['MAIL_ENCRYPTION'],
            ],
        ],
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

                [
                    'class' => yii\rest\UrlRule::class,
                    'controller' => [
                        'api/category',
                        'api/post',
                        'api/comment',
                        'api/tag',
                        'api/file',
                        'api/ai-log'
                    ],
                    'pluralize' => false,
                ],

                //post
                'GET api/post/trash' => 'api/post/trash-all',
                'GET  api/post/slug/<slug:[\w-]+>' => 'api/post/view-by-slug',
                'POST api/post/<id:\d+>/restore' => 'api/post/restore',
                'DELETE api/post/<id:\d+>/force' => 'api/post/force-delete',


                // Comment
                'GET api/comment/post/<postId:\d+>' => 'api/comment/by-post',

                // Like
                'POST api/like/<id:\d+>' => 'api/like/like',
                'DELETE api/like/<id:\d+>' => 'api/like/unlike',

                //ai
                'POST api/ai/generate-title' => 'api/ai/generate-title',
                'POST api/ai/generate-summary' => 'api/ai/generate-summary',
                'POST api/ai/rewrite' => 'api/ai/rewrite',
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
