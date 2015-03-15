<?php
require_once "Tests/Support/TestHelper.php";

use \Mockery as m;

class ModelTest extends PHPUnit_Framework_TestCase
{
    public function testTableGuess()
    {
        $model = new Test_model();
        $this->assertEquals("test", $model->table);
    }

    public function testPrimaryKey()
    {
        $model = new Test_model();
        $this->assertEquals("pk", $model->primaryKey);
    }

    public function testBeforeInitHook()
    {
        $model = new Test_model();
        $this->assertTrue($model->beforeInitRun);
    }

    public function testLimit()
    {
        $model = new Test_model();
        $model->limit(10, 5);
        $this->assertEquals("LIMIT 5, 10", $model->getProtected("_limit"));

        $model = new Postgre_model();
        $model->setDriver();
        $model->limit(10, 5);
        $this->assertEquals("LIMIT 10 OFFSET 5", $model->getProtected("_limit"));
    }

    public function testOrderBy()
    {
        $model = new Test_model();
        $model->orderBy(array("col1", "col2"));
        $this->assertEquals("ORDER BY `col1`,`col2` asc", $model->getProtected("_orderBy"));
        $model->orderBy(array("col1", "col2"), "desc");
        $this->assertEquals("ORDER BY `col1`,`col2` desc", $model->getProtected("_orderBy"));
        $model->orderBy(array("col1" => "ASC", "col2" => "DESC"));
        $this->assertEquals("ORDER BY `col1` ASC,`col2` DESC", $model->getProtected("_orderBy"));
    }

    public function testGroupBy()
    {
        $model = new Test_model();
        $model->groupBy(array("col1", "col2"));
        $this->assertEquals("GROUP BY `col1`,`col2`", $model->getProtected("_groupBy"));
    }

    public function testSkipValidation()
    {
        $model = new Test_model();
        $model->skipValidation();
        $this->assertTrue($model->getProtected("_ignoreValidation"));
    }

    public function testSetWithDeleted()
    {
        $model = new Test_model();
        $model->withDeleted();
        $this->assertTrue($model->getProtected("_ignoreSoftDelete"));
    }

    public function testJoin()
    {
        $model = new Test_model();
        $model->join(
            "table",
            array(
                array("col1", "col1"),
                array("col2", "col2", "OR"),
                array("col3", "col3")
            )
        );
        $this->assertEquals(
            "INNER JOIN `table` ON (`test`.`col1` = `table`.`col1` OR `test`.`col2` = `table`.`col2` " .
            "AND `test`.`col3` = `table`.`col3` ) ",
            $model->getProtected("_join")
        );

        $model = new Test_model();
        $model->join(
            "table",
            array(
                array(
                    "leftTable"     =>  "lefty",
                    "leftColumn"    =>  "leftCol1",
                    "rightTable"    =>  "righty",
                    "rightColumn"   =>  "rightCol1"
                ),
                array(
                    "leftColumn"        =>  "leftCol2",
                    "rightTable"        =>  "righty",
                    "rightColumn"       =>  "rightCol2",
                    "logicalOperator"   =>  "OR"
                ),
                array(
                    "leftTable"         =>  "lefty",
                    "leftColumn"        =>  "leftCol3",
                    "rightColumn"       =>  "rightCol3"
                )
            )
        );
        $this->assertEquals(
            "INNER JOIN `table` ON (`lefty`.`leftCol1` = `righty`.`rightCol1` OR " .
            "`test`.`leftCol2` = `righty`.`rightCol2` AND `lefty`.`leftCol3` = `table`.`rightCol3` ) ",
            $model->getProtected("_join")
        );
    }

    public function testInsert()
    {
        $model = new Test_model();
        $data = array("col1" => "val1");
        // prepare the db->query mock
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("INSERT INTO `test` (`col1`) VALUES (?)", array("val1"))
            ->once()
            ->andReturn(true)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertTrue($model->insert($data));

        // reset query mock for error
        $model->db = m::mock("db")->shouldReceive("query")
            ->withAnyArgs()
            ->once()
            ->andReturn(false)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $model->lang = new \stdClass();
        $model->lang->language = array("error_create_error" => "unit test error");
        $this->assertInstanceOf("\\SlaxWeb\\BaseModel\\Error", $model->insert($data));
    }

    public function testInsertBool()
    {
        $model = new Test_model();
        $data = array("col1" => true);
        // prepare the db->query mock
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("INSERT INTO `test` (`col1`) VALUES (true)", array())
            ->once()
            ->andReturn(true)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertTrue($model->insert($data));
        $this->assertEquals("INSERT INTO `test` (`col1`) VALUES (true)", $model->getQuery());
    }

    public function testSelect()
    {
        $model = new Test_model();
        // test a failed select and that the sql is correctly formed
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("SELECT * FROM `test`     ", array())
            ->twice()
            ->andReturn(
                false,
                m::mock("query")
                ->shouldReceive("result_object")
                ->withNoArgs()
                ->once()
                ->andReturn(array())
                ->mock()
            )
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertFalse($model->getBy());

        // test a successful select
        $this->assertInstanceOf("\\SlaxWeb\\BaseModel\\Result", $model->getBy());

        // test selecting a specific primary key value
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("SELECT * FROM `test`  WHERE  `pk` = 123   ", array())
            ->once()
            ->andReturn(
                m::mock("query")
                ->shouldReceive("result_object")
                ->withNoArgs()
                ->once()
                ->andReturn(array())
                ->mock()
            )
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertInstanceOf("\\SlaxWeb\\BaseModel\\Result", $model->get(123));
    }

    public function testUpdate()
    {
        $model = new Test_model();
        // test a failed update and that the sql is correctly formed
        $model->lang = new \stdClass();
        $model->lang->language = array("error_update_error" => "unit test error");
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("UPDATE `test` SET `col1` = ?     ", array("val1"))
            ->twice()
            ->andReturn(false, true)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertInstanceOf("\\SlaxWeb\\BaseModel\\Error", $model->updateBy(array("col1" => "val1"), array()));

        // test a successful update
        $this->assertTrue($model->updateBy(array("col1" => "val1"), array()));
    }

    public function testUpdateBool()
    {
        $model = new Test_model();
        // test a failed update and that the sql is correctly formed
        $model->lang = new \stdClass();
        $model->lang->language = array("error_update_error" => "unit test error");
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("UPDATE `test` SET `col1` = false     ", array())
            ->twice()
            ->andReturn(true)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();

        // test a successful update
        $this->assertTrue($model->updateBy(array("col1" => false), array()));
        $this->assertEquals("UPDATE `test` SET `col1` = false     ", $model->getQuery());
    }

    public function testDelete()
    {
        $model = new Test_model();
        // test a failed hard delete
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("DELETE FROM `test`  WHERE `col1` = ?    ", array("val1"))
            ->twice()
            ->andReturn(false, true)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertFalse($model->deleteBy(array("col1" => "val1")));

        // test a successful delete
        $this->assertTrue($model->deleteBy(array("col1" => "val1")));

        // test a failed soft delete (mark)
        $model = new SoftMark_model();
        $model->lang = new \stdClass();
        $model->lang->language = array("error_update_error" => "unit test error");
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("UPDATE `softmark` SET `deleted` = true  WHERE  `deleted` = false   ", array())
            ->twice()
            ->andReturn(false, true)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertInstanceOf("\\SlaxWeb\\BaseModel\\Error", $model->deleteBy(array()));

        // test a successful soft delete (mark)
        $this->assertTrue($model->deleteBy(array()));

        // test a failed soft delete (status)
        $model = new SoftCol_model();
        $model->lang = new \stdClass();
        $model->lang->language = array("error_update_error" => "unit test error");
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("UPDATE `softcol` SET `status` = ?  WHERE  `status` != ?   ", array("deleted", "deleted"))
            ->twice()
            ->andReturn(false, true)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertInstanceOf("\\SlaxWeb\\BaseModel\\Error", $model->deleteBy(array()));

        // test a successful soft delete (status)
        $this->assertTrue($model->deleteBy(array()));
    }

    public function testWhereBuild()
    {
        $model = new Test_model();
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("SELECT * FROM `test`  WHERE  `col1` = ?   ", array("val1"))
            ->once()
            ->andReturn(false)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertFalse($model->where("col1", "val1")->getBy());


        $model->db = m::mock("db")->shouldReceive("query")
            ->with("SELECT * FROM `test`  WHERE col1 = ?   ", array("val1"))
            ->once()
            ->andReturn(false)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertFalse($model->where(array("col1 = ?", array("val1")))->getBy());
    }

    public function testCombinedWhereBuild()
    {
        $model = new Test_model();
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("SELECT * FROM `test`  WHERE `oldCol1` = ? AND `newCol1` = ?   ", array("oldVal1", "newVal1"))
            ->once()
            ->andReturn(false)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $model->wBuild->add("newCol1", "newVal1");
        $this->assertFalse($model->getBy(array("oldCol1" => "oldVal1")));
    }

    public function testMultipleSelects()
    {
        $model = new Test_model();
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("SELECT * FROM `test`  WHERE `col1` = ?    ", array("val1"))
            ->once()
            ->andReturn(false)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertFalse($model->getBy(array("col1" => "val1")));
        $this->assertFalse($model->getBy(array("col1" => "val1")));
        $this->assertFalse($model->getBy(array("col1" => "val1")));
        $this->assertFalse($model->getBy(array("col1" => "val1")));
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("SELECT * FROM `test`  WHERE  `col2` = ?   ", array("val2"))
            ->once()
            ->andReturn(false)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $model->wBuild->add("col2", "val2");
        $this->assertFalse($model->getBy());
        $model->wBuild->add("col2", "val2");
        $this->assertFalse($model->getBy());
        $model->wBuild->add("col2", "val2");
        $this->assertFalse($model->getBy());
        $model->wBuild->add("col2", "val2");
        $this->assertFalse($model->getBy());
    }

    public function testPredefinedSelectColumns()
    {
        $model = new Test_model();
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("SELECT `col1`,`col2` FROM `test`  WHERE  `col1` = ?   ", array("val1"))
            ->once()
            ->andReturn(false)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $model->wBuild->add("col1", "val1");
        $this->assertFalse($model->getBy("", array("col1", "col2")));
    }

    public function testPostgreDriverEscapes()
    {
        $model = new Postgre_model();
        $model->setDriver();
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("SELECT \"col1\",\"col2\" FROM \"postgre\"  WHERE  \"col1\" = ?   ", array("val1"))
            ->once()
            ->andReturn(false)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $model->wBuild->add("col1", "val1");
        $this->assertFalse($model->getBy("", array("col1", "col2")));

        // tet the insert statement
        $data = array("col1" => "val1");
        $model->db = m::mock("db")->shouldReceive("query")
            ->with("INSERT INTO \"postgre\" (\"col1\") VALUES (?)", array("val1"))
            ->once()
            ->andReturn(true)
            ->shouldReceive("last_query")
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertTrue($model->insert($data));
    }
}
