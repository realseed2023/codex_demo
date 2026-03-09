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
            'payment' => $this->paymentService->createIntent(requestJson()),
        ], 202);
    }

    public function paymentStatus(): void
    {
        $status = $this->paymentService->status((string) ($_GET['out_trade_no'] ?? ''));

        if (!wantsJson()) {
            view('payment.status', [
                'status' => $status,
                'outTradeNo' => (string) ($_GET['out_trade_no'] ?? ''),
            ]);

            return;
        }

        jsonResponse([
            'module' => 'order-payment',
            'status' => $status,
            'note' => 'Payment channel integration is intentionally pending.',
        ]);
    }

    public function callback(): void
    {
        jsonResponse([
            'module' => 'order-payment',
            'result' => $this->paymentService->callback(requestJson()),
        ], 202);
    }
}
