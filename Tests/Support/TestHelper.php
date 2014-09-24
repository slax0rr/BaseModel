<?php
/**
 * This is a TESTS helper file, it prepares the environment for testing and
 * loads all the files necesarry.
 */

require_once "Tests/Support/Models/Test_model.php";
require_once "Tests/Support/Models/SoftMark_model.php";
require_once "Tests/Support/Models/SoftCol_model.php";

/**
 * CodeIgniter Model mock class
 */
class CI_Model
{
    public $load = null;

    public function __construct()
    {
        $this->load = new CI_Loader();
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

function plural($input)
{
    return $input;
}
