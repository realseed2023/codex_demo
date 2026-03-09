<?php

declare(strict_types=1);

namespace App\Models;

class Menu
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $price
    ) {
    }
}
