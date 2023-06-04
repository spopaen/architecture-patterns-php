<?php

namespace App\Service;

use App\Exception\OutOfStockException;
use App\Model\Batch;
use App\Model\OrderLine;

class Allocation
{
    /**
     * @param Batch[]|array $batches
     * @throws OutOfStockException
     */
    public static function allocate(OrderLine $line, array $batches): string
    {
        usort($batches, function ($batch1, $batch2) {
            if ($batch1->getEta() === null) {
                return -1; // $batch1 is considered greater
            }

            if ($batch2->getEta() === null) {
                return 1; // $batch2 is considered greater
            }

            return $batch1->getEta() <=> $batch2->getEta();
        });

        foreach ($batches as $batch) {
            if ($batch->canAllocate($line)) {
                $batch->allocate($line);

                return $batch->getReference();
            }
        }

        throw new OutOfStockException;
    }
}
