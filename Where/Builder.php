<?php
namespace SlaxWeb\BaseModel\Where;

use \SlaxWeb\BaseModel\Where\Expression as E;

/**
 * Where statement builder for BaseModel
 *
 * @author Tomaz Lovrec <tomaz.lovrec@gmail.com>
 */
class Builder
{
    public $binds = array();
    protected $_expressions = array();
    protected $_currentIndex = 0;

    public function add(
        $column,
        $value,
        $logicalOperator = "AND",
        $comparator = "=",
        $table = "",
        $group = null
    ) {
        if (empty($this->_expressions)) {
            $logicalOperator = "";
        } elseif ($logicalOperator === "") {
            $logicalOperator = "AND";
        }
        if ($comparator === "") {
            $comparator = "=";
        }

        $expr = new E();
        $expr->column = $column;
        $expr->value = $value;
        $expr->table = $table;
        $expr->comparator = $comparator;
        $expr->logicalOperator = $logicalOperator;
        $expr->group = $group;
        $this->_expressions[] = $expr;

        return $this;
    }

    public function get()
    {
        if (isset($this->_expressions[$this->_currentIndex])) {
            return $this->_expressions[$this->_currentIndex++];
        } else {
            return null;
        }
    }

    public function reset()
    {
        $this->_currentIndex = 0;
        return $this;
    }

    public function clear()
    {
        $this->_currentIndex = 0;
        $this->_expressions = array();
        $this->binds = array();

        return $this;
    }

    public function toString()
    {
        $where = "";
        foreach ($this->_expressions as $e) {
            $where .= (string)$e;
            $this->binds = array_merge($this->binds, $e->binds);
        }
        return $where;
    }
}
