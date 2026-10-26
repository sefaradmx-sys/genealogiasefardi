<?php
/**
 * Copia este archivo a config.php y ajusta los valores,
 * o deja que config.php cargue .env / .hostgator-db.json.
 */
return [
    'app_name'   => 'Genealogía Sefardí',
    'intake_notify_email' => 'orgullosefardi@gmail.com',
    'app_url'    => 'https://genealogiasefardi.site',
    'debug'      => false,
    'timezone'   => 'America/Monterrey',

    'db' => [
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'YOUR_DB_NAME',
        'user'     => 'YOUR_DB_USER',
        'password' => 'YOUR_DB_PASSWORD',
        'charset'  => 'utf8mb4',
    ],

    'session_name' => 'gsfardi_session',
];
