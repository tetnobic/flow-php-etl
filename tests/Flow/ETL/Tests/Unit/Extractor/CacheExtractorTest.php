<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use Flow\ETL\Cache;
use Flow\ETL\Config;
use Flow\ETL\DSL\Entry;
use Flow\ETL\DSL\From;
use Flow\ETL\FlowContext;
use Flow\ETL\Row;
use Flow\ETL\Rows;
use PHPUnit\Framework\TestCase;

final class CacheExtractorTest extends TestCase
{
    public function test_extracting_from_cache() : void
    {
        $cache = $this->createMock(Cache::class);

        $generator = function () : \Generator {
            yield new Rows(Row::create(Entry::integer('id', 1)));
            yield new Rows(Row::create(Entry::integer('id', 2)));
            yield new Rows(Row::create(Entry::integer('id', 3)));
        };

        $cache->expects($this->any())
            ->method('read')
            ->with('id')
            ->willReturn($generator());

        $cache->expects($this->any())
            ->method('has')
            ->with('id')
            ->willReturn(true);

        $cache->expects($this->never())
            ->method('clear')
            ->with('id');

        $extractor = From::cache('id');

        $this->assertEquals(
            [
                new Rows(Row::create(Entry::integer('id', 1))),
                new Rows(Row::create(Entry::integer('id', 2))),
                new Rows(Row::create(Entry::integer('id', 3))),
            ],
            \iterator_to_array($extractor->extract(new FlowContext(Config::builder()->cache($cache)->build())))
        );
    }

    public function test_extracting_from_cache_and_clear() : void
    {
        $cache = $this->createMock(Cache::class);

        $generator = function () : \Generator {
            yield new Rows(Row::create(Entry::integer('id', 1)));
            yield new Rows(Row::create(Entry::integer('id', 2)));
            yield new Rows(Row::create(Entry::integer('id', 3)));
        };

        $cache->expects($this->any())
            ->method('read')
            ->with('id')
            ->willReturn($generator());

        $cache->expects($this->any())
            ->method('has')
            ->with('id')
            ->willReturn(true);

        $cache->expects($this->once())
            ->method('clear')
            ->with('id');

        \iterator_to_array((From::cache('id', clear: true))->extract(new FlowContext(Config::builder()->cache($cache)->build())));
    }

    public function test_extracting_from_fallback_extractor_when_cache_is_empty() : void
    {
        $config = Config::builder()
            ->cache($cache = $this->createMock(Cache::class))
            ->build();

        $cache->expects($this->exactly(2))
            ->method('has')
            ->with('id')
            ->willReturnOnConsecutiveCalls(false, true);

        $cache->expects($this->once())
            ->method('read')
            ->with('id')
            ->willReturn(
                From::rows($rowsToCache = new Rows(
                    Row::create(Entry::integer('id', 1)),
                    Row::create(Entry::integer('id', 2)),
                    Row::create(Entry::integer('id', 3)),
                ))->extract(new FlowContext($config))
            );

        $cache->expects($this->never())
            ->method('clear')
            ->with('id');

        $extractor = From::cache('id', From::rows($rowsToCache));

        \iterator_to_array($extractor->extract(new FlowContext($config)));

        $this->assertEquals(
            [
                $rowsToCache,
            ],
            \iterator_to_array($extractor->extract(new FlowContext($config)))
        );
    }
}
