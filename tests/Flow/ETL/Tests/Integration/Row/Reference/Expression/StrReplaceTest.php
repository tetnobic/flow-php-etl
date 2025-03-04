<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\Row\Reference\Expression;

use function Flow\ETL\DSL\ref;
use Flow\ETL\DSL\From;
use Flow\ETL\DSL\To;
use Flow\ETL\Flow;
use Flow\ETL\Memory\ArrayMemory;
use PHPUnit\Framework\TestCase;

final class StrReplaceTest extends TestCase
{
    public function test_str_replace() : void
    {
        (new Flow())
            ->read(
                From::array(
                    [
                        ['key' => 'value'],
                    ]
                )
            )
            ->withEntry('str_replace', ref('key')->strReplace('e', 'es'))
            ->write(To::memory($memory = new ArrayMemory()))
            ->run();

        $this->assertSame(
            [
                ['key' => 'value', 'str_replace' => 'values'],
            ],
            $memory->data
        );
    }

    public function test_str_replace_on_non_string_key() : void
    {
        (new Flow())
            ->read(
                From::array(
                    [
                        ['id' => 1],
                    ]
                )
            )
            ->withEntry('str_replace', ref('id')->strReplace('', ''))
            ->write(To::memory($memory = new ArrayMemory()))
            ->run();

        $this->assertSame(
            [
                ['id' => 1, 'str_replace' => null],
            ],
            $memory->data
        );
    }
}
