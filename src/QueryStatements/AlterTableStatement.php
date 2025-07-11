<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\HasTrailingStatements;
use Tempest\Database\QueryStatement;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Database\model;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class AlterTableStatement implements QueryStatement, HasTrailingStatements
{
    private(set) array $trailingStatements = [];

    public function __construct(
        private readonly string $tableName,
        private array $statements = [],
    ) {}

    /** @param class-string $modelClass */
    public static function forModel(string $modelClass): self
    {
        return new self(model($modelClass)->getTableDefinition()->name);
    }

    public function add(QueryStatement $statement): self
    {
        $this->statements[] = new AlterAddColumnStatement($statement);

        return $this;
    }

    public function unique(string ...$columns): self
    {
        $this->trailingStatements[] = new UniqueStatement(
            tableName: $this->tableName,
            columns: $columns,
        );

        return $this;
    }

    public function index(string ...$columns): self
    {
        $this->trailingStatements[] = new IndexStatement(
            tableName: $this->tableName,
            columns: $columns,
        );

        return $this;
    }

    public function dropColumn(string $name): self
    {
        $this->statements[] = new AlterDropStatement(ColumnNameStatement::fromString($name));

        return $this;
    }

    public function dropConstraint(string $name): self
    {
        $this->statements[] = new AlterDropStatement(ConstraintNameStatement::fromString($name));

        return $this;
    }

    public function rename(string $from, string $to): self
    {
        $this->statements[] = new RenameColumnStatement(
            new IdentityStatement($from),
            new IdentityStatement($to),
        );

        return $this;
    }

    public function modify(QueryStatement $column): self
    {
        $this->statements[] = new ModifyColumnStatement($column);

        return $this;
    }

    public function compile(DatabaseDialect $dialect): string
    {
        if ($this->statements !== []) {
            $alterTable = sprintf(
                'ALTER TABLE %s %s;',
                new TableDefinition($this->tableName),
                arr($this->statements)
                    ->map(fn (QueryStatement $queryStatement) => str($queryStatement->compile($dialect))->trim()->replace('  ', ' '))
                    ->filter(fn (ImmutableString $line) => $line->isNotEmpty())
                    ->implode(', ' . PHP_EOL . '    ')
                    ->wrap(before: PHP_EOL . '    ', after: PHP_EOL)
                    ->toString(),
            );
        } else {
            $alterTable = '';
        }

        return $alterTable;
    }
}
