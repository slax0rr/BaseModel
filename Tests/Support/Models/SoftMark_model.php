<?php
use \SlaxWeb\BaseModel\Constants as C;

/**
 * Test model with soft delete (mark)
 *
 * Used for testing of the base model
 */
class SoftMark_model extends \SlaxWeb\BaseModel\Model
{
    public $softDelete = C::DELETESOFTMARK;
}
