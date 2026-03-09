<?php

declare(strict_types=1);

namespace App\Services\Payment;

class NullPaymentGateway implements PaymentGatewayInterface
{
    public function createPaymentOrder(array $payload): array
    {
        return [
            'implemented' => false,
            'message' => 'payment gateway not implemented',
            'payload' => $payload,
        ];
    }

    public function queryPaymentStatus(string $outTradeNo): array
    {
        return [
            'implemented' => false,
            'out_trade_no' => $outTradeNo,
            'status' => 'unknown',
            'message' => 'payment gateway not implemented',
        ];
    }

    public function handleCallback(array $payload): array
    {
        return [
            'implemented' => false,
            'message' => 'payment gateway not implemented',
            'payload' => $payload,
        ];
    }
}
