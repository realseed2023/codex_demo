<?php

declare(strict_types=1);

namespace App\Repositories;

class MenuRepository
{
    private string $file;

    public function __construct()
    {
        $this->file = dirname(__DIR__, 2) . '/storage/menu_data.json';
    }

    public function getData(): array
    {
        if (!is_file($this->file)) {
            $this->seed();
        }

        $raw = file_get_contents($this->file);
        $data = $raw !== false ? json_decode($raw, true) : null;

        if (!is_array($data) || !isset($data['categories'], $data['menu_items'])) {
            $this->seed();
            $raw = file_get_contents($this->file);
            $data = $raw !== false ? json_decode($raw, true) : null;
        }

        return is_array($data) ? $data : ['categories' => [], 'menu_items' => []];
    }

    public function saveData(array $data): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function seed(): void
    {
        $now = date('c');
        $this->saveData([
            'categories' => [
                ['id' => 1, 'name' => '招牌', 'sort' => 1, 'status' => 'enabled', 'created_at' => $now, 'updated_at' => $now],
                ['id' => 2, 'name' => '饮品', 'sort' => 2, 'status' => 'enabled', 'created_at' => $now, 'updated_at' => $now],
            ],
            'menu_items' => [
                [
                    'id' => 1,
                    'category_id' => 1,
                    'name' => 'Signature Noodles',
                    'description' => 'Chef recommended noodles',
                    'price' => 36,
                    'image_url' => '',
                    'status' => 'on_sale',
                    'stock' => 100,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 2,
                    'category_id' => 2,
                    'name' => 'Iced Lemon Tea',
                    'description' => 'Fresh brewed tea with lemon',
                    'price' => 12,
                    'image_url' => '',
                    'status' => 'on_sale',
                    'stock' => 100,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
        ]);
    }
}
