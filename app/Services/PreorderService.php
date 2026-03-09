<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PreorderRepository;
use App\Repositories\TableRepository;
use App\Services\Payment\NullPaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use InvalidArgumentException;

class PreorderService
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PAID = 'paid';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public function __construct(
        private readonly PreorderRepository $preorderRepository = new PreorderRepository(),
        private readonly MenuService $menuService = new MenuService(),
        private readonly TableRepository $tableRepository = new TableRepository(),
        private readonly PaymentGatewayInterface $paymentGateway = new NullPaymentGateway()
    ) {
    }

    public function listPreorders(): array
    {
        return $this->preorderRepository->all();
    }

    public function validateTableCode(string $tableCode): array
    {
        $cleanCode = trim($tableCode);
        if ($cleanCode === '') {
            throw new InvalidArgumentException('table_code 必填');
        }

        $table = $this->tableRepository->findByCode($cleanCode);
        if ($table === null || (string) ($table['status'] ?? '') !== 'active') {
            throw new InvalidArgumentException('无效桌码，禁止下单');
        }

        return $table;
    }

    public function createOrUpdateCart(array $payload): array
    {
        $table = $this->validateTableCode((string) ($payload['table_code'] ?? ''));
        $orderItems = $this->buildOrderItems($payload['items'] ?? []);

        $draftId = isset($payload['pre_order_id']) ? (int) $payload['pre_order_id'] : null;
        if ($draftId !== null && $draftId > 0) {
            $draft = $this->preorderRepository->findById($draftId);
            if ($draft === null || (string) ($draft['status'] ?? '') !== self::STATUS_DRAFT) {
                throw new InvalidArgumentException('草稿订单不存在或不可编辑');
            }
        }

        return $this->preorderRepository->saveDraft([
            'id' => $draftId,
            'table_id' => (int) $table['id'],
            'table_code' => (string) $table['table_code'],
            'subtotal_amount' => $orderItems['subtotal_amount'],
            'remark' => (string) ($payload['remark'] ?? ''),
            'items' => $orderItems['items'],
        ]);
    }

    public function submitPreorder(array $payload): array
    {
        $draft = $this->createOrUpdateCart($payload);
        $payNow = (bool) ($payload['pay_now'] ?? false);
        $finalStatus = $payNow ? self::STATUS_PENDING_PAYMENT : self::STATUS_SUBMITTED;

        $order = $this->preorderRepository->submit([
            'id' => (int) $draft['id'],
            'order_no' => $this->generateOrderNo(),
            'status' => $finalStatus,
            'remark' => (string) ($payload['remark'] ?? ($draft['remark'] ?? '')),
        ]);

        if ($payNow) {
            $this->paymentGateway->createPaymentOrder([
                'order_id' => (int) $order['id'],
                'order_no' => (string) $order['order_no'],
                'amount' => (float) $order['subtotal_amount'],
                'channel' => (string) ($payload['payment_channel'] ?? 'reserved'),
            ]);
        }

        return $order;
    }

    public function allowedStatusFlow(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_PENDING_PAYMENT,
            self::STATUS_PAID,
            self::STATUS_CONFIRMED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    private function buildOrderItems(array $items): array
    {
        if (!is_array($items) || $items === []) {
            throw new InvalidArgumentException('items 不能为空');
        }

        $lines = [];
        $subtotalAmount = 0.0;

        foreach ($items as $line) {
            $itemId = (int) ($line['menu_item_id'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? $line['qty'] ?? 0);
            if ($quantity <= 0) {
                throw new InvalidArgumentException('数量必须为正整数');
            }

            $item = $this->menuService->validatePreorderItem($itemId, $quantity);
            $unitPrice = (float) $item['price'];
            $lineAmount = $unitPrice * $quantity;
            $subtotalAmount += $lineAmount;

            $lines[] = [
                'menu_item_id' => $itemId,
                'item_name_snapshot' => (string) $item['name'],
                'unit_price_snapshot' => $unitPrice,
                'quantity' => $quantity,
                'line_amount' => $lineAmount,
            ];
        }

        return [
            'items' => $lines,
            'subtotal_amount' => $subtotalAmount,
        ];
    }

    private function generateOrderNo(): string
    {
        return 'PO' . date('YmdHis') . random_int(100, 999);
    }
}
