<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PaymentService;

class OrderPaymentController
{
    public function __construct(
        private readonly PaymentService $paymentService = new PaymentService()
    ) {
    }

    public function createPaymentIntent(): void
    {
        jsonResponse([
            'module' => 'order-payment',
            'payment' => $this->paymentService->createIntent(),
        ], 202);
    }

    public function paymentStatus(): void
    {
        jsonResponse([
            'module' => 'order-payment',
            'status' => $this->paymentService->status(),
            'note' => 'Payment channel integration is intentionally pending.',
        ]);
    }
}
