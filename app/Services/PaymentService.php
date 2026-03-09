<?php

declare(strict_types=1);

namespace App\Services;

class PaymentService
{
    public function createIntent(): array
    {
        return [
            'order_no' => 'ORDER_PLACEHOLDER_001',
            'payment_status' => 'pending',
            'provider' => 'reserved',
        ];
    }

    public function status(): array
    {
        return [
            'order_no' => 'ORDER_PLACEHOLDER_001',
            'payment_status' => 'pending',
            'allowed_transitions' => ['paid', 'failed', 'cancelled'],
        ];
    }
}
