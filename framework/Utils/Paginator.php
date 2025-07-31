<?php

namespace Fcker\Framework\Utils;

class Paginator
{
    private array $items;
    private int $total;
    private int $perPage;
    private int $currentPage;
    private int $lastPage;

    public function __construct(array $items, int $total, int $perPage, int $currentPage)
    {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->lastPage = (int) ceil($total / $perPage);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    public function getFrom(): int
    {
        return ($this->currentPage - 1) * $this->perPage + 1;
    }

    public function getTo(): int
    {
        return min($this->currentPage * $this->perPage, $this->total);
    }

    public function toArray(): array
    {
        return [
            'data' => $this->items,
            'pagination' => [
                'current_page' => $this->currentPage,
                'per_page' => $this->perPage,
                'total' => $this->total,
                'last_page' => $this->lastPage,
                'from' => $this->getFrom(),
                'to' => $this->getTo(),
                'has_more_pages' => $this->hasMorePages(),
                'has_pages' => $this->hasPages()
            ]
        ];
    }

    public static function create(array $items, int $total, int $perPage, int $currentPage): self
    {
        return new self($items, $total, $perPage, $currentPage);
    }
} 