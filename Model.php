<?php
namespace SlaxWeb\BaseModel;

use \SlaxWeb\BaseModel\Result;
use \SlaxWeb\BaseModel\Constants as C;

/**
 * BaseModel for CodeIgniter
 *
 * Providing basic ORM functionality to CodeIgniter.
 *
 * @author Tomaz Lovrec <tomaz.lovrec@gmail.com>
 */
class Model extends \CI_Model
{
    /******************
     * Table settings *
     ******************/
    /**
     * Model table
     *
     * Auto guessed from models name, or can be assigned separately.
     *
     * @var string
     */
    public $table = "";
    /**
     * Table prefix
     *
     * If your tables are prefixed, you can set that prefix here.
     *
     * @var string
     */
    public $tablePrefix = "";
    /**
     * Primary key
     *
     * Defaults to "id".
     *
     * @var string
     */
    public $primaryKey = "id";

    /***************
     * Soft delete *
     * *************/
    /**
     * Soft delete
     *
     * Instead of deleting the row, only mark it as deleted
     *
     * @var int
     */
    public $softDelete = C::DELETEHARD;
    /**
     * Soft delete column
     *
     * Column name where the soft delete is marked.
     *
     * @var string
     */
    public $deleteCol = "deleted";
    /**
     * Soft delete status column
     *
     * @var string
     */
    public $statusCol = "status";
    /**
     * Soft delete status name
     *
     * @var string
     */
    public $deleteStatus = "deleted";
    /**
     * Custom where string
     *
     * If a custom where string is used, you can assign binding where parameters
     * to $whereBinds property.
     *
     * @var string
     */
    public $where = "";
    /**
     * Custom where binds
     *
     * @var array
     */
    public $whereBinds = array();

    /*************
     * Callbacks *
     *************/
    /**
     * Before init callback
     *
     * Called before the base model has initialized, giving you the opportunity
     * to set the table and primary key name. This callback is already set
     * because you can not change its value before the base model has already
     * initialized.
     *
     * @var string
     */
    public $beforeInit = "beforeInitCallback";

    /**************
     * Protecteds *
     **************/
    /**
     * Ignore soft delete on next query
     *
     * @var bool
     */
    protected $_ignoreSoftDelete = false;
    /**
     * Where array
     *
     * @var array
     */
    protected $_where = array();

    /***********
     * Methods *
     ***********/

    public function __construct($table = "")
    {
        parent::__construct();

        if (method_exists($this, $this->beforeInit) === true) {
            $this->{$this->beforeInit}();
        }

        $this->load->helper("inflector");
        $this->table = $table;
        $this->_setTable();
    }

    /**
     * Get row by Primary Key(ID)
     *
     * If ID === 0, all records are returned.
     */
    public function get($id = 0)
    {
        if ($id === 0) {
            return $this->getBy(array());
        } else {
            return $this->getBy(array($this->primaryKey => $id));
        }
    }

    /**
     * Get row(s)
     *
     * Input parameters can be column name, column value, which are then
     * added to the queries WHERE statement.
     */
    public function getBy($where, $cols = "*")
    {
        $this->_withDeleted();

        $where = $this->_setWhere();
        if (is_array($cols) === true) {
            $cols = implode(",", $cols);
        }

        $query = $this->db->query("SELECT {$cols} FROM {$this->table} WHERE {$where}", $this->whereBinds);

        return new Result($query->result_object());
    }

    /**********
     * Scopes *
     **********/
    /**
     * Return deleted items as well on next query.
     */
    public function withDeleted()
    {
        $this->_ignoreSoftDelete = true;
        return $this;
    }

    /*********************
     * Protected Methods *
     *********************/
    /**
     * Include deleted where statement
     */
    protected function _withDeleted()
    {
        if ($this->softDelete !== C::DELETEHARD && $this->_ignoreSoftDelete === false) {
            if ($this->softDelete === C::DELETESOFTMARK) {
                $this->_where[$this->deleteCol] = false;
            } elseif ($this->softDelete === C::DELETESOFTSTATUS) {
                $this->_where["{$this->statusCol} !="] = $this->deleteStatus;
            }
        }
        $this->_where = array_merge($where, $this->_where);
    }
    /**
     * Set the where string, if not set by user, BLACK VOODOO MAGIC
     */
    protected function _setWhere()
    {
        // if user has set his own where string, use it.
        if ($this->where !== "") {
            return $this->where;
        }

        $where = "";
        $link = false;
        foreach ($this->_where as $col => $value) {
            if (is_array($value) === true) {
                $where .= $this->_addWhereGroup($value);
            } else {
                $where .= $this->_setWhereValue($col, $value, $link);
                $link = true;
            }
        }

        return $where;
    }

    /**
     * Add a where group to the where string
     */
    protected function _addWhereGroup($params)
    {
        $where = "(";
        $link = false;
        foreach ($params as $col => $value) {
            if (is_array($value) === true) {
                $where .= $this->_addWhereGroup($value);
            } else {
                $where .= $this->_setWhereValue($col, $value, $link);
                $link = true;
            }
        }
        $where .= ")";
        return $where;
    }

    /**
     * Set the where column/value pair
     */
    protected function _setWhereValue($columnName, $value, $link = true)
    {
        $where = "";
        // check if we need to use some link between conditions
        if (strpos($columnName, "OR ") === 0) {
            $columnName = ltrim($columnName, "OR ");
            $where .= "OR ";
        } elseif ($link === true) {
            $where .= "AND ";
        }
        $this->whereBinds[] = $value;
        if (is_string($value) === true && strpos($value, "(") === false) {
            $value = "'?'";
        } else {
            $value = "?";
        }
        $where .= "{$columnName}";
        if (strpos($columnName, " ") === false) {
            $where.= " =";
        }
        $where .= " {$value} ";
        return $where;
    }

    /**
     * Auto-guess the table name
     */
    protected function _setTable()
    {
        if ($this->table === "") {
            $this->table = plural(preg_replace("/(_m|_model)?$/", "", strtolower(get_class($this))));
        }
    }
}
