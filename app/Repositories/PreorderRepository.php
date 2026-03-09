<?php

declare(strict_types=1);

namespace App\Repositories;

class PreorderRepository
{
    private string $file;

    public function __construct()
    {
        $this->file = dirname(__DIR__, 2) . '/storage/preorders.json';
    }

    public function all(): array
    {
        $rows = $this->read();
        usort($rows, static fn(array $a, array $b): int => (int) $b['id'] <=> (int) $a['id']);
        return $rows;
    }

    public function create(array $payload): array
    {
        $rows = $this->read();
        $ids = array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $rows);
        $nextId = ($ids !== [] ? max($ids) : 100) + 1;
        $payload['id'] = $nextId;
        $rows[] = $payload;
        $this->write($rows);

        return $payload;
    }

    private function read(): array
    {
        if (!is_file($this->file)) {
            return [];
        }

        $raw = file_get_contents($this->file);
        $decoded = $raw !== false ? json_decode($raw, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    private function write(array $rows): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->file, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
