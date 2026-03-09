<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PreorderRepository;
use InvalidArgumentException;

class PreorderService
{
    public function __construct(
        private readonly PreorderRepository $preorderRepository = new PreorderRepository(),
        private readonly MenuService $menuService = new MenuService()
    ) {
    }

    public function listPreorders(): array
    {
        return $this->preorderRepository->all();
    }

    public function createPreorder(array $payload): array
    {
        $tableCode = trim((string) ($payload['table_code'] ?? ''));
        if ($tableCode === '') {
            throw new InvalidArgumentException('table_code 必填');
        }

        $items = $payload['items'] ?? [];
        if (!is_array($items) || $items === []) {
            throw new InvalidArgumentException('items 不能为空');
        }

        $lines = [];
        $totalAmount = 0.0;
        foreach ($items as $line) {
            $itemId = (int) ($line['menu_item_id'] ?? 0);
            $qty = (int) ($line['qty'] ?? 0);
            $item = $this->menuService->validatePreorderItem($itemId, $qty);
            $lineAmount = (float) $item['price'] * $qty;
            $totalAmount += $lineAmount;

            $lines[] = [
                'menu_item_id' => $itemId,
                'name' => $item['name'],
                'price' => (float) $item['price'],
                'qty' => $qty,
                'amount' => $lineAmount,
            ];
        }

        return $this->preorderRepository->create([
            'table_code' => $tableCode,
            'status' => 'draft',
            'items' => $lines,
            'total_amount' => $totalAmount,
            'created_at' => date('c'),
        ]);
    }
}
