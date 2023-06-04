<?php

namespace App\Tests;

use App\Exception\OutOfStockException;
use App\Model\Batch;
use App\Model\OrderLine;
use App\Service\Allocation;
use PHPUnit\Framework\TestCase;

final class AllocationTest extends TestCase
{
    public function test_allocating_to_a_batch_reduces_the_available_quantity()
    {
        $inStockBatch = new Batch("in-stock-batch", "RETRO-CLOCK", quantity: 100, eta: null);
        $shipmentBatch = new Batch("in-stock-batch", "RETRO-CLOCK", quantity: 100, eta: new \DateTime('Tomorrow'));
        $line = new OrderLine('order-ref', "RETRO-CLOCK", 10);

        Allocation::allocate($line, [$inStockBatch, $shipmentBatch]);

        $this->assertSame(90, $inStockBatch->getAvailableQuantity());
        $this->assertSame(100, $shipmentBatch->getAvailableQuantity());
    }

    public function test_prefers_earlier_batches()
    {
        $earliest = new Batch("speedy-batch", "RETRO-CLOCK", quantity: 100, eta: new \DateTime('Today'));
        $medium = new Batch("normal-batch", "RETRO-CLOCK", quantity: 100, eta: new \DateTime('Tomorrow'));
        $latest = new Batch("slow-batch", "RETRO-CLOCK", quantity: 100, eta: new \DateTime('Next week'));
        $line = new OrderLine('order-ref', "RETRO-CLOCK", 10);

        Allocation::allocate($line, [$medium, $earliest, $latest]);

        $this->assertSame(90, $earliest->getAvailableQuantity());
        $this->assertSame(100, $medium->getAvailableQuantity());
        $this->assertSame(100, $latest->getAvailableQuantity());
    }

    public function test_returns_allocated_batch_ref()
    {
        $inStockBatch = new Batch("in-stock-batch", "HIGHBROW-POSTER", quantity: 100, eta: null);
        $shipmentBatch = new Batch("shipment-batch-ref", "HIGHBROW-POSTER", quantity: 100, eta: new \DateTime('Tomorrow'));
        $line = new OrderLine('order-ref', "HIGHBROW-POSTER", 10);

        $allocation = Allocation::allocate($line, [$inStockBatch, $shipmentBatch]);

        $this->assertSame($allocation, $inStockBatch->getReference());
    }

    public function  test_raises_out_of_stock_exception_if_cannot_allocate()
    {
        $this->expectException(OutOfStockException::class);

        $batch = new Batch('batch1', 'SMALL-FORK', 10, new \DateTime('Today'));
        Allocation::allocate(new OrderLine('order1', 'SMALL-FORK', 10), [$batch]);

        Allocation::allocate(new OrderLine('order2', 'SMALL-FORK', 1), [$batch]);
    }
}
