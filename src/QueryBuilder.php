<?php

namespace Brash\QueryBuilder;

use Brash\QueryBuilder\Filter\FilterInterface;
use Brash\QueryBuilder\Filter\FilterList;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class QueryBuilder implements QueryBuilderInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $with =[];

    /**
     * @var array
     */
    protected $withCount = [];

    /**
     * @var array
     */
    protected $injections = [];

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
     * @param Model   $model
     * @param FilterList|null $filterList
     * @param Request|null    $request
     */
    public function __construct(
        Model $model,
        FilterList $filterList = null,
        Request $request = null
    ) {
        $this->model = $model;
        $this->request = $request ?? request();
        $this->filterList = $filterList ?? new FilterList;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function inject(callable $callable): QueryBuilderInterface
    {
        $this->injections[] = $callable;

        return $this;
    }

    public function find(int $id): Model
    {
        $query = $this->query()->where('id', $id);

        return $query->firstOrFail();
    }

    public function get(): Collection
    {
        $orderBy = $this->getOrderBy();

        $query = $this->query()
            ->orderBy($orderBy->getColumn(), $orderBy->getDirection());

        return $query->get();
    }

    public function paginate(): LengthAwarePaginator
    {
        $orderBy = $this->getOrderBy();

        $query = $this->query()
            ->orderBy($orderBy->getColumn(), $orderBy->getDirection());

        return $query->paginate();
    }

    public function count(): int
    {
        return $this->query()->count();
    }

    protected function query(): Builder
    {
        $query = (clone $this->model)::query()
            ->with($this->getWith())
            ->withCount($this->getWithCount());

        $this->applyAll($query);

        return $query;
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

    protected function applyAll(Builder $builder)
    {
        $this->applyInjections($builder);
        $this->applyFilters($builder);
    }

    protected function applyInjections(Builder $builder)
    {
        foreach ($this->injections as $injection) {
            $injection($builder);
        }
    }

    protected function applyFilters(Builder $builder)
    {
        $filterArray = (array) $this->request->query->get('filter');

        foreach ($filterArray as $key => $value) {
            $filter = $this->getFilter($key);

            $filter($builder, $value);
        }
    }

    protected function getFilter(string $filter):? FilterInterface
    {
        if (!$this->filterList->has($filter)) {
            throw new AuthorizationException(sprintf(
                "Filter %s does not exist in %s",
                $filter,
                get_class($this)
            ));
        }

        return $this->filterList->get($filter);
    }
}
