<?php

declare(strict_types=1);

namespace Flow\ETL\Join\Comparison;

use Flow\ETL\Join\Comparison;
use Flow\ETL\Row;
use Flow\ETL\Row\EntryReference;

/**
 * @implements Comparison<array{entry_left: string|EntryReference, entry_right: string|EntryReference}>
 */
final class Identical implements Comparison
{
    public function __construct(
        private readonly string|EntryReference $entryLeft,
        private readonly string|EntryReference $entryRight
    ) {
    }

    public function __serialize() : array
    {
        return [
            'entry_left' => $this->entryLeft,
            'entry_right' => $this->entryRight,
        ];
    }

    public function __unserialize(array $data) : void
    {
        $this->entryLeft = $data['entry_left'];
        $this->entryRight = $data['entry_right'];
    }

    public function compare(Row $left, Row $right) : bool
    {
        return $left->valueOf($this->entryLeft) === $right->valueOf($this->entryRight);
    }

    /**
     * @return array<EntryReference>
     */
    public function left() : array
    {
        return [\is_string($this->entryLeft) ? EntryReference::init($this->entryLeft) : $this->entryLeft];
    }

    /**
     * @return array<EntryReference>
     */
    public function right() : array
    {
        return [\is_string($this->entryRight) ? EntryReference::init($this->entryRight) : $this->entryRight];
    }
}
