<?php
/**
 * Test model
 *
 * Used for testing of the base model
 */
class Test_model extends \SlaxWeb\BaseModel\Model
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
}
