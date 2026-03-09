<?php

declare(strict_types=1);

namespace App\Repositories;

class MenuRepository
{
    public function all(): array
    {
        return [
            ['id' => 1, 'name' => 'Signature Noodles', 'price' => 36],
            ['id' => 2, 'name' => 'Iced Lemon Tea', 'price' => 12],
        ];
    }
}
