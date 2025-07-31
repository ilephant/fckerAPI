<?php

namespace Fcker\Application\Models;

use Fcker\Framework\Core\Model;

class PostModel extends Model
{
    protected string $table = 'posts';
    protected string $primaryKey = 'id';
    protected array $fillable = ['title', 'content', 'user_id', 'status'];
    protected array $hidden = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function getWithUser(int $id): ?array
    {
        $sql = "SELECT p.*, u.name as author_name, u.email as author_email 
                FROM {$this->table} p 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE p.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    public function getAllWithUser(int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT p.*, u.name as author_name, u.email as author_email 
                FROM {$this->table} p 
                LEFT JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        
        return $stmt->fetchAll();
    }

    public function getByUser(int $userId, int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit, $offset]);
        
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->fetch();
        
        return (int) $result['count'];
    }
} 