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
    public $placeholder = false;
    public $logicalOperator = "";
    public $comparator = "";
    public $groupBegin = false;
    public $groupEnd = false;
    public $prev = null;
    public $next = null;
}
