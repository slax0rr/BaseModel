<?php
namespace SlaxWeb\BaseModel;

use \SlaxWeb\BaseModel\Error;
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
    /**
     * Primary key type
     *
     * @var int
     */
    public $keyType = C::PKEYAI;
    /**
     * Primary key function
     *
     * Needs to be of type callable
     *
     * @var mixed
     */
    public $keyFunc = "";
    /**
     * Key function parameters
     *
     * @var array
     */
    public $keyFuncParams = array();

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

    /**************
     * Validation *
     **************/
    /**
     * Validation rules
     *
     * Validation rules need to be in same format as for Form_validation,
     * the validation is ran automatically for insert and update.
     *
     * @var mixed
     */
    public $rules = null;

    /*********
     * Where *
     *********/
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
     * Skip validation
     *
     * For next query
     *
     * @var bool
     */
    protected $_ignoreValidation = false;
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
     * Input parameters can be column name, column value in an array, which are then
     * added to the queries WHERE statement, and an array of
     * columns to be selected, or "*" (default) for all columns.
     */
    public function getBy($where, $cols = "*")
    {
        $this->_where = array();
        $this->_withDeleted();

        $where = $this->_setWhere($where);
        if (is_array($cols) === true) {
            $cols = implode(",", $cols);
        }

        $query = $this->db->query(
            "SELECT {$cols} FROM `{$this->tablePrefix}{$this->table}` {$where}", $this->whereBinds
        );

        return new Result($query->result_object());
    }

    /**
     * Insert a row
     */
    public function insert(array $data)
    {
        $insert = $this->_setPrimaryKey();
        if ($this->keyType === C::PKEYFUNC) {
            $insert["cols"] = "{$this->primaryKey},";
            $insert["value"] = "{$this->keyValue},";
        }
        $binds = array();
        if ($this->validate($data) === false) {
            $error = new Error($this->lang->language);
            $error->add("VALIDATION_ERROR");
            return $error;
        }
        foreach ($data as $col => $value) {
            $binds[] = $value;
            $value = "?";
            $insert["cols"] .= "`{$col}`,";
            $insert["values"] .= "{$value},";
        }
        $insert["cols"] = rtrim($insert["cols"], ",");
        $insert["values"] = rtrim($insert["values"], ",");
        $status = $this->db->query(
            "INSERT INTO `{$this->tablePrefix}{$this->table}` ({$insert["cols"]}) VALUES ({$insert["values"]})",
            $binds
        );
        if ($status === false) {
            $status = new Error($this->lang->language);
            $status->add("CREATE_ERROR");
        }
        return $status;
    }

    /**
     * Update by primary key
     *
     * If ID === 0, all records are updated.
     */
    public function update(array $data, $id = 0)
    {
        if ($id === 0) {
            return $this->updateBy($data, array());
        } else {
            return $this->updateBy($data, array($this->primaryKey => $id));
        }

    }

    /**
     * Update row(s)
     *
     * Input parameters can be column name, column value in an array, which are then
     * added to the queries WHERE statement.
     * Appart from the where array, there also must be an update array,
     * keys hold the column names, and values hold the, well, values.
     */
    public function updateBy(array $data, $where)
    {
        $this->_where = array();
        $this->_withDeleted();

        $where = $this->_setWhere($where);
        $updateString = "";
        $binds = array();
        if ($this->validate($data) === false) {
            $error = new Error($this->lang->language);
            $error->add("VALIDATION_ERROR");
            return $error;
        }
        foreach ($data as $col => $value) {
            $binds[] = $value;
            $value = "?";
            $updateString .= "`{$col}` = {$value}, ";
        }
        $updateString = rtrim($updateString, ", ");
        $status = $this->db->query(
            "UPDATE `{$this->tablePrefix}{$this->table}` SET {$updateString} {$where}",
            array_merge($binds, $this->whereBinds)
        );

        if ($status === false) {
            $status = new Error($this->lang->language);
            $status->add("UPDATE_ERROR");
        }

        return $status;
    }

    /**
     * Delete by primary key
     *
     * If ID === 0, all records are deleted.
     */
    public function delete($id = 0)
    {
        if ($id === 0) {
            return $this->deleteBy(array());
        } else {
            return $this->deleteBy(array($this->primaryKey => $id));
        }
    }

    /**
     * Delete row(s)
     *
     * Input parameters can be column name, column value in an array, which are then
     * added to the queries WHERE statement, and an array of
     * columns to be selected, or "*" (default) for all columns.
     *
     * If soft delete is used, an update is issued, if not, the row is DELETED!
     */
    public function deleteBy($where)
    {
        $status = false;
        /**
         * if we are doing a hard delete, check if we maybe have to also
         * delete some old soft deleted rows, and run the delete statement
         */
        if ($this->softDelete === C::DELETEHARD) {
            $this->_where = array();
            $this->_withDeleted();
            $this->_setWhere($where);

            $status = $this->db->query(
                "DELETE FROM `{$this->tablePrefix}{$this->table}` {$where}", $this->whereBinds
            );
        } else {
            $update = array();
            if ($this->softDelete === C::DELETESOFTMARK) {
                $update = array($this->deleteCol => true);
            } elseif ($this->softDelete === C::DELETESOFTSTATUS) {
                $update = array($this->statusCol => $this->deleteStatus);
            }
            $status = $this->updateby($update, $where);
        }

        return $status;
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

    /**
     * Skip Validation on next query.
     */
    public function skipValidation()
    {
        $this->_ignoreValidation = true;
        return $this;
    }

    /**
     * Validate the data about to be inserted
     */
    public function validate($data)
    {
        $status = true;
        if ($this->_ignoreValidation === true) {
            $this->_ignoreValidation = false;
            return $status;
        }

        if (empty($this->rules) === false) {
            $oldPost = $_POST;
            foreach ($data as $key => $value) {
                $_POST[$key] = $value;
            }
            if (is_array($this->rules) === true) {
                $this->form_validation->set_rules($this->rules);
                $status = $this->form_validation->run();
            } else {
                $status = $this->form_validation->run($this->rules);
            }
            $_POST = $oldPost;
        }
        return $status;
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
    }

    /**
     * Set the where string, if not set by user, BLACK VOODOO MAGIC
     */
    protected function _setWhere($where)
    {
        // if user has set his own where string, use it.
        if ($this->where !== "") {
            return $this->where;
        }

        $this->_where = array_merge($this->_where, $where);

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

        return (empty($where)) ? "" : "WHERE {$where}";
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
        $value = "?";
        if (strpos($columnName, " ") === false) {
            $where .= "`{$columnName}` =";
        } else {
            $col = explode(" ", $columnName);
            $where = "`{$col[0]}` {$col[1]}";
        }
        $where .= " {$value} ";
        return $where;
    }

    /**
     * Prepare primary key for insert
     */
    protected function _setPrimaryKey()
    {
        $data = array("cols" => "", "values" => "");
        switch ($this->keyType) {
            case C::PKEYAI:
            case C::PKEYNONE;
                // no need to do anything, database will handle everything
                break;
            case C::PKEYUUID:
                $data["cols"] = "{$this->primaryKey},";
                $data["values"] = "UUID(),";
                break;
            case C::PKEYFUNC:
                $data["cols"] = "{$this->primaryKey},";
                $data["values"] = call_user_func($this->keyFunc, $this->keyFuncParams);
                break;
        }
        return $data;
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
