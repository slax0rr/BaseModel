<?php
namespace SlaxWeb\BaseModel\Where;

/**
 * Where expression for the BaseModel
 *
 * @author Tomaz Lovrec <tomaz.lovrec@gmail.com>
 */
class Expression
{
    public $column = "";
    public $value = null;
    public $table = "";
    public $placeholder = false;
    public $comparator = "";
    public $groupBegin = false;
    public $groupEnd = false;

    public $binds = array();

    public function __toString()
    {
        $value = $this->_prepareValue($this->value);

        $expr = "";
        // add the logical operator
        $expr .= "{$this->logicalOperator} ";

        // check if we need to go into a subgroup
        if ($this->groupBegin) {
            $expr .= "(";
        }

        // prepare column
        if ($this->table !== "") {
            $expr .= "`{$this->table}`.";
        }
        $expr .= "`{$this->column}`";

        // add a comparator
        $expr .= " {$this->comparator} ";

        // prepare and add the value
        $expr .= $this->_prepareValue($this->value);

        // check if we need to end the subgroup
        if ($this->groupEnd) {
            $expr .= ")";
        }

        return $expr;
    }

    protected function _prepareValue($value)
    {
        $preparedValue = $value;
        // if its a list construct
        if (is_array($value)) {
            $preparedValue = "(";
            foreach ($value as $v) {
                $preparedValue .= "?,";
            }
            $preparedValue = rtrim($preparedValue, ",") . ")";
            $this->binds = array_merge($this->binds, $value);
        }

        if (is_string($value)) {
            $preparedValue = "?";
            $this->binds[] = $value;
        }

        if (is_object($value)) {
            $preparedValue = (string)$value;
        }
        return $preparedValue;
    }
}
