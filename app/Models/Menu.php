<?php

declare(strict_types=1);

namespace App\Models;

class MenuCategory
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $sort,
        public readonly string $status,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {
    }
}

class MenuItem
{
    public function __construct(
        public readonly int $id,
        public readonly int $categoryId,
        public readonly string $name,
        public readonly string $description,
        public readonly float $price,
        public readonly string $imageUrl,
        public readonly string $status,
        public readonly int $stock,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {
    }
}
