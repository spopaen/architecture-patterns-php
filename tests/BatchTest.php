<?php

namespace App\Tests;

use App\Model\Batch;
use App\Model\OrderLine;
use PHPUnit\Framework\TestCase;

final class BatchTest extends TestCase
{
    public function test_allocating_to_a_batch_reduces_the_available_quantity()
    {
        $batch = new Batch("batch-001", "SMALL-TABLE", quantity: 20, eta: new \DateTime());
        $line = new OrderLine('order-ref', "SMALL-TABLE", 2);
        $batch->allocate($line);
        $this->assertSame(18, $batch->getAvailableQuantity());
    }

    /**
     * @return array [Batch, OrderLine]|array
     */
    public function makeBatchAndLine(string $sku, int $batch_qty, int $line_qty): array
    {
        return [
            new Batch("batch-001", $sku, $batch_qty, $eta = new \DateTime()),
            new OrderLine("order-123", $sku, $line_qty)
        ];
    }

    public function test_canAllocate_if_available_greater_than_required()
    {
        list($large_batch, $small_line) = $this->makeBatchAndLine("ELEGANT-LAMP", 20, 2);

        $this->assertTrue($large_batch->canAllocate($small_line));
    }

    public function test_cannot_allocate_if_available_smaller_than_required()
    {
        list($small_batch, $large_line) = $this->makeBatchAndLine("ELEGANT-LAMP", 2, 20);

        $this->assertFalse($small_batch->canAllocate($large_line));
    }

    public function test_canAllocate_if_available_equal_to_required()
    {
        list($batch, $line) = $this->makeBatchAndLine("ELEGANT-LAMP", 2, 2);

        $this->assertTrue($batch->canAllocate($line));
    }

    public function test_cannot_allocate_if_skus_do_not_match()
    {
        $batch = new Batch("batch-001", "UNCOMFORTABLE-CHAIR", 100);
        $different_sku_line = new OrderLine("order-123", "EXPENSIVE-TOASTER", 10);

        $this->assertFalse($batch->canAllocate($different_sku_line));
    }

    public function test_can_only_deallocate_allocated_lines()
    {
        list($batch, $unallocated_line) = $this->makeBatchAndLine("DECORATIVE-TRINKET", 20, 2);
        $batch->deallocate($unallocated_line);

        $this->assertSame(20, $batch->getAvailableQuantity());
    }

    public function test_allocation_is_idempotent(): void {
        [$batch, $line] = $this->makeBatchAndLine("ANGULAR-DESK", 20, 2);
        $batch->allocate($line);
        $batch->allocate($line);

        $this->assertSame(18, $batch->getAvailableQuantity());
    }
}
