<?php
use \SlaxWeb\BaseModel\Constants as C;

/**
 * Test model with soft delete (status)
 *
 * Used for testing of the base model
 */
class SoftCol_model extends \SlaxWeb\BaseModel\Model
{
    public $softDelete = C::DELETESOFTSTATUS;
}
