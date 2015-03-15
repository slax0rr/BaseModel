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
    public $comparator = "";
    public $logicalOperator = "";
    public $group = null;

    public $binds = array();

    public function __toString()
    {
        $expr = "";
        // add the logical operator
        $expr .= empty($this->logicalOperator) ? "" : " {$this->logicalOperator} ";

        // check if we need to go into a subgroup
        if ($this->group === true) {
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
        if ($this->group === false) {
            $expr .= ")";
        }

        return $expr;
    }

    protected function _prepareValue($value)
    {
        $preparedValue = $value;
        // if its a list construct
        if (is_array($value)) {
            $preparedValue = $this->_prepareForBind($value);
        }

        // if the value is a string
        if (is_string($value)) {
            $preparedValue = "?";
            $this->binds[] = $value;
        }

        // if the value is a bool
        if (is_bool($value)) {
            $preparedValue = $value === true ? "true" : "false";
        }

        // if the value is an object
        if (is_object($value)) {
            $stringValue = (string)$value;
            // prepare list for binding
            $preparedValue = $this->_prepareForBind(explode(",", $stringValue));
        }
        return $preparedValue;
    }

    protected function _prepareForBind($value)
    {
        $preparedValue = "";
        $addBinds = false;
        foreach ($value as $v) {
            if (is_string($v)) {
                $preparedValue .= "?,";
                $addBinds = true;
            } else {
                $preparedValue .= "{$v},";
            }
        }
        $preparedValue = rtrim($preparedValue, ",");
        if ($addBinds === true) {
            $this->binds = array_merge($this->binds, $value);
        }
        return "({$preparedValue})";
    }
}
