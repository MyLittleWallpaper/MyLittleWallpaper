<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\RepositoryBuilder;

$repository = RepositoryBuilder::createWithNoAdapters()
    ->addAdapter(EnvConstAdapter::class)
    ->immutable()->make();

$dotenv = Dotenv::create($repository, dirname(__DIR__));
$dotenv->load();
$dotenv->required(
    [
        'FILE_FOLDER',
        'DBHOST',
        'DBNAME',
        'DBUSER',
        'DBPASS',
    ]
);
