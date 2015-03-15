<?php
class WhereBuilderTest extends PHPUnit_Framework_TestCase
{

    public function testAdd()
    {
        $builder = new \SlaxWeb\BaseModel\Where\Builder();
        $builder->add(
            "columnName",
            "value"
        );
        $this->assertNotEmpty($builder->get());

        return $builder;
    }

    /**
     * @depends testAdd
     */
    public function testGet($builder)
    {
        $builder->reset();
        $get = $builder->get();
        $this->assertInstanceOf("\\SlaxWeb\\BaseModel\\Where\\Expression", $get);
        $this->assertEquals("columnName", $get->column);
        $this->assertEquals("value", $get->value);
    }

    public function testSimpleExpression()
    {
        $builder = new \SlaxWeb\BaseModel\Where\Builder();
        $builder->add("columnName", "value");
        $this->assertEquals("`columnName` = ?", ltrim($builder->toString()));
        $this->assertEquals("value", $builder->binds[0]);
    }

    public function testBoolValue()
    {
        $builder = new \SlaxWeb\BaseModel\Where\Builder();
        $builder->add("columnName", false);
        $this->assertEquals("`columnName` = false", $builder->toString());
    }

    public function testMultiExpressions()
    {
        $builder = new \SlaxWeb\BaseModel\Where\Builder();
        $builder->add("columnName", "value")->add("columnName2", "value2");
        $this->assertEquals("`columnName` = ? AND `columnName2` = ?", ltrim($builder->toString()));
        $this->assertEquals("value", $builder->binds[0]);
        $this->assertEquals("value2", $builder->binds[1]);
    }

    public function testExpressionWithTable()
    {
        $builder = new \SlaxWeb\BaseModel\Where\Builder();
        $builder->add("columnName", "value", "", "", "table");
        $this->assertEquals("`table`.`columnName` = ?", ltrim($builder->toString()));
    }

    public function testLogicalOperatorExpression()
    {
        $builder = new \SlaxWeb\BaseModel\Where\Builder();
        $builder->add("columnName", "value")->add("columnName2", "value2", "OR");
        $this->assertEquals("`columnName` = ? OR `columnName2` = ?", ltrim($builder->toString()));
    }

    public function testComparatorExpression()
    {
        $builder = new \SlaxWeb\BaseModel\Where\Builder();
        $builder->add("columnName", "value")->add("columnName2", 10, "", "<");
        $this->assertEquals("`columnName` = ? AND `columnName2` < 10", ltrim($builder->toString()));
    }

    public function testGroupedExpressions()
    {
        $builder = new \SlaxWeb\BaseModel\Where\Builder();
        $builder->add("columnName", "value")
            ->add("columnName2", 10, "OR", "<", "", true)
            ->add("columnName3", "value3", "", "", "", false);
        $this->assertEquals("`columnName` = ? OR (`columnName2` < 10 AND `columnName3` = ?)", ltrim($builder->toString()));
    }

    public function testInExpression()
    {
        $builder = new \SlaxWeb\BaseModel\Where\Builder();
        $builder->add("columnName", array("value", 10), "", "IN");
        $this->assertEquals("`columnName` IN (?,10)", ltrim($builder->toString()));
        $this->assertEquals("value", $builder->binds[0]);
    }
}
