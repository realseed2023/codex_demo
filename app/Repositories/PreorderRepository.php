<?php

declare(strict_types=1);

namespace App\Repositories;

class PreorderRepository
{
    public function all(): array
    {
        return [
            ['id' => 101, 'table_code' => 'A-08', 'status' => 'draft'],
        ];
    }
}
