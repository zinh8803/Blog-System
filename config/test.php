<?php

$config = require __DIR__ . '/web.php';

$config['id'] = 'basic-tests';

$config['components']['db'] = require __DIR__ . '/test_db.php';

$config['components']['mailer'] = [
    'class' => \yii\symfonymailer\Mailer::class,
    'messageClass' => \yii\symfonymailer\Message::class,
    'useFileTransport' => true,
    'viewPath' => '@app/mail',
];

$config['components']['request']['enableCsrfValidation'] = false;

return $config;
