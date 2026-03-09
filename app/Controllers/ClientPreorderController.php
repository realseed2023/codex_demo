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
        if (!wantsJson()) {
            view('client.preorders', [
                'preorders' => $this->preorderService->listPreorders(),
                'statusFlow' => $this->preorderService->allowedStatusFlow(),
                'menu' => $this->menuService->publicMenu(),
            ]);

            return;
        }

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

            if (!wantsJson()) {
                view('client.table-validate', [
                    'tableCode' => $tableCode,
                    'result' => $this->preorderService->validateTableCode($tableCode),
                ]);

                return;
            }

            jsonResponse([
                'module' => 'client-preorder',
                'data' => $this->preorderService->validateTableCode($tableCode),
            ]);
        } catch (InvalidArgumentException $e) {
            if (!wantsJson()) {
                view('client.table-validate', [
                    'tableCode' => (string) ($_GET['table_code'] ?? ''),
                    'error' => $e->getMessage(),
                ]);

                return;
            }

            jsonResponse([
                'module' => 'client-preorder',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function menu(): void
    {
        if (!wantsJson()) {
            view('client.menu', [
                'menu' => $this->menuService->publicMenu(),
            ]);

            return;
        }

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
