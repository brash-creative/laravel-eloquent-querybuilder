<?php

namespace Brash\QueryBuilder;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface QueryBuilderInterface
{
    public function getModel(): Model;

    public function inject(callable $callable): QueryBuilderInterface;

    public function find(int $id): Model;

    public function get(): Collection;

    public function paginate(): LengthAwarePaginator;

    public function count();
}
