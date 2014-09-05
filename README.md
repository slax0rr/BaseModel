BaseModel
=========

Base model for CodeIgniter, helps you with your database operations in the model. it auto guesses the table name from the model class name, saves you the hassle of soft deletes and more. BaseModel is also used by [BaseController](https://github.com/slax0rr/BaseModel).

The idea for the BaseModel came from Jamie Rumbelows [base model](https://github.com/jamierumbelow/codeigniter-base-model), with some additions and changes. At this point I would also like to thank [Marco Monteiro](https://github.com/mpmont) and [Sami Keinänen](https://github.com/skope) for their help.

If you run into issues or have questions/ideas, please submit a ticket here on [GitHub](https://github.com/slax0rr/BaseModel/issues).

This is still in development phase, but is available for public as early-beta. Please use with caution, I can not guarantee that something will not change along the way.

Table of contents
=================

Install
=======

The easiest way to install at the moment is to use [composer](https://getcomposer.org/), or by installing the [BaseController](https://github.com/slax0rr/BaseModel), version 0.2+, which has BaseModel listed as requirement.
Simply create composer.json file in your project root:
```
{
  "require": {
    "slaxweb/ci-basemodel": "~0.1"
  }
}
```

Then run **composer.phar install**. When finished, edit CodeIgniter index.php file and add this line right after PHP opening tag:
```PHP
require_once "vendor/autoload.php";
```

Use the BaseModel
-----------------

The BaseModel is meant to be extended by your models, so, instead of extending from *CI_Model*, extend from **\SlaxWeb\BaseModel\Model**:
```PHP
class Some_model extend \SlaxWeb\BaseModel\Model
```

This is just a draft...More documentation will follow...
