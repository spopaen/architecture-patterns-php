<?php

namespace App;

class OrderLine
{
    public function __construct(
        private string $orderid,
        private string $sku,
        private int $quantity
    ) { }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
