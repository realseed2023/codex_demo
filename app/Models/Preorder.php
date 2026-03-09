<?php

declare(strict_types=1);

namespace App\Models;

class Preorder
{
    public function __construct(
        public readonly int $id,
        public readonly string $tableCode,
        public readonly string $status
    ) {
    }
}
