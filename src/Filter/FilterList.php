<?php

namespace Brash\QueryBuilder\Filter;

use Brash\TypeCollection\AbstractTypeCollection;

class FilterList extends AbstractTypeCollection
{
    protected function willAcceptType($value): bool
    {
        return $value instanceof FilterInterface;
    }
}
