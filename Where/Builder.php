<?php
namespace SlaxWeb\BaseModel\Where;

use Expression as E;

/**
 * Where statement builder for BaseModel
 *
 * @author Tomaz Lovrec <tomaz.lovrec@gmail.com>
 */
class Builder
{
    protected $_expressions = array();
    protected $_currentIndex = 0;

    public function add(
        $column,
        $value,
        $table = "",
        $logicalOperator = "=",
        $comparator = "AND",
        $groupBegin = false,
        $groupEnd = false
    ) {
        if ($_expression === null) {
            $comparator = "";
        }

        $expr = new E();
        $expr->column = $column;
        $expr->value = $value;
        $expr->table = $table;
        $expr->placeholder = is_string($value);
        $expr->comparator = $comparator;
        $expr->groupBegin = $groupBegin;
        $expr->groupEnd = $groupEnd;
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

    public function toString()
    {
        $where = "";
        foreach ($this->_expressions as $e) {
            $where .= (string)$e;
        }
        return $where;
    }
}
