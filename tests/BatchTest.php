<?php

namespace App\Tests;

use App\Batch;
use App\OrderLine;
use PHPUnit\Framework\TestCase;

final class BatchTest extends TestCase
{
    public function test_allocating_to_a_batch_reduces_the_available_quantity()
    {
        $batch = new Batch("batch-001", "SMALL-TABLE", availableQuantity: 20, eta: new \DateTime());
        $line = new OrderLine('order-ref', "SMALL-TABLE", 2);
        $batch->allocate($line);
        $this->assertSame(18, $batch->getAvailableQuantity());
    }
}
