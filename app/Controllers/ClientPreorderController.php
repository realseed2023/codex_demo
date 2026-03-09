<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\MenuService;
use App\Services\PreorderService;
use InvalidArgumentException;

class ClientPreorderController
{
    public function __construct(
        private readonly PreorderService $preorderService = new PreorderService(),
        private readonly MenuService $menuService = new MenuService()
    ) {
    }

    public function index(): void
    {
        jsonResponse([
            'module' => 'client-preorder',
            'preorders' => $this->preorderService->listPreorders(),
            'status_flow' => $this->preorderService->allowedStatusFlow(),
        ]);
    }

    public function validateTable(): void
    {
        try {
            $tableCode = (string) ($_GET['table_code'] ?? '');
            jsonResponse([
                'module' => 'client-preorder',
                'data' => $this->preorderService->validateTableCode($tableCode),
            ]);
        } catch (InvalidArgumentException $e) {
            jsonResponse([
                'module' => 'client-preorder',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function menu(): void
    {
        jsonResponse([
            'module' => 'client-preorder',
            'menu' => $this->menuService->publicMenu(),
        ]);
    }

    public function cart(): void
    {
        try {
            $draft = $this->preorderService->createOrUpdateCart(requestJson());
            jsonResponse([
                'module' => 'client-preorder',
                'data' => $draft,
            ], 201);
        } catch (InvalidArgumentException $e) {
            jsonResponse([
                'module' => 'client-preorder',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function submit(): void
    {
        try {
            $order = $this->preorderService->submitPreorder(requestJson());
            jsonResponse([
                'module' => 'client-preorder',
                'data' => $order,
                'payment_option' => 'pay_later',
            ], 201);
        } catch (InvalidArgumentException $e) {
            jsonResponse([
                'module' => 'client-preorder',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
