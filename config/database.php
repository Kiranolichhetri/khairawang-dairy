<?php

declare(strict_types=1);

return [
    "default" => "mysql",

    "connections" => [
        "mysql" => [
            "driver" => "mysql",
            "host" => "127.0.0.1",
            "port" => 3306,
            "database" => "khairawang_dairy",
            "username" => "root",
            "password" => "",
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
        ],
        "mongodb" => [
            "driver" => "mongodb",
            "uri" => "mongodb+srv://kiranoli421_db_user:keFt9XKE7oIdaAY2@cluster0.dxc9xkf.mongodb.net/",
            "database" => "khairawang_dairy",
        ],
    ],

    "migrations" => [
        "table" => "migrations",
        "path" => "database/migrations",
    ],
];
