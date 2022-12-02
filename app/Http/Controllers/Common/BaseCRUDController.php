<?php

namespace App\Http\Controllers\Common;

use App\Foundation\Database\Paginator\Paginator;
use App\Foundation\Database\QueryBuilder;
use App\Foundation\HTTP\Exceptions\NotFoundException;
use App\Foundation\HTTP\Request;
use App\Helpers\Collection\Arr;
use App\Http\Resources\Common\CollectionResource;
use App\Http\Resources\Common\SingleResource;
use App\Models\Common\BaseModel;
use Closure;
use Exception;
use Throwable;

abstract class BaseCRUDController extends BaseController
{
    protected ?string $single_resource = null;
    protected ?string $collection_resource = null;

    /**
     * @return array|string
     */
    abstract protected function getDefaultOrder(): array|string;

    /**
     * @return QueryBuilder
     */
    abstract protected function getQueryBuilder(): QueryBuilder;

    /**
     * @throws Exception
     */
    protected function parentIndex(Request $request, array $options = [], Closure $closure = null): null|array|Paginator|CollectionResource
    {

        $default_options = [
            'filters' => [
                'enable' => true,
                'ignore' => []
            ],
            'orders' => [
                'enable' => true,
            ],
            'pagination' => [
                'limit' => 10,
                'enable' => true,
            ]
        ];

        $this->setOptions(array_merge_recursive_distinct($default_options, $options));

        $builder = $this->getQueryBuilder();

        if ($this->getOption('filters.enable')) {
            $builder = $this->addFilters($request, $builder);
        }

        if ($this->getOption('orders.enable')) {
            $builder = $this->addOrders($request, $builder);
        }

        if ($closure) {
            $tmp_builder = $closure($builder, 'builder');
            if ($tmp_builder) {
                $builder = $tmp_builder;
            }
        }

        if ($this->getOption('pagination.enable')) {

            $limit = $request->get('limit') ?? $this->getOption('pagination.limit');
            $page = $request->get('page') ?? 1;


            $items = $builder->paginate($limit, $page);
        } else {
            $items = $builder->get();
        }


        if ($closure) {
            if ($filter_result = $closure($items, 'filter')) {
                $items = $filter_result;
            }
        }

        if (is_null($this->collection_resource)) {
            return $items;
        } else {
            return new $this->collection_resource($items);
        }
    }

    /**
     * @throws NotFoundException
     */
    protected function parentShow(Request $request, $key, $options = [], \Closure $closure = null): BaseModel|SingleResource
    {
        $default_options = [];

        $this->setOptions(array_merge_recursive_distinct($default_options, $options));

        $this->setCurrentModel($this->getModelByKey($key));

        if ($closure) {
            $closure_result = $closure($this->current_model);
            if ($closure_result && is_array($closure_result) && isset($closure_result['mode']) && $closure_result['mode'] == 'return') {
                return $closure_result['result'];
            }
        }

        if (is_null($this->single_resource)) {
            return $this->current_model;
        } else {
            return new $this->single_resource($this->current_model);
        }
    }

    /**
     * @param Request $request
     * @param array $options
     * @param Closure|null $closure
     * @return BaseModel
     * @throws Throwable
     */
    protected function parentStore(Request $request, array $options = [], \Closure $closure = null): BaseModel|SingleResource
    {
        $default_options = [];

        $this->setOptions(array_merge_recursive_distinct($default_options, $options));

        $this->initFunction([
            'action_type' => 'store',
        ]);

        foreach ($this->current_model->getFillable() as $column) {
            if ($request->has($column)) {
                $this->current_model->{$column} = $request->get($column);
            }
        }



        helper_database_begin_transaction();

        if ($closure) {
            $closure($this->current_model, 'before');
        }

        try {
            $this->current_model->save();

            if ($closure) {
                $closure($this->current_model, 'after');
            }

            helper_database_commit();

            if (is_null($this->single_resource)) {
                return $this->current_model;
            } else {
                return new $this->single_resource($this->current_model);
            }
        } catch (Throwable $exception) {
            helper_database_rollback();
            throw $exception;
        }
    }

    /**
     * @param Request $request
     * @param  $key
     * @param array $options
     * @param Closure|null $closure
     * @return BaseModel
     * @throws Throwable
     */
    protected function parentUpdate(Request $request, $key, array $options = [], \Closure $closure = null): BaseModel|SingleResource
    {
        $default_options = [];

        $this->setOptions(array_merge_recursive_distinct($default_options, $options));

        $this->setCurrentModel($this->getModelByKey($key));

        foreach ($this->current_model->getFillable() as $column) {
            if ($request->has($column)) {
                $this->current_model->$column = $request->get($column);
            }
        }

        helper_database_begin_transaction();

        if ($closure) {
            $closure($this->current_model, 'before');
        }

        try {
            $this->current_model->save();

            if ($closure) {
                $closure($this->current_model, 'after');
            }

            helper_database_commit();

            if (is_null($this->single_resource)) {
                return $this->current_model;
            } else {
                return new $this->single_resource($this->current_model);
            }
        } catch (Throwable $exception) {
            helper_database_rollback();
            throw $exception;
        }
    }

    /**
     * @param Request $request
     * @param  $key
     * @param array $options
     * @param Closure|null $closure
     * @return BaseModel
     * @throws Throwable
     */
    protected function parentDestroy(Request $request, $key, array $options = [], \Closure $closure = null): string|BaseModel|SingleResource
    {
        $default_options = [];

        $this->setOptions(array_merge_recursive_distinct($default_options, $options));

        $this->setCurrentModel($this->getModelByKey($key));

        foreach ($this->current_model->getFillable() as $column) {
            if ($request->has($column)) {
                $this->current_model->$column = $request->get($column);
            }
        }

        helper_database_begin_transaction();

        if ($closure) {
            $closure($this->current_model, 'before');
        }

        try {
            $this->current_model->delete();

            if ($closure) {
                $closure($this->current_model, 'after');
            }

            helper_database_commit();

            if (is_null($this->single_resource)) {
                return 'ok';
            } else {
                return 'ok';
            }
        } catch (Throwable $exception) {
            helper_database_rollback();
            throw $exception;
        }
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param $key
     * @return BaseModel
     * @throws NotFoundException
     */
    protected function getModelByKey($key): BaseModel
    {
        $builder = $this->getQueryBuilder();


        $primary_key = $this->current_model::getPrimaryKey();
        $column = $this->current_model->getTable() . '.' . $primary_key;

        return $builder->where($column, $key)->firstOrFail();
    }

    /**
     * @param Request $request
     * @param QueryBuilder $builder
     * @return QueryBuilder
     * @throws Exception
     */
    protected function addFilters(Request $request, QueryBuilder $builder): QueryBuilder
    {
        $filters = $request->get('filter') ?? [];

        foreach ($filters as $filter) {
            $builder = $this->addFilter($builder, $filter);
        }

        return $builder;
    }

    /**
     * Operators:
     * '=', '<', '>', '<=', '>=', '<>', '!=', '<=>', 'like', 'like binary',
     * 'not like', 'ilike', '&', '|', '^', '<<', '>>', 'rlike', 'not rlike',
     * 'regexp', 'not regexp','~', '~*', '!~', '!~*', 'similar to',
     * 'not similar to', 'not ilike', '~~*', '!~~*'
     * @param QueryBuilder $builder
     * @param array $filter
     * @return QueryBuilder
     * @throws Exception
     */
    protected function addFilter(QueryBuilder $builder, array $filter): QueryBuilder
    {
        if (!Arr::isAssoc($filter)) {
            throw new Exception('Bad filter. The filter should be an associative array.', 400);
        }

        if (!isset($filter['column'])) {
            throw new Exception('Bad filter. The column is required.', 400);
        }

        $ignore = $this->getOption('filter.ignore');
        if ($ignore && is_array($ignore) && in_array($filter['column'], $ignore)) {
            return $builder;
        }

        $column = $filter['column'];
        $operator = $filter['operator'] ?? '=';
        $value = $filter['value'] ?? null;
        $boolean = $filter['boolean'] ?? 'and';

        if (!mb_stripos($column, '.')) {
            $column = $this->current_model->getTable() . '.' . $column;
        }

        if (is_array($value)) {
            return $builder->whereIn($column, $operator !== '=', $value, $boolean);
        }

        return $builder->where($column, $operator, $value, $boolean);
    }

    /**
     * @param Request $request
     * @param QueryBuilder $builder
     * @return QueryBuilder
     */
    protected function addOrders(Request $request, QueryBuilder $builder): QueryBuilder
    {
        $orders = $request->get('order') ?? [];

        if (!$orders) {
            $orders = $this->getDefaultOrder();
        }

        if (!is_array($orders)) {
            $orders = [$orders];
        }

        foreach ($orders as $order) {
            $builder = $this->addOrder($builder, htmlspecialchars($order, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        }

        return $builder;
    }

    /**
     * @param QueryBuilder $builder
     * @param string $order
     * @return QueryBuilder
     */
    protected function addOrder(QueryBuilder $builder, string $order): QueryBuilder
    {
        $direction = 'asc';

        if (str_starts_with($order, '-')) {
            $direction = 'desc';
            $order = substr($order, 1);
        }

        $builder->orderBy($order, $direction);

        return $builder;
    }
}
