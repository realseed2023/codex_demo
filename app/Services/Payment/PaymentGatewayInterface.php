<?php

declare(strict_types=1);

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    public function createPaymentOrder(array $payload): array;

    public function queryPaymentStatus(string $outTradeNo): array;

    public function handleCallback(array $payload): array;
}
