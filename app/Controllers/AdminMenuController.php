<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\MenuService;
use InvalidArgumentException;

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
            'data' => $this->menuService->listMenus(),
        ]);
    }

    public function createCategory(): void
    {
        $this->guard(fn() => jsonResponse(['data' => $this->menuService->createCategory(requestJson())], 201));
    }

    public function updateCategory(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $this->guard(fn() => jsonResponse(['data' => $this->menuService->updateCategory($id, requestJson())]));
    }

    public function deleteCategory(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $this->guard(function () use ($id): void {
            $this->menuService->deleteCategory($id);
            jsonResponse(['message' => '分类删除成功']);
        });
    }

    public function createMenuItem(): void
    {
        $this->guard(fn() => jsonResponse(['data' => $this->menuService->createMenuItem(requestJson())], 201));
    }

    public function updateMenuItem(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $this->guard(fn() => jsonResponse(['data' => $this->menuService->updateMenuItem($id, requestJson())]));
    }

    public function deleteMenuItem(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $this->guard(function () use ($id): void {
            $this->menuService->deleteMenuItem($id);
            jsonResponse(['message' => '菜品删除成功']);
        });
    }

    private function guard(callable $handler): void
    {
        try {
            $handler();
        } catch (InvalidArgumentException $e) {
            jsonResponse(['message' => $e->getMessage()], 422);
        }
    }
}
