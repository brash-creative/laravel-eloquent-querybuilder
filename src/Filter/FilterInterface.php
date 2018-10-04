<?php

namespace Brash\QueryBuilder\Filter;

use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    public function __invoke(Builder $builder, $value): Builder;
}
