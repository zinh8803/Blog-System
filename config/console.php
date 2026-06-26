<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'mail' => [
            'class' => \app\components\MailComponent::class,
        ],
        'redis' => [
            'class' => yii\redis\Connection::class,
            'hostname' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['REDIS_PORT'] ?? 6379,
            'database' => $_ENV['REDIS_DATABASE'] ?? 0,
            'password' => !empty($_ENV['REDIS_PASSWORD'])
                ? $_ENV['REDIS_PASSWORD']
                : null,
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'transport' => [
                'scheme' => 'smtp',
                'host' => $_ENV['MAIL_HOST'] ?? '',
                'username' => $_ENV['MAIL_USERNAME'] ?? '',
                'password' => $_ENV['MAIL_PASSWORD'] ?? '',
                'port' => (int) ($_ENV['MAIL_PORT'] ?? 587),
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
            ],
        ],
        'queue' => [
            'class' => yii\queue\redis\Queue::class,
            'redis' => 'redis',
            'channel' => 'queue',
            'as log' => yii\queue\LogBehavior::class,
        ],
        'log' => [
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
    ];
    // configuration adjustments for 'dev' environment
    // requires version `2.1.21` of yii2-debug module
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
