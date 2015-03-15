<?php
namespace SlaxWeb\BaseModel;

use \SlaxWeb\BaseModel\Error;
use \SlaxWeb\BaseModel\Result;
use \SlaxWeb\BaseModel\Constants as C;
use \SlaxWeb\BaseModel\Where\Builder as B;

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
     * Columns
     *
     * @var array
     */
    public $columns = array();
    /**
     * Primary key
     *
     * Defaults to "id".
     *
     * @var string
     */
    public $primaryKey = "";
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

    /********
     * Join *
     ********/
    /**
     * Join
     *
     * This var is cleared after the query has been run
     *
     * @var string
     */
    protected $_join = "";

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
    /**
     * Where builder
     *
     * @var \SlaxWeb\BaseModel\Where\Builder
     */
    public $wBuild = null;

    /*********
     * Query *
     *********/
    /**
     * Last unbound query
     *
     * @var string
     */
    protected $_query = "";
    /**
     * Last ran query
     *
     * @var string
     */
    protected $_ranQuery = "";
    /**
     * Last where binds
     *
     * @var array
     */
    protected $_whereBinds = array();

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
    /***************
     * SQL Clauses *
     ***************/
    /**
     * Group by clause
     *
     * @var string
     */
    protected $_groupBy = "";
    /**
     * Order by clause
     *
     * @var string
     */
    protected $_orderBy = "";
    /**
     * Limit clause
     *
     * @var string
     */
    protected $_limit = "";
    /************
     * Database *
     ************/
    /**
     * Driver
     *
     * @var string
     */
    protected $_dbDriver = "";

    /***********
     * Methods *
     ***********/

    public function __construct($table = "")
    {
        parent::__construct();

        // run the before init callback if set and exists
        if (method_exists($this, $this->beforeInit) === true) {
            $this->{$this->beforeInit}();
        }

        // load the inflector for pluralizing the model name
        $this->load->helper("inflector");
        // set the passed in table, if it was not set before
        if ($this->table === "") {
            $this->table = $table;
        }
        // guess the table name
        $this->_setTable();
        // get the column data
        $this->columns = $this->db->field_data($this->table);
        // get the primary key
        $this->_setKey();
        // initialize the where builder
        $this->wBuild = new B();

        // get the database driver
        $this->_dbDriver = $this->db->dbdriver;
    }

    /**
     * Get row by Primary Key(ID)
     *
     * If ID === 0, all records are returned.
     */
    public function get($id = 0)
    {
        if ($id !== 0) {
            $this->wBuild->add($this->primaryKey, $id);
        }
        return $this->getBy();
    }

    /**
     * Get row(s)
     *
     * Input parameters can be column name, column value in an array, which are then
     * added to the queries WHERE statement, and an array of
     * columns to be selected, or "*" (default) for all columns.
     */
    public function getBy($where = "", $cols = "*")
    {
        if (is_array($cols) === true) {
            foreach ($cols as &$c) {
                $c = "`{$c}`";
            }
            unset($c);
            $cols = implode(",", $cols);
        }

        $sql = "SELECT {$cols} FROM `{$this->tablePrefix}{$this->table}`";
        $query = $this->_runQuery($sql, $where);

        return $query ? new Result($query->result_object()) : false;
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
            if (is_string($value)) {
                $binds[] = $value;
                $value = "?";
            } elseif (is_bool($value)) {
                $value = $value === true ? "true" : "false";
            }
            $insert["cols"] .= "`{$col}`,";
            $insert["values"] .= "{$value},";
        }
        $insert["cols"] = rtrim($insert["cols"], ",");
        $insert["values"] = rtrim($insert["values"], ",");
        $sql = $this->_fixQueryEscapes(
            "INSERT INTO `{$this->tablePrefix}{$this->table}` ({$insert["cols"]}) VALUES ({$insert["values"]})"
        );
        $this->_query = $sql;
        $status = $this->db->query($sql, $binds);
        $this->_ranQuery = $this->db->last_query();
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
        if ($id !== 0) {
            $this->wBuild->add($this->primaryKey, $id);
        }
        return $this->updateBy($data);
    }

    /**
     * Update row(s)
     *
     * Input parameters can be column name, column value in an array, which are then
     * added to the queries WHERE statement.
     * Appart from the where array, there also must be an update array,
     * keys hold the column names, and values hold the, well, values.
     */
    public function updateBy(array $data, $where = "")
    {
        $updateString = "";
        $binds = array();
        if ($this->validate($data) === false) {
            $error = new Error($this->lang->language);
            $error->add("VALIDATION_ERROR");
            return $error;
        }
        foreach ($data as $col => $value) {
            if (is_string($value)) {
                $binds[] = $value;
                $value = "?";
            } elseif (is_bool($value)) {
                $value = $value === true ? "true" : "false";
            }
            $updateString .= "`{$col}` = {$value}, ";
        }
        $updateString = rtrim($updateString, ", ");
        $this->whereBinds = array_merge($this->whereBinds, $binds);
        $sql = "UPDATE `{$this->tablePrefix}{$this->table}` SET {$updateString}";
        $status = $this->_runQuery($sql, $where);

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
        if ($id !== 0) {
            $this->wBuild->add($this->primaryKey, $id);
        }
            return $this->deleteBy();
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
    public function deleteBy($where = "")
    {
        $status = false;
        /**
         * if we are doing a hard delete, check if we maybe have to also
         * delete some old soft deleted rows, and run the delete statement
         */
        if ($this->softDelete === C::DELETEHARD) {
            $sql = "DELETE FROM `{$this->tablePrefix}{$this->table}`";
            $status = $this->_runQuery($sql, $where);
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

    /**
     * Where builder *add* method alias
     *
     * So you can directly add where items, without the need of calling
     * a different class.
     */
    public function where()
    {
        $args = func_get_args();
        if (is_array($args[0]) === true) {
            $this->where = $args[0][0];
            if (isset($args[0][1])) {
                $this->whereBinds = $args[0][1];
            }
        } else {
            call_user_func_array(array($this->wBuild, "add"), $args);
        }
        return $this;
    }

    /**
     * Add a join statement to the next query
     */
    public function join($table, array $condition, $direction = C::JOININNER)
    {
        $join = C::JOININNER . " JOIN `{$table}` ON";
        $conditions = "";
        $condCount = count($condition);
        $count = 0;
        foreach ($condition as $c) {
            if (isset($c[0])) {
                // support for old way of building joins, but DEPRECATED
                $this->_deprecatedJoin($table, $c, $count, $conditions);
            } else {
                $leftTable = isset($c["leftTable"]) ? $c["leftTable"] : $this->table;
                $leftColumn = $c["leftColumn"];
                $rightTable = isset($c["rightTable"]) ? $c["rightTable"] : $table;
                $rightColumn = $c["rightColumn"];
                if ($count > 0) {
                    $conditions .= (isset($c["logicalOperator"]) ? $c["logicalOperator"] : "AND") . " ";
                }
                $conditions .=
                    "`{$this->tablePrefix}{$leftTable}`.`{$leftColumn}`" .
                    " = `{$this->tablePrefix}{$rightTable}`.`{$rightColumn}` ";
            }
            $count++;
        }
        if ($condCount > 1) {
            $conditions = "({$conditions})";
        }

        $this->_join .= "{$join} {$conditions} ";

        return $this;
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
     * Add group by clause to the next statement
     */
    public function groupBy(array $columns)
    {
        $this->_groupBy = "GROUP BY ";
        foreach ($columns as $c) {
            $this->_groupBy .= "`{$c}`,";
        }
        $this->_groupBy = rtrim($this->_groupBy, ",");
        return $this;
    }

    /**
     * Add an order by to next statement
     */
    public function orderBy(array $columns, $direction = "asc")
    {
        $this->_orderBy = "ORDER BY ";
        if (isset($columns[0]) === true) {
            foreach ($columns as $c) {
                $this->_orderBy .= "`{$c}`,";
            }
            $this->_orderBy = rtrim($this->_orderBy, ",");
            $this->_orderBy .= " {$direction}";
        } else {
            foreach ($columns as $column => $sort) {
                $this->_orderBy .= "`{$column}` {$sort},";
            }
            $this->_orderBy = rtrim($this->_orderBy, ",");
        }
        return $this;
    }

    /**
     * Add the limit clause to the next statement
     */
    public function limit($limit, $start = 0)
    {
        $this->_limit = "LIMIT {$start}, {$limit}";
        if ($this->_dbDriver !== "mysql" && $this->_dbDriver !== "mysqli") {
            $this->_limit = "LIMIT {$limit} OFFSET {$start}";
        }
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
            if (is_array($this->rules) === true) {
                $this->form_validation->reset_validation();
                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($this->rules);
                $status = $this->form_validation->run();
            } else {
                $status = $this->form_validation->run($this->rules);
            }
        }
        return $status;
    }

    /**
     * Get last query
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Get last bound query
     */
    public function getBoundQuery()
    {
        return $this->_ranQuery;
    }

    /**
     * Get last query binds
     */
    public function getQueryBinds()
    {
        return $this->_whereBinds;
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
                $this->wBuild->add($this->deleteCol, false);
            } elseif ($this->softDelete === C::DELETESOFTSTATUS) {
                $this->wBuild->add($this->statusCol, $this->deleteStatus, "", "!=");
            }
        }
    }

    /**
     * DEPRECATED
     *
     * Set the where string, if not set by user, BLACK VOODOO MAGIC
     *
     * This is DEPRECATED in favour of the Where\Builder class, that uses
     * MOAR BLACK VOODOO MAGIC, reffer to documentation on how to use it.
     */
    protected function _setWhere($where)
    {
        // if user has set his own where string, use it.
        if ($this->where !== "") {
            return $this->where;
        }
        if (is_string($where)) {
            return $where;
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
            case C::PKEYNONE:
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

    /**
     * Try and find the primary key of the table
     */
    protected function _setKey()
    {
        // if table doesn't use primary key or is already set, no need guessing it.
        if ($this->keyType === C::PKEYNONE || empty($this->primaryKey) === false) {
            return;
        }

        foreach ($this->columns as $c) {
            if ($c->primary_key) {
                $this->primaryKey = $c->name;
                return;
            }
        }
    }

    /**
     * Concatenate clauses in right order
     */
    protected function _getClauses()
    {
        $clauses = "";
        $clauses = "{$this->_groupBy} {$this->_orderBy} {$this->_limit}";
        return $clauses;
    }
    
    /**
     * Run query
     * 
     * Assembles the where statement with the help of the WHERE builder
     * and the now DEPRECATED _setWhere class method for backward
     * compatibility.
     * When the where is prepared, it takes the passed in query,
     * adds the clauses and the prepared WHERE statement,
     * and runs the query.
     * After query has run it shuts down the query, unsets the binds,
     * the joins etc.
     */
    protected function _runQuery($sql, $where)
    {
        $this->_withDeleted();

        // DEPRECATED
        $where = $this->_setWhere($where);

        $wBuild = $this->wBuild->toString();
        if (empty($wBuild) === false) {
            $where .= empty($where) ? "" : "AND";
            $where .= " {$wBuild}";
            unset($wBuild);
        }
        $this->whereBinds = array_merge($this->whereBinds, $this->wBuild->binds);

        // monstrosity...because of deprecated stuff...be sure to remove this in the future
        if (empty($where) === false) {
            $where = "WHERE {$where}";
        }

        // prepare the query
        $sql = $this->_fixQueryEscapes("{$sql} {$this->_join} {$where} {$this->_getClauses()}");
        $this->_query = $sql;
        $this->_whereBinds = $this->whereBinds;

        // run the query
        $query = $this->db->query($sql, $this->whereBinds);
        $this->_ranQuery = $this->db->last_query();
        
        if ($query !== false) {
            // shutdown the query
            $this->_join = "";
            $this->_orderBy = "";
            $this->_groupBy = "";
            $this->_limit = "";
        }
        $this->_where = array();
        $this->whereBinds = array();
        $this->wBuild->clear();

        return $query;
    }

    /**
     * mySQL requers backtics as escapes, normal databases require double quotes.
     * This method handles the replace of those escapes.
     */
    protected function _fixQueryEscapes($sql)
    {
        if ($this->_dbDriver !== "mysql" && $this->_dbDriver !== "mysqli") {
            $sql = str_replace("`", "\"", $sql);
        }
        return $sql;
    }

    /**
     * Support for old, now, DEPRECATED way of building a where statement.
     * It is DEPRECATED because it did not support joins between custom tables, but it always forced join on the main
     * table.
     */
    protected function _deprecatedJoin($table, $c, $count, &$conditions)
    {
        log_message("debug", "WARNING! Use of deprecated method for JOIN statement building in BaseModel!");
        if ($count > 0) {
            if (isset($c[2])) {
                $conditions .= "{$c[2]} ";
            } else {
                $conditions .= "AND ";
            }
        }
        $conditions .= "`{$this->tablePrefix}{$this->table}`.`{$c[0]}` = `{$this->tablePrefix}{$table}`.`{$c[1]}` ";
    }
}
