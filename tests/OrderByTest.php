<?php

namespace Brash\QueryBuilder\Tests;

use Brash\QueryBuilder\OrderBy;
use PHPUnit\Framework\TestCase;

class OrderByTest extends TestCase
{
    public function testOrderBy()
    {
        $sut = new OrderBy;

        $this->assertEquals('created_at', $sut->getColumn());
        $this->assertEquals('desc', $sut->getDirection());
    }
}
