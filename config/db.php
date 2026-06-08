<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => $_ENV['DB_DSN'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 24*60*60,
    'schemaCache' => 'cache',
];
