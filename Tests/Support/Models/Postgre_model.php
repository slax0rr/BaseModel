<?php
/**
 * Postgre model
 *
 * Used for testing of the base model with postgre as the driver
 */
class Postgre_model extends \SlaxWeb\BaseModel\Model
{
    // before init callback hook
    public $beforeInit = "beforeInit";

    // was the before init callback called?
    public $beforeInitRun = false;

    public function beforeInit()
    {
        $this->beforeInitRun = true;
    }

    public function getProtected($protected)
    {
        return $this->{$protected};
    }

    // set postgre as db driver, this property is read from CodeIgniter,
    // but for the test environment, we set it manually.
    public function setDriver()
    {
        $this->_dbDriver = "postgres";
    }
}
