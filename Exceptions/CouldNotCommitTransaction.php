<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

final class CouldNotCommitTransaction extends DatabaseException
{
    public function __construct()
    {
        parent::__construct('Cannot commit transaction');
    }
}
