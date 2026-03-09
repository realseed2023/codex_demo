<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\MenuService;

class MenuController
{
    public function __construct(
        private readonly MenuService $menuService = new MenuService()
    ) {
    }

    public function index(): void
    {
        jsonResponse([
            'module' => 'public-menu',
            'categories' => $this->menuService->publicMenu(),
        ]);
    }
}
