<?php

namespace Brash\QueryBuilder\Tests\Filter;

use Brash\QueryBuilder\Filter\FilterInterface;
use Brash\QueryBuilder\Filter\FilterList;
use PHPUnit\Framework\TestCase;

class FilterListTest extends TestCase
{
    public function testWillAcceptType()
    {
        $filter = $this->createMock(FilterInterface::class);

        $sut = new FilterList;
        $sut->push($filter);

        $this->assertCount(1, $sut);
    }
}
