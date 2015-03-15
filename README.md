BaseModel
=========

[![Build Status](https://travis-ci.org/slax0rr/BaseModel.svg?branch=feature%2FwhereBuilder)](https://travis-ci.org/slax0rr/BaseModel)

Base model for CodeIgniter, helps you with your database operations in the model. it auto guesses the table name from the model class name, saves you the hassle of soft deletes and more. BaseModel is also used by [BaseController](https://github.com/slax0rr/BaseModel).

The idea for the BaseModel came from Jamie Rumbelows [base model](https://github.com/jamierumbelow/codeigniter-base-model), with some additions and changes.
If you run into issues or have questions/ideas, please submit a ticket here on [GitHub](https://github.com/slax0rr/BaseModel/issues).

This is still in development phase, but is production ready. It has only been tested with mySQL, and at the time will probably work only with mySQL, although other drivers support has been added, but is EXPERIMENTAL.

Table of contents
=================
* [BaseModel](https://github.com/slax0rr/BaseModel/blob/master/README.md#basemodel)
* [Table of contents](https://github.com/slax0rr/BaseModel/blob/master/README.md#table-of-contents)
* [Install](https://github.com/slax0rr/BaseModel/blob/master/README.md#install)
  * [Use the BaseModel](https://github.com/slax0rr/BaseModel/blob/master/README.md#use-the-basemodel)
* [Properties](https://github.com/slax0rr/BaseModel/blob/master/README.md#properties)
* [Table name](https://github.com/slax0rr/BaseModel/blob/master/README.md#table-name)
  * [Guessing the table name](https://github.com/slax0rr/BaseModel/blob/master/README.md#guessing-the-table-name)
* [Config constants](https://github.com/slax0rr/BaseModel/blob/master/README.md#config-constants)
* [Database operations](https://github.com/slax0rr/BaseModel/blob/master/README.md#database-operations)
  * [Inserting data](https://github.com/slax0rr/BaseModel/blob/master/README.md#inserting-data)
  * [Getting data](https://github.com/slax0rr/BaseModel/blob/master/README.md#getting-data)
  * [Updating data](https://github.com/slax0rr/BaseModel/blob/master/README.md#updating-data)
  * [Deleting data](https://github.com/slax0rr/BaseModel/blob/master/README.md#deleting-data)
* [Joining tables](https://github.com/slax0rr/BaseModel/blob/master/README.md#joining-tables)
* [Building WHERE statements](https://github.com/slax0rr/BaseModel/blob/master/README.md#building-where-statements)
  * [Conditional operators](https://github.com/slax0rr/BaseModel/blob/master/README.md#conditional-operators)
  * [Comparison operators](https://github.com/slax0rr/BaseModel/blob/master/README.md#comparison-operators)
  * [Prefix columns with table names](https://github.com/slax0rr/BaseModel/blob/master/README.md#prefix-columns-with-table-names)
  * [Grouping WHERE expressions](https://github.com/slax0rr/BaseModel/blob/master/README.md#grouping-where-expressions)
  * [DEPRECATED - Conditional operators](https://github.com/slax0rr/BaseModel/blob/master/README.md#deprecated---conditional-operators)
  * [DEPRECATED - Comparison operators](https://github.com/slax0rr/BaseModel/blob/master/README.md#deprecated---comparison-operators)
  * [DEPRECATED - Grouping WHERE expressions](https://github.com/slax0rr/BaseModel/blob/master/README.md#deprecated---grouping-where-expressions)
* [SQL clauses](https://github.com/slax0rr/BaseModel/blob/master/README.md#sql-clauses)
  * [GROUP BY](https://github.com/slax0rr/BaseModel/blob/master/README.md#group-by)
  * [ORDER BY](https://github.com/slax0rr/BaseModel/blob/master/README.md#order-by)
  * [LIMIT](https://github.com/slax0rr/BaseModel/blob/master/README.md#limit)
* [Validation](https://github.com/slax0rr/BaseModel/blob/master/README.md#validation)
  * [Run validation manually](https://github.com/slax0rr/BaseModel/blob/master/README.md#run-validation-manually)
  * [Skipping validation](https://github.com/slax0rr/BaseModel/blob/master/README.md#skipping-validation)
* [Queries on soft deleted rows](https://github.com/slax0rr/BaseModel/blob/master/README.md#queries-on-soft-deleted-rows)
* [Results](https://github.com/slax0rr/BaseModel/blob/master/README.md#results)
  * [Getting column data](https://github.com/slax0rr/BaseModel/blob/master/README.md#getting-column-data)
  * [Number of rows](https://github.com/slax0rr/BaseModel/blob/master/README.md#number-of-rows)
  * [Get all rows](https://github.com/slax0rr/BaseModel/blob/master/README.md#get-all-rows)
  * [Traversing through rows](https://github.com/slax0rr/BaseModel/blob/master/README.md#traversing-through-rows)
* [Errors](https://github.com/slax0rr/BaseModel/blob/master/README.md#errors)
  * [Initialization of the Error class](https://github.com/slax0rr/BaseModel/blob/master/README.md#initialization-of-the-error-class)
  * [Add an error](https://github.com/slax0rr/BaseModel/blob/master/README.md#add-an-error)
  * [Has errors and count errors](https://github.com/slax0rr/BaseModel/blob/master/README.md#has-errors-and-count-errors)
  * [Getting errors](https://github.com/slax0rr/BaseModel/blob/master/README.md#getting-errors)
  * [Traversing through errors](https://github.com/slax0rr/BaseModel/blob/master/README.md#traversing-through-errors)
* [Thank you!](https://github.com/slax0rr/BaseModel/blob/master/README.md#thank-you)
* [ChangeLog](https://github.com/slax0rr/BaseModel/blob/master/README.md#changelog)

Install
=======

The easiest way to install at the moment is to use [composer](https://getcomposer.org/), or by installing the [BaseController](https://github.com/slax0rr/BaseModel), version 0.2+, which has BaseModel listed as requirement.
Simply create composer.json file in your project root:
```
{
  "require": {
    "slaxweb/ci-basemodel": "~0.3"
  }
}
```

Then run **composer.phar install**. When finished, enable composer autoloader in *application/config/config.php* configuration file.

Use the BaseModel
-----------------

The BaseModel is meant to be extended by your models, so, instead of extending from *CI_Model*, extend from **\SlaxWeb\BaseModel\Model**:
```PHP
class Some_model extends \SlaxWeb\BaseModel\Model
```
With this, your BaseModel is ready to use.

As an alternative, you can also extend from *MY_Model* and have MY_Model extending BaseModel.

Properties
==========

BaseModel provides you with the next set of properties you can set:
* **table** - The table of the model, although, BaseModel does auto-guess the table name from its own name.
* **tablePrefix** - The table prefix, if you use a special prefix, per model
* **primaryKey** - Defaults to "id", if you use something else as primary key, set it here
* **keyType** - Type of the key, Auto-Increment(default), UUID key, or a specific PHP or custom function you use to generate a primary key
* **keyFunc** - If you use a function to generate a primary key, assign it here, accepts type callable
* **keyFuncParams** - Parameters used by keyFunc function
* **softDelete** - Use soft delete, "hard" delete is default. Has 3 options, Soft delete status column, soft delete mark column, hard deletes
* **deleteCol** - If using soft delete mark column, define it here, default is "deleted"
* **statusCol** - If using soft delete status column, define the column here, default is "status"
* **deleteStatus** - Set the deleted status value for status column, default is "deleted"
* **rules** - Validation rules
* **where** - A custom where string
* **whereBinds** - If using a custom where string, and you want to bind your parameters into the where string, you can add those parameters to thir property as an array

At the moment BaseModel provides a single callback **beforeInit**, this callback is invoked before initialization of the BaseModel.

Table name
==========

There are 3 ways of defining a table name, first is to define a public property **table** in your model, passing the table name at construction time to the BaseModel *__construct* method, or just let BaseModel try and figure out your table name.

Guessing the table name
-----------------------

When guessing the table name, BaseModel takes the class name of your model, removes the *_model* or *_m* suffixes, pluralizes the remainder, and converts everything to lower case letters. So in example **User_model** becomes **users**.

Config constants
================

BaseModel provides a \SlaxWeb\BaseModal\Constants class for configuration of soft deletes and primary key types. To use in your controller, the best way is to *use* the class before declaration of your model.
```PHP
<?php
use \SlaxWeb\BaseModel\Constants as C;

class Test_model extends \SlaxWeb\BaseModel\model
{

}
```

And if you need to change soft delete settings or primary key type, simply use those constants:
* **DELETEHARD** - Hard delete
* **DELETESOFTMARK** - Use deleted column for marking of deleted rows
* **DELETESOFTSTATUS** - Use the status column for marking of deleted rows
* **PKEYAI** - Auto-incremented primary key
* **PKEYUUID** - Primary key generated in database by UUID()
* **PKEYFUNC** - Primary key generated by a PHP function or your custom function
* **PKEYNONE** - No primary key

Database oprations
==================

Inserting data
--------------

To insert data, the BaseModel provides a **insert** method which takes the data to be inserted as an array.
```PHP
$this->insert(array("column" => "value"));
```
The method will first try to validate data, if you have set the **rules** property and you have not set to skip the validation.

On error, the method will return an *Error* object, or true on success.

Getting data
------------

You can get data by the primary key, by your own where parameters, or just everything in there. For this, there are two methods, **get** and **getBy**. To retrieve a row by your primary key you call the **get** method with the primary key value as the input parameter.
```PHP
// retrieves row with primary key value 123
$this->get(123);
```
If you wish to get everything, just omit the primary key value in **get** method. To get data based on your own where statement you have two options, either set the BaseModel **where** property, or pass a "where" array to **getBy** method.
```PHP
$this->getBy(array("column" => "value"));
```
This method also provides a means to select only specific columns. You can pass an array of columns as the second parameter, or the columns as a string, as you would use them in a SELECT SQL statement.a

All get methods return the *Result* object.

Updating data
-------------

As well as getting data, updating also provides 3 ways, update by primary key, by your own where statement, or update everything in the table. Those two methods are **update** and **updateBy**. And they work pretty much the same as getting data, except you need to privde an array of data to be updated as the first parameter, and the primary key value or your where statement as the second parameter.
```PHP
$this->update(array("column" => "value"), 123);
```
This will update the column named "column" with the value where primary key value is 123.

To use your own where statement, you need to pass it as an array or a where string as you would use it in your SQL statement, to the **updateBy** method.
```PHP
$this->updateBy(array("column" => "value"), array("whereColumn" => "whereValue"));
```
Updating will first try to validate the data, if the rules are set and if you have not marked the validation to be skipped.

On error the methods return the *Error* object.

Deleting data
-------------

For deletion you once again have two methods, **delete** and **deleteBy**, and once again, you can delete by primary key value, your own where statement, or delete everything. If you are using deletion by status or deleted columns, this method will automatically make an update for you, and mark the row(s) as deleted. For usage examples, refer to (Getting data), because the usage is exactly the same, except the different method names.

Joining tables
==============

BaseModel also provides a way to join tables. For such, a **join** method is provided, which takes 3 input parameters. First is the table name to be joined, second is an array of join conditions, and third is the type of join, INNER per default. To switch the join type, Constants class provides 3 constants: JOININNER(default), JOINLEFT for left join, JOINRIGHT for right join.

First parameter is against which table you wish to join. The second parameter is the join conditions array and must be a nested array, which can have these options:
* **leftTable** - left table in condition, if not set, the models table is used
* **leftColumn** - left column in condition, can not be empty
* **rightTable** - right table in condition, if not set, the first passed in parameter is used as table name
* **rightColumn** - right column in condition, can not be empty
* **logicalOperator** - logical operator between multiple JOIN conditions, if not set, *AND* is used
```PHP
$this->join(
  "table2",
  array(
    array(
      "leftTable"   =>  "customTable",
      "leftColumn"  =>  "leftCol1",
      "rightTable"  =>  "rightCustomTable",
      "rightColumn" =>  "rightCol1",
    ),
    array(
      "leftColumn"      =>  "leftCol2",
      "rightColumn"     =>  "rightCol2",
      "logicalOperator" =>  "OR"
    )
  )
);
```

Above example will produce: *INNER JOIN \`table2\` ON (\`customTable\`.\`leftCol1\` = \`rightCustomTable\`.\`rightCol1\` OR \`models_table\`.\`leftCol2\` = \`table2\`.\`rightCol2\`)*.

**DEPRECATED**

This method below is DEPRECATED and should be avoided.

The first parameter is self explainatory, just pass in the name of the table. The second parameter must be a nested array which can have 2 and 3 items in it. The first item is the column from the left table in the join, second item is the column from the right table in the join. The third parameter is the link between multiple join conditions, defaults to AND.
```PHP
$this->join("table2", array(array("column1", "column1"), array("column2", "column2", "OR")));
```
Above example will produce: *INNER JOIN \`table2\` ON (\`models_table\`.\`column1\` = \`table2\`.\`column1\` OR \`models_table\`.\`column2\` = \`table2\`.\`column2\`)*.

After you have ran the query with the join, the join is reset, and you have to do it again if you want to re-use it.

Building WHERE statements
=========================

BaseModel provides a WHERE builder class, where you can easily build your own where statements.

The BaseModel provides this builder in its own property **wBuild**, and is already initiated, so you can go ahead and use it. To add expressions to the WHERE statement, the Builder class provides a **add** method, which accepts various input parameters of which 2 are mandatory.

In addition to the where builder being available through the **wBuild** property, you can also use the **where** method of the BaseModel, which returns the object of it self back, so you can also link together multiple where expressions as well as link further into a query.

DEPRECATED - BaseModel provides some variations in building your WHERE statement from an array, so you can do more complex WHERE statements than just normal *WHERE \`column1\` = 'value' AND \`column2\` = 'value'*.

Where expression
----------------

To add an expression to the WHERE statement, simply call the **add** method of the Builder class with column name and value as input parameters.
```PHP
$this->wBuild->add("columnName", "value");
```

Above example will produce a simple where statement: *\`columnName\` = ?* and put the value of the expression to the *bind* array, which will be auto-bound later to your query.

The same thing with the *where* method.
```PHP
$this->where("columnName1", "value")->get();
```

The **add** method returns the Builder object, so you can link together the method calls, and each subsequent call to **add** method will use the *AND* logical operator between expressions.
```PHP
$this->wBuild->add("columnName1", "value1")->add("columnName2", 10);
```

The above example will produce: *\`columnName1\` = ? AND \`columnName2\` = 10*, notice the second one is not a *question mark*, because it does not need to be bound, and it is safe to simply add the value to the query.

You can also pass an array and the Builder will compose a list of all items separated by commas. If there are more than one items, the comma separated list is encapsulated in parenthesis, if there is more than one item in the array, so this is used for *IN/NOT IN* expressions.
```PHP
$this->wBuild->add("columnName", array("value1", "value2"), "",  "IN");
```

The above example will produce: *\`columnName\` IN (?,?)*, and once again it will add the values to the *bind* array. You may also have noticed that this  example uses more than 2 input parameters, but more on that later.

As well as an array, you can pass in an object, which will be cast to string, and then the whole string is exploded into an array and comma is used as delimiter, so a secure list can be composed in the same way as by the array. This enables you to simply use the Result object of previous queries in the where builder. The Result method *__toString* will compose the list of the first column accross all rows, so in the next example we assume *$this->get(123)*, returns 2 rows with the first column values: *value1* and *value2*.
```PHP
$this->wBuild->add("columnName", $this->get(123), "", "NOT IN");
```

The above example will produce *\`columnName\` IN (?,?)*, with *value1* and *value2* in the *bind* array.

To use a completely custom where statement, you can pass it into the *where* method as an array, containing the where statement as first element and second the binds for your statement.
```PHP
$this->where(array("columnName1 = ?", array("value1")))->get();
```

If you have no parameters for binding, just pass the where statement string in the array.

Conditional operators
---------------------

To change the conditional operator to anything other than **AND** which is default, simply pass whatever logical operator you want as the third input parameter.

Comparison operators
--------------------

To change the comparison operator from the defaul **=** pass it to the **add** method as the fourth input parameter.

Prefix columns with table names
-------------------------------

It could be necesarry, especially in joined queries to prepend some column names with its respective table name, this can be done by passing the name of the table as the fifth parameter.
```PHP
$this->wBuild->add("columnName", "value", "", "", "tableName");
```

The above example will produce: *\`tableName\`.\`columnName\` = ?*.

Grouping WHERE expressions
--------------------------

To group where expressions you need to tell the where builder where to start the group and where to end it, and you do this by setting the sixth parameter to boolean *true*, and where builder will begin the group there, and when you want to end it, pass boolean *false* as the sixth parameter, and where builder will close the group after that expression.

DEPRECATED - Conditional operators
----------------------------------

To replace AND with any other conditional operator between two WHERE expressions, you have to prefix your column name in the array key with your desired conditional operator.
```PHP
$this->getBy(array("column1" => "value1", "OR column2" => "value2"));
```
The above example will produce **WHERE \`column1\` = 'value1' OR \`column2\` = 'value2'**.

**NOTE:** at the moment this works only with *OR*, working on more.

DEPRECATED - Comparison operators
---------------------------------

Normally BaseModel uses the *equal* comparison operator between column and value, but should you need any other, you have add it as a suffix to the column name in the where array.
```PHP
$this->getBy(array("column1 <" => 10));
```
The above example will produce **WHERE \`column1\` < 10**.

DEPRECATED - Grouping WHERE expressions
---------------------------------------

You can also group sets of expressions as you wish to. To do so, simply add a sub-array with further where expressions in this sub-array.
```PHP
$this->getBy(
 array(
  array(
   "groupCol1" => "groupVal1",
   "groupCol2" => "groupVal2"
  ),
  "OR column1" => "value1"
 )
);
```
Above example will produce **WHERE (\`groupCol1\` = 'groupVal1' AND \`groupCol2\` = 'groupVal2') OR \`column1\` = 'value1'**.

SQL clauses
===========

BaseModel suppors some SQL clauses that you can use.

GROUP BY
--------

To add a group by clause to the next query, set it with the **groupBy** method. The input parameter must be an array containing the columns you wish to group by. It returns the object of the model so you can link your method calls.

ORDER BY
--------

To add an order by clause to the query set it with the **orderBy** method. It will be used only for the next query. First parameter must be an array, and must contain column names. The second parameter is the direction of order by, default is "ascending". The method returns the object of the model, so you can link together your method calls.

To use multiple sorting directions in the ORDER BY statement, pass the column names as array keys in first parameter, and direction of each column as the array value and omit the second parameter, example:
```PHP
$this->orderBy(
  array(
    "col1" => "ASC",
    "col2  => "DESC"
  )
);
```

Above example will produce *ORDER BY `col1` ASC, `col2` DESC*.

LIMIT
-----

To add a limit clause to the next query, set it with the **limit** method. It takes two integer parameters, the first one is the limit on how many rows you want to affect with your query, and the second, default int(0) is the offset, at which row to begin counting.

Validation
==========

BaseModel automatically validates your data when inserting or updating, as long as you provide it the validation rules in the **rules** proeprty. The rules have to be CodeIgniter Form Validation compliant.
```PHP
$this->rules = array(
    array(
        "field" =>  "fieldName",
        "label" =>  "Field label",
        "rules" =>  "required|max_length[100]"
    )
);
$this->insert(array("fieldName" => "fieldValue"));
```

Run validation manually
-----------------------

You can also run the validation manually, with the **validate** method. Once again, you need to have rules set in the **rules** property.
```PHP
$this->rules = array(
    array(
        "field" =>  "fieldName",
        "label" =>  "Field label",
        "rules" =>  "required|max_length[100]"
    )
);
$this->validate(array("fieldName" => "fieldValue"));
```

Skipping validation
-------------------

To skip the validation you either set the rules to an empty array, or prior to calling insert or update methods call the **skipValidation** method, which will skip the validation for the next query. **skipValidation** returns back the model object, so you can link your query after it.
```PHP
$this->skipValidation()->insert($data);
```

Queries on soft deleted rows
============================

If you wish to run update/get queries on the soft deleted rows, you need to call the **withDeleted** method before executing your query, or use a custom where string. The **withDeleted** will allow to include deleted rows only for the next query in your update or select queries. It also returns the model object, so you can link your queries.
```PHP
$this->withDeleted()->get();
```

Results
=======

The select query will return a *Result* object, from which you can then access data, and traverse through rows.

Getting column data
-------------------

To get the data, just access the Result object property, and use the column name as the property name.
```PHP
$result = $this->getBy(array("whereColumn" => "whereValue"), "column");
$result->column;
```

If the result is multi-row, the first row is used for data retrieval. If the column doesn't exist, *null* is returned.

Get as array
------------

To get all columns as array, simply call the **asArray** method, this will return the current row you're on as an array.
```PHP
$result->asArray();
```

Number of rows
--------------

To get the number of rows just call the **rowCount** method.

Get all rows
------------

To get all the rows at once, call **getResult** method.

Traversing through rows
-----------------------

To traverse through the rows, the Result class provides 3 methods:
* next - moves to the next row
* prev - moves to the previous row
* row - moves to the row you specify in the input parameter

All three methods return the object to it self, or false if the next, previous or specific rows don't exist.

Errors
======

Error class provides an easier mean to handle errors, and enables a way for you to assign error messages for specific errors easily.

Initialization of the Error class
---------------------------------

In order to successfully assign error messages, a language array must be passed to the Error object at construction time. BaseModel does this for you in some methods where the error occurs, if you need to do it your self in your own method, the best and easiest way is to supply it with the CodeIgniter language array ($this->lang->language).

Add an error
------------

To add an error you have to provide an error code, and as optional parameters you can provide an integer severity level, and an array of additional error data. When adding, Error class automatically sorts your errors based on the severity. As well as it searches for a respective error message in the provided language array. The key for the message has to be "error_your_error_code" all in lower case letters.

Has errors and count errors
---------------------------

To check if there are errors use the **hasErrors** method, and to check how many errors are there, use the **errorCount** method.
```PHP
$error->hasErrors();
$error->errorCount();
```

Getting errors
--------------

You can get all errors at once with the **getErrors** method, or the current error with **get** method. You can also get an error at specific index with **errorAt**, or an error which contains your code that you supply as the input parameter with the **error** method.

Traversing through errors
-------------------------

The same as the Result class, Error class provides a **prev** and **next** methods, that return false if there is no previous or next errors, or the object to it self for method linking, but it does not provide a method like **row**, except the **errorAt** which returns already the error at the provided index.

Thank you!
==========

I would like to thank all who contributed to this project, by either ideas, testing, proofreading of documentation and so on:
* [Marco Monteiro](https://github.com/mpmont)
* [Sami Keinänen](https://github.com/skope)
* [Saso Sabotin](https://github.com/sasos90)

ChangeLog
=========

0.4.2
-----

* No functionality changes, new tests, and version bump

0.4.1
-----

* Properly handle bool values in queries
* Properly handle bool values in where expression builder

0.4.0
-----

* Automatically try to guess table primary key column

0.3.6
-----

* Always wrap bound list in parenthesis, even if only one value is found in the list
* Fix minor typo in README

0.3.5
-----

* Reset form validation before validating

0.3.4
-----

* Set data to form validation manually

0.3.3
-----

* Multiple sort directions in ORDER BY statement
* Proper LIMIT syntax for non-mySQL databases

0.3.2
-----

* Fix issue of being unable to specify specific table for JOIN statements

0.3.1
-----

* Fix custom where string parsing bug

0.3.0
-----

* Add Error class
* Add validation
* Add primary key type
* Add GROUP BY, ORDER BY and LIMIT clauses
* Add WHERE statement builder
* Add JOIN statements
* Add other database driver than mysql(i) capability - EXPERIMENTAL!

0.2.5
-----

* Reset the where array between queries

0.2.4
-----

* Remove WHERE keyword from query when there is no WHERE statement

0.2.3
-----

* With deleted where statement was mistakingly removed

0.2.2
-----

* Add backtics to column names

0.2.1
-----

* Remove quotes on bound placeholders

0.2.0
-----

* Add insert method
* Bind parameters to update statement

0.1.0
-----

* Initial release
