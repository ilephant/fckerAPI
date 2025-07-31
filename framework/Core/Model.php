<?php

namespace Fcker\Framework\Core;

use PDO;
use PDOException;

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = ['password'];

    public function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $host = getenv('DB_HOST') ?: 'db';
            $port = getenv('DB_PORT') ?: 3306;
            $database = getenv('DB_DATABASE') ?: 'fcker_api';
            $username = getenv('DB_USERNAME') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';
            
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->db = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function all(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $this->hideAttributes($stmt->fetchAll());
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ? $this->hideAttributes($result) : null;
    }

    public function findBy(string $column, mixed $value): ?array
    {
        if (!in_array($column, $this->fillable, true)) {
            throw new \InvalidArgumentException("Invalid column name: {$column}");
        }

        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        
        return $result ? $this->hideAttributes($result) : null;
    }

    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        $setClause = implode(', ', array_map(fn($key) => "{$key} = :{$key}", array_keys($data)));
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id");
        $data['id'] = $id;
        
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function hideAttributes(array $data): array
    {
        if (empty($this->hidden)) {
            return $data;
        }
        
        return array_diff_key($data, array_flip($this->hidden));
    }

    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
