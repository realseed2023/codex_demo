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
        if (!wantsJson()) {
            view('menu.public', [
                'categories' => $this->menuService->publicMenu(),
            ]);

            return;
        }

        jsonResponse([
            'module' => 'public-menu',
            'categories' => $this->menuService->publicMenu(),
        ]);
    }
}
