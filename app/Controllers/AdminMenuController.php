<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\MenuService;

class AdminMenuController
{
    public function __construct(
        private readonly MenuService $menuService = new MenuService()
    ) {
    }

    public function index(): void
    {
        jsonResponse([
            'module' => 'admin-menu',
            'menus' => $this->menuService->listMenus(),
        ]);
    }

    public function store(): void
    {
        jsonResponse([
            'module' => 'admin-menu',
            'message' => 'Menu creation placeholder completed.',
            'next_step' => 'Persist to database via repository.',
        ], 201);
    }
}
