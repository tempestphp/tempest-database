<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class TextStatement implements QueryStatement
{
    public function __construct(
        private string $name,
        private bool $nullable = false,
        private ?string $default = null,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            '`%s` TEXT %s %s',
            $this->name,
            $this->default ? "DEFAULT \"{$this->default}\"" : '',
            $this->nullable ? '' : 'NOT NULL',
        );
    }
}
