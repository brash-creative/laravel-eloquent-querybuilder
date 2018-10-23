<?php

namespace Brash\QueryBuilder;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface QueryBuilderInterface
{
    public function getModel(): Model;

    public function getQuery(): Builder;

    public function inject(callable $callable): QueryBuilderInterface;

    public function get(): Collection;

    public function paginate(): LengthAwarePaginator;

    public function count(): int;
}
