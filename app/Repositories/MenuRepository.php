<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class MenuRepository
{
    private string $driver;
    private string $file;

    public function __construct()
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';
        $this->driver = strtolower((string) ($config['driver'] ?? 'json'));
        $this->file = dirname(__DIR__, 2) . '/storage/menu_data.json';
    }

    public function getData(): array
    {
        return $this->driver === 'mysql' ? $this->getDataFromMysql() : $this->getDataFromJson();
    }

    public function saveData(array $data): void
    {
        if ($this->driver === 'mysql') {
            $this->saveDataToMysql($data);
            return;
        }

        $this->saveDataToJson($data);
    }

    private function getDataFromJson(): array
    {
        if (!is_file($this->file)) {
            $this->seedJson();
        }

        $raw = file_get_contents($this->file);
        $data = $raw !== false ? json_decode($raw, true) : null;

        if (!is_array($data) || !isset($data['categories'], $data['menu_items'])) {
            $this->seedJson();
            $raw = file_get_contents($this->file);
            $data = $raw !== false ? json_decode($raw, true) : null;
        }

        return is_array($data) ? $data : ['categories' => [], 'menu_items' => []];
    }

    private function saveDataToJson(array $data): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function seedJson(): void
    {
        $now = date('c');
        $this->saveDataToJson([
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

    private function getDataFromMysql(): array
    {
        $pdo = Database::connection();
        $this->ensureMysqlSchema($pdo);

        $categoryRows = $pdo->query('SELECT id,name,sort,status,created_at,updated_at FROM categories ORDER BY sort ASC, id ASC')->fetchAll();
        $menuRows = $pdo->query('SELECT id,category_id,name,description,price,image_url,status,stock,created_at,updated_at FROM menu_items ORDER BY id ASC')->fetchAll();

        if ($categoryRows === [] && $menuRows === []) {
            $this->seedMysql($pdo);
            $categoryRows = $pdo->query('SELECT id,name,sort,status,created_at,updated_at FROM categories ORDER BY sort ASC, id ASC')->fetchAll();
            $menuRows = $pdo->query('SELECT id,category_id,name,description,price,image_url,status,stock,created_at,updated_at FROM menu_items ORDER BY id ASC')->fetchAll();
        }

        return [
            'categories' => array_map([$this, 'normalizeCategoryRow'], $categoryRows),
            'menu_items' => array_map([$this, 'normalizeMenuItemRow'], $menuRows),
        ];
    }

    private function saveDataToMysql(array $data): void
    {
        $pdo = Database::connection();
        $this->ensureMysqlSchema($pdo);

        $pdo->beginTransaction();
        try {
            $pdo->exec('DELETE FROM menu_items');
            $pdo->exec('DELETE FROM categories');

            $categoryStmt = $pdo->prepare('INSERT INTO categories (id,name,sort,status,created_at,updated_at) VALUES (:id,:name,:sort,:status,:created_at,:updated_at)');
            foreach ($data['categories'] ?? [] as $category) {
                $categoryStmt->execute([
                    ':id' => (int) $category['id'],
                    ':name' => (string) $category['name'],
                    ':sort' => (int) $category['sort'],
                    ':status' => (string) $category['status'],
                    ':created_at' => (string) $category['created_at'],
                    ':updated_at' => (string) $category['updated_at'],
                ]);
            }

            $menuStmt = $pdo->prepare('INSERT INTO menu_items (id,category_id,name,description,price,image_url,status,stock,created_at,updated_at) VALUES (:id,:category_id,:name,:description,:price,:image_url,:status,:stock,:created_at,:updated_at)');
            foreach ($data['menu_items'] ?? [] as $item) {
                $menuStmt->execute([
                    ':id' => (int) $item['id'],
                    ':category_id' => (int) $item['category_id'],
                    ':name' => (string) $item['name'],
                    ':description' => (string) ($item['description'] ?? ''),
                    ':price' => (float) $item['price'],
                    ':image_url' => (string) ($item['image_url'] ?? ''),
                    ':status' => (string) $item['status'],
                    ':stock' => (int) $item['stock'],
                    ':created_at' => (string) $item['created_at'],
                    ':updated_at' => (string) $item['updated_at'],
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private function ensureMysqlSchema(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            sort INT NOT NULL DEFAULT 0,
            status ENUM('enabled','disabled') NOT NULL DEFAULT 'enabled',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_id INT NOT NULL,
            name VARCHAR(120) NOT NULL,
            description TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            image_url VARCHAR(255) NOT NULL DEFAULT '',
            status ENUM('on_sale','off_sale') NOT NULL DEFAULT 'off_sale',
            stock INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX idx_category_id (category_id),
            CONSTRAINT fk_menu_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    private function seedMysql(PDO $pdo): void
    {
        $now = date('Y-m-d H:i:s');
        $pdo->exec("INSERT INTO categories (id,name,sort,status,created_at,updated_at) VALUES
            (1,'招牌',1,'enabled','{$now}','{$now}'),
            (2,'饮品',2,'enabled','{$now}','{$now}')");

        $pdo->exec("INSERT INTO menu_items (id,category_id,name,description,price,image_url,status,stock,created_at,updated_at) VALUES
            (1,1,'Signature Noodles','Chef recommended noodles',36,'','on_sale',100,'{$now}','{$now}'),
            (2,2,'Iced Lemon Tea','Fresh brewed tea with lemon',12,'','on_sale',100,'{$now}','{$now}')");
    }

    private function normalizeCategoryRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'sort' => (int) $row['sort'],
            'status' => (string) $row['status'],
            'created_at' => $this->toIso8601((string) $row['created_at']),
            'updated_at' => $this->toIso8601((string) $row['updated_at']),
        ];
    }

    private function normalizeMenuItemRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'category_id' => (int) $row['category_id'],
            'name' => (string) $row['name'],
            'description' => (string) $row['description'],
            'price' => (float) $row['price'],
            'image_url' => (string) $row['image_url'],
            'status' => (string) $row['status'],
            'stock' => (int) $row['stock'],
            'created_at' => $this->toIso8601((string) $row['created_at']),
            'updated_at' => $this->toIso8601((string) $row['updated_at']),
        ];
    }

    private function toIso8601(string $datetime): string
    {
        return date('c', strtotime($datetime));
    }
}
