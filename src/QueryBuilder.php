<?php

namespace Brash\QueryBuilder;

use Brash\QueryBuilder\Filter\FilterInterface;
use Brash\QueryBuilder\Filter\FilterList;
use UnexpectedValueException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class QueryBuilder
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $with =[];

    /**
     * @var array
     */
    protected $withCount = [];

    /**
     * @var FilterList|FilterInterface[]
     */
    protected $filterList;

    /**
     * @var Request
     */
    protected $request;

    /**
     * QueryBuilderRepository constructor.
     *
     * @param Builder|Model   $builder
     * @param FilterList|null $filterList
     * @param Request|null    $request
     */
    public function __construct(
        $builder,
        FilterList $filterList = null,
        Request $request = null
    ) {
        $this->initBuilder($builder);

        $this->request = $request ?? request();
        $this->filterList = $filterList ?? new FilterList;
    }

    private function initBuilder($builder)
    {
        if (!$builder instanceof Model && !$builder instanceof Builder) {
            throw new UnexpectedValueException(sprintf(
                "Builder must be instance of %s or %s",
                Builder::class,
                Model::class
            ));
        }

        $this->builder = $builder instanceof Model ? $builder->newQuery() : $builder;
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    protected function getOrderBy(): OrderBy
    {
        if ($this->request->query->has('sort')) {
            $request = $this->request->query->get('sort');
            $sort = (array) explode(',', $request) + ['created_at', 'asc'];

            [$column, $direction] = $sort;

            return new OrderBy($column, $direction);
        }

        return new OrderBy;
    }

    protected function getWith($key = 'include'): array
    {
        if ($this->request->query->has($key)) {
            $request = $this->request->query->get($key);

            return explode(',', $request);
        }

        return [];
    }

    protected function getWithCount(): array
    {
        return $this->getWith('includeCount');
    }

    protected function applyFilters()
    {
        $filterArray = (array) $this->request->query->get('filter');

        foreach ($filterArray as $key => $value) {
            $filter = $this->getFilter($key);

            $filter($this->builder, $value);
        }
    }

    protected function getFilter(string $filter):? FilterInterface
    {
        if ($this->filterList->has($filter)) {
            throw new AuthorizationException(sprintf(
                "Filter %s does not exist in %s",
                $filter,
                get_class($this)
            ));
        }

        return $this->filterList->get($filter);
    }

    public function find(int $id): Model
    {
        $this->applyFilters();

        $query = $this->getBuilder()
            ->with($this->getWith())
            ->withCount($this->getWithCount())
            ->where('id', $id);

        return $query->firstOrFail();
    }

    public function get(): Collection
    {
        $this->applyFilters();

        $orderBy = $this->getOrderBy();

        $query = $this->getBuilder()
            ->with($this->getWith())
            ->withCount($this->getWithCount())
            ->orderBy($orderBy->getColumn(), $orderBy->getDirection());

        return $query->get();
    }

    public function paginate(): LengthAwarePaginator
    {
        $this->applyFilters();

        $query = $this->getBuilder()
            ->with($this->getWith())
            ->withCount($this->getWithCount())
            ->orderBy($this->getOrderBy()->getColumn(), $this->getOrderBy()->getDirection());

        return $query->paginate();
    }

    public function count(): int
    {
        $this->applyFilters();

        return $this->getBuilder()->count();
    }
}
