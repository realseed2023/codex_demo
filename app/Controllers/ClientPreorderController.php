<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PreorderService;

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
        jsonResponse([
            'module' => 'client-preorder',
            'message' => 'Table QR preorder API placeholder completed.',
            'next_step' => 'Validate table code and save preorder.',
        ], 201);
    }
}
