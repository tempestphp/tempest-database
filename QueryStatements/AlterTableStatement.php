<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final class AlterTableStatement implements QueryStatement
{
    public function __construct(
        private readonly string $tableName,
        private array $statements = [],
    ) {
    }

    public function add(QueryStatement $statement): self
    {
        $this->statements[] = new AlterStatement(Alter::ADD, $statement);

        return $this;
    }

    public function update(QueryStatement $statement): self
    {
        $this->statements[] = new AlterStatement(Alter::UPDATE, $statement);

        return $this;
    }

    public function delete(string $table): self
    {
        $this->statements[] = new AlterStatement(
            Alter::DELETE,
            new RawStatement($table)
        );

        return $this;
    }

    public function constraint(string $constraintName, ?QueryStatement $statement = null): self
    {
        $this->statements[] = new ConstraintStatement($constraintName, $statement);

        if ($statement !== null) {
            $this->statements[] = $statement;
        }

        return $this;
    }

    public function unique(string $columnName): self
    {
        $this->statements[] = new UniqueStatement($columnName);

        return $this;
    }

    public function index(string $indexName): self
    {
        $this->statements[] = new IndexStatement($indexName);

        return $this;
    }

    public function drop(QueryStatement $statement): self
    {
        $this->statements[] = new AlterStatement(Alter::DROP, $statement);

        return $this;
    }

    public function compile(DatabaseDialect $dialect): string
    {
        $compiled = sprintf(
            'ALTER TABLE %s %s;',
            new TableName($this->tableName),
            implode(
                ' ',
                array_filter(
                    array_map(
                        static fn (QueryStatement $statement) => $statement->compile($dialect),
                        $this->statements,
                    ),
                ),
            ),
        );

        return str_replace('  ', ' ', $compiled);
    }
}
