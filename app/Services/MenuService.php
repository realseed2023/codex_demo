<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MenuRepository;
use InvalidArgumentException;

class MenuService
{
    public function __construct(
        private readonly MenuRepository $menuRepository = new MenuRepository()
    ) {
    }

    public function listMenus(): array
    {
        $data = $this->menuRepository->getData();

        return [
            'categories' => $data['categories'],
            'menu_items' => $data['menu_items'],
        ];
    }

    public function createCategory(array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('分类名称必填');
        }

        $data = $this->menuRepository->getData();
        $now = date('c');
        $id = $this->nextId($data['categories']);

        $category = [
            'id' => $id,
            'name' => $name,
            'sort' => (int) ($payload['sort'] ?? (count($data['categories']) + 1)),
            'status' => $this->normalizeCategoryStatus((string) ($payload['status'] ?? 'enabled')),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $data['categories'][] = $category;
        $this->menuRepository->saveData($data);

        return $category;
    }

    public function updateCategory(int $id, array $payload): array
    {
        $data = $this->menuRepository->getData();
        foreach ($data['categories'] as &$category) {
            if ((int) $category['id'] !== $id) {
                continue;
            }

            if (isset($payload['name'])) {
                $name = trim((string) $payload['name']);
                if ($name === '') {
                    throw new InvalidArgumentException('分类名称必填');
                }
                $category['name'] = $name;
            }

            if (isset($payload['sort'])) {
                $category['sort'] = (int) $payload['sort'];
            }

            if (isset($payload['status'])) {
                $category['status'] = $this->normalizeCategoryStatus((string) $payload['status']);
            }

            $category['updated_at'] = date('c');
            $this->menuRepository->saveData($data);
            return $category;
        }

        throw new InvalidArgumentException('分类不存在');
    }

    public function deleteCategory(int $id): void
    {
        $data = $this->menuRepository->getData();
        foreach ($data['menu_items'] as $item) {
            if ((int) $item['category_id'] === $id) {
                throw new InvalidArgumentException('分类下存在菜品，无法删除');
            }
        }

        $before = count($data['categories']);
        $data['categories'] = array_values(array_filter($data['categories'], static fn(array $c): bool => (int) $c['id'] !== $id));
        if (count($data['categories']) === $before) {
            throw new InvalidArgumentException('分类不存在');
        }

        $this->menuRepository->saveData($data);
    }

    public function createMenuItem(array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('菜品名称必填');
        }

        $price = (float) ($payload['price'] ?? -1);
        if ($price < 0) {
            throw new InvalidArgumentException('价格必须为非负数');
        }

        $categoryId = (int) ($payload['category_id'] ?? 0);
        if (!$this->categoryExists($categoryId)) {
            throw new InvalidArgumentException('分类不存在');
        }

        $stock = (int) ($payload['stock'] ?? 0);
        if ($stock < 0) {
            throw new InvalidArgumentException('库存不能小于0');
        }

        $data = $this->menuRepository->getData();
        $now = date('c');
        $item = [
            'id' => $this->nextId($data['menu_items']),
            'category_id' => $categoryId,
            'name' => $name,
            'description' => (string) ($payload['description'] ?? ''),
            'price' => $price,
            'image_url' => (string) ($payload['image_url'] ?? ''),
            'status' => $this->normalizeItemStatus((string) ($payload['status'] ?? 'off_sale')),
            'stock' => $stock,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $data['menu_items'][] = $item;
        $this->menuRepository->saveData($data);

        return $item;
    }

    public function updateMenuItem(int $id, array $payload): array
    {
        $data = $this->menuRepository->getData();
        foreach ($data['menu_items'] as &$item) {
            if ((int) $item['id'] !== $id) {
                continue;
            }

            if (isset($payload['category_id'])) {
                $categoryId = (int) $payload['category_id'];
                if (!$this->categoryExists($categoryId)) {
                    throw new InvalidArgumentException('分类不存在');
                }
                $item['category_id'] = $categoryId;
            }

            if (isset($payload['name'])) {
                $name = trim((string) $payload['name']);
                if ($name === '') {
                    throw new InvalidArgumentException('菜品名称必填');
                }
                $item['name'] = $name;
            }

            if (isset($payload['price'])) {
                $price = (float) $payload['price'];
                if ($price < 0) {
                    throw new InvalidArgumentException('价格必须为非负数');
                }
                $item['price'] = $price;
            }

            if (isset($payload['description'])) {
                $item['description'] = (string) $payload['description'];
            }

            if (isset($payload['image_url'])) {
                $item['image_url'] = (string) $payload['image_url'];
            }

            if (isset($payload['status'])) {
                $item['status'] = $this->normalizeItemStatus((string) $payload['status']);
            }

            if (isset($payload['stock'])) {
                $stock = (int) $payload['stock'];
                if ($stock < 0) {
                    throw new InvalidArgumentException('库存不能小于0');
                }
                $item['stock'] = $stock;
            }

            $item['updated_at'] = date('c');
            $this->menuRepository->saveData($data);
            return $item;
        }

        throw new InvalidArgumentException('菜品不存在');
    }

    public function deleteMenuItem(int $id): void
    {
        $data = $this->menuRepository->getData();
        $before = count($data['menu_items']);
        $data['menu_items'] = array_values(array_filter($data['menu_items'], static fn(array $i): bool => (int) $i['id'] !== $id));
        if (count($data['menu_items']) === $before) {
            throw new InvalidArgumentException('菜品不存在');
        }

        $this->menuRepository->saveData($data);
    }

    public function publicMenu(): array
    {
        $data = $this->menuRepository->getData();
        $grouped = [];

        foreach ($data['categories'] as $category) {
            if (($category['status'] ?? 'disabled') !== 'enabled') {
                continue;
            }

            $items = array_values(array_filter($data['menu_items'], static function (array $item) use ($category): bool {
                return (int) $item['category_id'] === (int) $category['id']
                    && ($item['status'] ?? 'off_sale') === 'on_sale'
                    && (int) ($item['stock'] ?? 0) > 0;
            }));

            if ($items === []) {
                continue;
            }

            $grouped[] = [
                'id' => $category['id'],
                'name' => $category['name'],
                'sort' => $category['sort'],
                'items' => $items,
            ];
        }

        usort($grouped, static fn(array $a, array $b): int => (int) $a['sort'] <=> (int) $b['sort']);

        return $grouped;
    }

    public function validatePreorderItem(int $itemId, int $qty): array
    {
        $data = $this->menuRepository->getData();
        foreach ($data['menu_items'] as $item) {
            if ((int) $item['id'] !== $itemId) {
                continue;
            }

            if (($item['status'] ?? 'off_sale') !== 'on_sale' || (int) ($item['stock'] ?? 0) <= 0) {
                throw new InvalidArgumentException('下架菜品不可加入预下单');
            }

            if ($qty <= 0) {
                throw new InvalidArgumentException('购买数量必须大于0');
            }

            if ((int) $item['stock'] < $qty) {
                throw new InvalidArgumentException('库存不足');
            }

            return $item;
        }

        throw new InvalidArgumentException('菜品不存在');
    }

    private function categoryExists(int $categoryId): bool
    {
        $data = $this->menuRepository->getData();
        foreach ($data['categories'] as $category) {
            if ((int) $category['id'] === $categoryId) {
                return true;
            }
        }

        return false;
    }

    private function nextId(array $rows): int
    {
        $ids = array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $rows);
        return ($ids !== [] ? max($ids) : 0) + 1;
    }

    private function normalizeCategoryStatus(string $status): string
    {
        return in_array($status, ['enabled', 'disabled'], true) ? $status : 'enabled';
    }

    private function normalizeItemStatus(string $status): string
    {
        return in_array($status, ['on_sale', 'off_sale'], true) ? $status : 'off_sale';
    }
}
