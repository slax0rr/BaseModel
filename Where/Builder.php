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

    public function add(
        $column,
        $value,
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
        $expr->value = $this->_prepareValue($value);
        $expr->placeholder = is_string($value);
        $expr->comparator = $comparator;
        $expr->groupBegin = $groupBegin;
        $expr->groupEnd = $groupEnd;
        $this->_expressions[] = $expr;
    }

    protected function _prepareValue($value)
    {
        return $value;
    }
}
