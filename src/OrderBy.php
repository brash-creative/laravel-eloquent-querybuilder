<?php

namespace Brash\QueryBuilder;

class OrderBy
{
    /** @var string */
    private $column;

    /** @var string */
    private $direction;

    /**
     * OrderBy constructor.
     *
     * @param string $column
     * @param string $direction
     */
    public function __construct(string $column = 'created_at', string $direction = 'desc')
    {
        $this->column = $column;
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }
}
