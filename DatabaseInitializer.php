<?php

declare(strict_types=1);

namespace Tempest\Database;

use PDO;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Database\Transactions\TransactionManager;

#[Singleton]
final readonly class DatabaseInitializer implements Initializer
{
    public function initialize(Container $container): Database
    {
        return new GenericDatabase(
            $container->get(PDO::class),
            $container->get(TransactionManager::class),
        );
    }
}
