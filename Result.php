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
    protected $_currRow = 0;
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
        if ($this->_rows > 0) {
            $this->_resultRow = $this->_result[0];
        }
    }

    /**
     * Get parameters from current row
     */
    public function __get($param)
    {
        if (isset($this->_resultRow->{$param}) === true) {
            return $this->_resultRow->{$param};
        } else {
            return null;
        }
    }

    /**
     * Move to next result row
     */
    public function next()
    {
        $this->_resultRow = $this->_result[$this->_currRow++];
        return $this;
    }

    /**
     * Move to previous result row
     */
    public function prev()
    {
        $this->_resultRow = $this->_result[$this->_currRow--];
        return $this;
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
}
