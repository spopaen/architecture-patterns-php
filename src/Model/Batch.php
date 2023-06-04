<?php

namespace App\Model;

class Batch
{
    private \Ds\Set $allocations;

    private int $purchasedQuantity = 0;

    public function __construct(
        private readonly string $reference,
        private readonly string $sku,
        int $quantity,
        private readonly ?\DateTime $eta = null,
    )
    {
        $this->purchasedQuantity = $quantity;
        $this->allocations = new \Ds\Set();
    }

    public function allocate(OrderLine $line): void
    {
        if ($this->canAllocate($line) && !$this->allocations->contains($line)) {
            $this->allocations->add($line);
        }
    }

    public function deallocate(OrderLine $line): void
    {
        if ($this->allocations->contains($line)) {
            $this->allocations->remove($line);
        }
    }

    public function canAllocate(OrderLine $line): bool
    {
        return $this->sku === $line->getSku() && $this->getAvailableQuantity() >= $line->getQuantity();
    }

    public function getAllocatedQuantity(): int {
        return array_reduce($this->allocations->toArray(), function ($sum, $line) {
            return $sum + $line->getQuantity();
        }, 0);
    }

    public function getAvailableQuantity(): int {
        return $this->purchasedQuantity - $this->getAllocatedQuantity();
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getEta(): ?\DateTime
    {
        return $this->eta;
    }
}
