<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host'     => env('DB_HOST_ETP', 'localhost'),
            'port'     => env('DB_PORT_ETP', '1433'),
            'database' => env('DB_DATABASE_ETP', 'forge'),
            'username' => env('DB_USERNAME_ETP', 'forge'),
            'password' => env('DB_PASSWORD_ETP', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
            'prefix_indexes' => true,
            'encrypt'  => env('DB_ENCRYPT_ETP', 'no'),  // Optional: Set this to 'no' if you're not using encryption
            'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE_ETP', 'true'), // Optional
            'options'  => [
                'TrustServerCertificate' => true,
                'MultipleActiveResultSets' => true,
            ],
        ],

        'oracle' => [
            'driver'        => 'oracle',
            'tns'           => env('DB_ORACLE_TNS', ''),
            'host'          => env('DB_ORACLE_HOST', ''),
            'port'          => env('DB_ORACLE_PORT', '1521'),
            'database'      => env('DB_ORACLE_DATABASE', ''),
            'username'      => env('DB_ORACLE_USERNAME', ''),
            'password'      => env('DB_ORACLE_PASSWORD', ''),
            'charset'       => env('DB_ORACLE_CHARSET', 'AL32UTF8'),
            'prefix'        => env('DB_ORACLE_PREFIX', ''),
            'prefix_schema' => env('DB_ORACLE_SCHEMA_PREFIX', ''),
        ],

        'dimfs' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL_DIMFS'),
            'host' => env('DB_HOST_DIMFS', '127.0.0.1'),
            'port' => env('DB_PORT_DIMFS', '3306'),
            'database' => env('DB_DATABASE_DIMFS', 'forge'),
            'username' => env('DB_USERNAME_DIMFS', 'forge'),
            'password' => env('DB_PASSWORD_DIMFS', ''),
            'unix_socket' => env('DB_SOCKET_DIMFS', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'aimfs' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL_AIMFS'),
            'host' => env('DB_HOST_AIMFS', '127.0.0.1'),
            'port' => env('DB_PORT_AIMFS', '3306'),
            'database' => env('DB_DATABASE_AIMFS', 'forge'),
            'username' => env('DB_USERNAME_AIMFS', 'forge'),
            'password' => env('DB_PASSWORD_AIMFS', ''),
            'unix_socket' => env('DB_SOCKET_AIMFS', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'masterfile' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL_MASTER'),
            'host' => env('DB_HOST_MASTER', '127.0.0.1'),
            'port' => env('DB_PORT_MASTER', '3306'),
            'database' => env('DB_DATABASE_MASTER', 'forge'),
            'username' => env('DB_USERNAME_MASTER', 'forge'),
            'password' => env('DB_PASSWORD_MASTER', ''),
            'unix_socket' => env('DB_SOCKET_MASTER', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
