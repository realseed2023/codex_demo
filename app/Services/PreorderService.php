<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PreorderRepository;

class PreorderService
{
    public function __construct(
        private readonly PreorderRepository $preorderRepository = new PreorderRepository()
    ) {
    }

    public function listPreorders(): array
    {
        return $this->preorderRepository->all();
    }
}
