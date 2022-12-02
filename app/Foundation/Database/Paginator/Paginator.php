<?php

namespace App\Foundation\Database\Paginator;



class Paginator
{
    #Последняя страница
    protected int $last_page;
    protected int $count;

    public function __construct(
        protected array $data,
        protected int $limit,
        protected int $current_page,
        protected int $total,

    )
    {
        $this->last_page = $this->calcLastPage();
        $this->count = count($this->data);
    }

    public function getPaginationInfo(): array
    {
        return [
            'limit' => $this->getLimit(),
            'total' => $this->getTotal(),
            'current_page' => $this->getCurrentPage(),
            'last_page' => $this->getLastPage(),
            'count' => $this->getCount(),
        ];
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->current_page;
    }

    /**
     * @return int
     */
    public function getLastPage(): int
    {
        return $this->last_page;
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }


    /**
     * Расчёт последней страницы
     *
     * @return int
     */
    protected function calcLastPage(): int
    {
        return ceil($this->total / $this->limit);
    }
}