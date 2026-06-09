<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
// comment out the following two lines when deployed to production
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

defined('YII_DEBUG') or define('YII_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
defined('YII_ENV') or define('YII_ENV', $_ENV['APP_ENV'] ?? 'prod');



$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
