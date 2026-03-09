<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Payment\NullPaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;

class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayInterface $paymentGateway = new NullPaymentGateway()
    ) {
    }

    public function createIntent(array $payload = []): array
    {
        return $this->paymentGateway->createPaymentOrder($payload);
    }

    public function status(string $outTradeNo = ''): array
    {
        return $this->paymentGateway->queryPaymentStatus($outTradeNo);
    }

    public function callback(array $payload = []): array
    {
        return $this->paymentGateway->handleCallback($payload);
    }
}
