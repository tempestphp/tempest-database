<?php

declare(strict_types=1);

namespace Tempest\Database;

interface Migration
{
    public function getName(): string;

    public function up(): QueryStatement|null;

    public function down(): QueryStatement|null;
}
