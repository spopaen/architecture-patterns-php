<?php

namespace App;

class Batch
{
    private array $allocations = [];

    private int $purchasedQuantity = 0;

    public function __construct(
        private readonly string     $reference,
        private readonly string     $sku,
        int $quantity,
        private readonly ?\DateTime $eta = null,
    )
    {
        $this->purchasedQuantity = $quantity;
    }

    public function allocate(OrderLine $line): void
    {
        if ($this->canAllocate($line)) {
            $this->allocations[] = $line;
        }
    }

    public function deallocate(OrderLine $line): void
    {
        foreach ($this->allocations as $key => $allocation) {
            if ($allocation === $line) {
                unset($this->allocations[$key]);
            }
        }
    }

    public function canAllocate(OrderLine $line): bool
    {
        return $this->sku === $line->getSku() && $this->getAvailableQuantity() >= $line->getQuantity();
    }

    public function getAllocatedQuantity(): int {
        return array_reduce($this->allocations, function ($sum, $line) {
            return $sum + $line->getQuantity();
        }, 0);
    }

    public function getAvailableQuantity(): int {
        return $this->purchasedQuantity - $this->getAllocatedQuantity();
    }
}
