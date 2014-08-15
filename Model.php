<?php
namespace SlaxWeb\BaseModel\Model;

/**
 * BaseModel for CodeIgniter
 *
 * Providing basic ORM functionality to CodeIgniter.
 *
 * @author Tomaz Lovrec <tomaz.lovrec@gmail.com>
 */
class Model extends \CI_Model
{
    /**
     * Model table
     *
     * Auto guessed from models name, or can be assigned separately.
     *
     * @var string
     */
    public $table = "";
    /**
     * Primary key
     *
     * Defaults to "id", base model tries to find tables primary key.
     *
     * @var string
     */
    public $primaryKey = "id";
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

    public function __construct($table = "")
    {
        parent::__construct();

        if (method_exists(array($this, $this->beforeInit)) === true) {
            $this->{$this->beforeInit}();
        }

        $this->load->helper("inflector");
        $this->table = $table;
        $this->_setTable();
        $this->_setPrimaryKey();
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
     * Try and retrieve the primary key from the table
     */
    protected function _setPrimaryKey()
    {
        $this->primaryKey = $this->db->query("SHOW KEYS FROM `{$this->table}` WHERE key_name = 'PRIMARY'")
            ->row()->Column_name;
    }
}
