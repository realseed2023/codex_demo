<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\MenuService;

class FrontendController
{
    public function __construct(
        private readonly MenuService $menuService = new MenuService()
    ) {
    }

    public function index(): void
    {
        view('frontend.index');
    }

    public function admin(): void
    {
        view('frontend.admin', [
            'menus' => $this->menuService->listMenus(),
            'publicMenu' => $this->menuService->publicMenu(),
        ]);
    }

    public function order(): void
    {
        view('frontend.order', [
            'tableCode' => trim((string) ($_GET['table_code'] ?? '')),
        ]);
    }
}
