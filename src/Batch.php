<?php

namespace App;

class Batch
{
    public function __construct(
        private string $reference,
        private string $sku,
        private int $availableQuantity,
        private ?\DateTime $eta = null
    ) { }

    public function allocate(OrderLine $line): void
    {
        $this->availableQuantity -= $line->getQuantity();
    }

    public function getAvailableQuantity(): int
    {
        return $this->availableQuantity;
    }
}
