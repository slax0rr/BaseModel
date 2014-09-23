<?php
namespace SlaxWeb\BaseModel;

class Result
{
    /**
     * Result
     *
     * @var array
     */
    protected $_result = array();
    /**
     * Result row
     *
     * @var object
     */
    protected $_resultRow = null;
    /**
     * Current row
     *
     * @var int
     */
    protected $_currRow = -1;
    /**
     * Rows
     *
     * @var int
     */
    protected $_rows = 0;

    /**
     * Constructor
     *
     * Set the result number of rows, and current row.
     */
    public function __construct(array $result)
    {
        $this->_result = $result;
        $this->_rows = count($this->_result);
    }

    /**
     * Get parameters from current row
     */
    public function __get($param)
    {
        if ($this->_resultRow === null) {
            $this->next();
        }
        if (isset($this->_resultRow->{$param}) === true) {
            return $this->_resultRow->{$param};
        } else {
            return null;
        }
    }

    /**
     * Return current row as string, separated by commas.
     */
    public function __toString()
    {
        $list = "";
        foreach ($this->_result as $r) {
            $r = (array)$r;
            $list .= reset($r) . ",";
        }
        $list = rtrim($list, ",");
        return $list;
    }

    /**
     * Move to next result row
     */
    public function next()
    {
        $currRow = ++$this->_currRow;
        if ($this->_setRow($currRow) === true) {
            return $this;
        }
        return false;
    }

    /**
     * Move to previous result row
     */
    public function prev()
    {
        $currRow = --$this->_currRow;
        if ($this->_setRow($currRow) === true) {
            return $this;
        }
        return false;
    }

    /**
     * Jump to defined row
     */
    public function row($row)
    {
        $row--;
        if ($row <= $this->_rows) {
            $this->_resultRow = $this->result[$row];
            $this->_currRow = $row;
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Get number of rows in result
     */
    public function rowCount()
    {
        return $this->_rows;
    }

    /**
     * Get all rows
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Return row as array
     */
    public function asArray()
    {
        return (array)$this->_resultRow;
    }

    /**
     * Set the result row
     */
    protected function _setRow($row)
    {
        if (isset($this->_result[$row])) {
            $this->_resultRow = $this->_result[$row];
            return true;
        } else {
            return false;
        }
    }
}
