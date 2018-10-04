<?php

namespace Brash\QueryBuilder\Tests;

use Brash\QueryBuilder\Filter\FilterList;
use Brash\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class QueryBuilderTest extends TestCase
{
    /** @var Model|\PHPUnit_Framework_MockObject_MockObject **/
    private $model;

    /** @var Builder|\PHPUnit_Framework_MockObject_MockObject */
    private $builder;

    /** @var FilterList|\PHPUnit_Framework_MockObject_MockObject */
    private $filterList;

    /** @var Request|\PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var QueryBuilder */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->model = $this->createMock(Model::class);
        $this->builder = $this->getMockBuilder(Builder::class)->disableOriginalConstructor()->getMock();
        $this->filterList = $this->getMockBuilder(FilterList::class)->getMock();
        $this->request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $this->sut = new QueryBuilder($this->builder, $this->filterList, $this->request);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testInvalidBuilder()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf(
            "Builder must be instance of %s or %s",
            Builder::class,
            Model::class
        ));

        new QueryBuilder('test');
    }

    public function testPlainFind()
    {
        $this->mockFilters();
        $this->mockBuilder();

        $this->builder->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $this->builder->expects($this->once())
            ->method('firstOrFail')
            ->willReturn($this->model);

        $result = $this->sut->find(1);

        $this->assertInstanceOf(Model::class, $result);
    }

    public function testPlainGet()
    {
        $this->mockFilters();
        $this->mockBuilder();

        $this->builder->expects($this->once())
            ->method('get')
            ->willReturn(new Collection);

        $result = $this->sut->get();

        $this->assertInstanceOf(Collection::class, $result);
    }

    private function mockBuilder($with = [], $withCount = [])
    {
        $this->builder->expects($this->once())
            ->method('with')
            ->with($with)
            ->willReturnSelf();

        $this->builder->expects($this->once())
            ->method('withCount')
            ->with($withCount)
            ->willReturnSelf();
    }

    private function mockFilters($params = [])
    {
        $this->request->query = new ParameterBag($params);
    }
}
