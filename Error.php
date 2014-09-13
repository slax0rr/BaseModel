<?php
namespace SlaxWeb\BaseModel;

/**
 * Error library for BaseModel
 *
 * Set errors, library will try to set a friendly, translated message, ready
 * for printing out to user.
 *
 * @author Tomaz Lovrec <tomaz.lovrec@gmail.com>
 */
class Error
{
    /**
     * Language
     *
     * @var array
     */
    protected $_language = array();
    /**
     * Errors
     *
     * @var array
     */
    protected $_errors = array();
    /**
     * Current error
     *
     * Array containing an error object and current error index
     *
     * @var array
     */
    protected $_currError = array();

    /**
     * Constructs the class and copies the language string to the class property.
     *
     * @param   array   Language strings
     */
    public function __construct(array $language)
    {
        $this->_language = $language;
        $this->_currError = array("error" => null, "index" => null);
    }

    /**
     * Add a new error to the errors array
     *
     * Supports severity (default 0) levels, higher number, higher severity
     * and supports additional error data, which has to be in an array,
     * again, you can set whatever you wish to.
     * To differentiate from errors, you need to provide a string error code
     * as the first parameter. This error code is also used to obtain
     * the translated string from the language array. The key in language array
     * has to be: error_your_errorcode
     *
     * @param   string  Error code
     * @param   int     Severity of the error, higher number means higher severity
     * @param   array   Additional error data, once again, anything you want.
     */
    public function add($code, $severity = 0, array $data = array())
    {
        $error = new \stdClass();
        $error->code = $code;
        $error->message = isset($this->_language["error_" . strtolower($code)])
            ? $this->_language["error_" . strtolower($code)] : "";
        $error->severity = $severity;
        $error->data = $data;
        $this->_errors[] = $error;
        $this->_sort();
    }

    /**
     * Check if there are errors
     *
     * @return  bool    If there are errors, return true, false otherwise
     */
    public function hasErrors()
    {
        return !empty($this->_errors);
    }

    /**
     * Error count
     *
     * @return  int     Number of errors
     */
    public function errorCount()
    {
        return count($this->_errors);
    }

    /**
     * Get all errors
     *
     * @return array    Whole error array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Get current error
     *
     * @return  object  Current error object
     */
    public function get()
    {
        if ($this->_currError["error"] === null) {
            $this->_currError["error"] = $this->_errors[0];
            $this->_currError["index"] = 0;
        }

        return $this->_currError["error"];
    }

    /**
     * Load the next error to current error object
     *
     * @return  object  Object of this class or false if index is out of bounds
     */
    public function next()
    {
        if (isset($this->_errors[$this->_currError["index"] + 1])) {
            $this->_currError["error"] = $this->_errors[++$this->_currError["index"]];
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Load the previous error to current error object
     *
     * @return  object  Object of this class or false if index is out of bounds
     */
    public function prev()
    {
        if (isset($this->_errors[$this->_currError["index"] - 1])) {
            $this->_currError["error"] = $this->_errors[--$this->_currError["index"]];
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Load the specific error to current error object
     *
     * @param   int     Index of the error object
     *
     * @return  object  Object of this class or false if index is out of bounds
     */
    public function errorAt($index)
    {
        if (isset($this->_errors[$index])) {
            $this->_currError["error"] = $this->_errors[$index];
            $this->_currError["index"] = $index;
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Error with specific code
     *
     * Retun the erorr object if found, or false otherwise
     *
     * @return  mixed
     */
    public function error($code)
    {
        foreach ($this->_errors as $error) {
            if ($error->code === $code) {
                return $error;
            }
        }
        return false;
    }

    /**
     * Sort errors by severity
     */
    protected function _sort()
    {
        usort($this->_errors, function($a, $b) {
            if ($a->severity === $b->severity) {
                return 0;
            }
            return ($a->severity < $b->severity) ? -1 : 1;
        });
    }
}
