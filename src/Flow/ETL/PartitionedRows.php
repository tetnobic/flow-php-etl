<?php

declare(strict_types=1);

namespace Flow\ETL;

use Flow\ETL\Row\EntryReference;

final class PartitionedRows
{
    /**
     * @var array<Partition>
     */
    public readonly array $partitions;

    public function __construct(public readonly Rows $rows, Partition $partition, Partition ...$partitions)
    {
        \array_unshift($partitions, $partition);

        $this->partitions = $partitions;
    }

    public function orderBy(EntryReference ...$refs) : Rows
    {
        return $this->rows->sortBy(...$refs);
    }
}
