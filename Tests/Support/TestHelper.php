<?php
/**
 * This is a TESTS helper file, it prepares the environment for testing and
 * loads all the files necesarry.
 */

require_once "Tests/Support/Models/Test_model.php";
require_once "Tests/Support/Models/SoftMark_model.php";
require_once "Tests/Support/Models/SoftCol_model.php";
require_once "Tests/Support/Models/Postgre_model.php";

/**
 * CodeIgniter Model mock class
 */
class CI_Model
{
    public $load = null;

    public function __construct()
    {
        $this->load = new CI_Loader();
        $this->db = new CI_Database();
    }
}

/**
 * CodeIgniter Loader mock class
 */
class CI_Loader
{
    public function __call($method, $params)
    {
        return true;
    }
}

/**
 * CodeIgniter Database mock class
 *
 * Used only for the db driver in BaseModels constructor, later mockery is used.
 */
class CI_Database
{
    public $dbdriver = "mysqli";

    public function field_data($table)
    {
        $col = new \stdClass();
        $col->name = "pk";
        $col->primary_key = 1;
        return array($col);
    }
}

function plural($input)
{
    return $input;
}

/**
 * CodeIgniter Log message method mock
 */
function log_message($level, $msg)
{
    return true;
}
