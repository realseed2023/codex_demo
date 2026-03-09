<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MenuRepository;

class MenuService
{
    public function __construct(
        private readonly MenuRepository $menuRepository = new MenuRepository()
    ) {
    }

    public function listMenus(): array
    {
        return $this->menuRepository->all();
    }
}
