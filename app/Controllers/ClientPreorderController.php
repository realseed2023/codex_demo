<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PreorderService;
use InvalidArgumentException;

class ClientPreorderController
{
    public function __construct(
        private readonly PreorderService $preorderService = new PreorderService()
    ) {
    }

    public function index(): void
    {
        jsonResponse([
            'module' => 'client-preorder',
            'preorders' => $this->preorderService->listPreorders(),
        ]);
    }

    public function store(): void
    {
        try {
            $preorder = $this->preorderService->createPreorder(requestJson());
            jsonResponse([
                'module' => 'client-preorder',
                'data' => $preorder,
            ], 201);
        } catch (InvalidArgumentException $e) {
            jsonResponse([
                'module' => 'client-preorder',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
