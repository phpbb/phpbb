<?php
/**
*
* @package phpbb_request
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* All application input is accessed through this class.
*
* It provides a method to disable access to input data through super globals.
* This should force MOD authors to read about data validation.
*
* @package phpbb_request
*/
class phpbb_request implements phpbb_request_interface
{
	/**
	* @var	array	The names of super global variables that this class should protect if super globals are disabled.
	*/
	protected $super_globals = array(
		phpbb_request_interface::POST => '_POST',
		phpbb_request_interface::GET => '_GET',
		phpbb_request_interface::REQUEST => '_REQUEST',
		phpbb_request_interface::COOKIE => '_COOKIE',
		phpbb_request_interface::SERVER => '_SERVER',
	);

	/**
	* @var	array	Stores original contents of $_REQUEST array.
	*/
	protected $original_request = null;

	/**
	* @var
	*/
	protected $super_globals_disabled = false;

	/**
	* @var	array	An associative array that has the value of super global constants as keys and holds their data as values.
	*/
	protected $input;

	/**
	* @var	phpbb_request_type_cast_helper_interface	An instance of a type cast helper providing convenience methods for type conversions.
	*/
	protected $type_cast_helper;

	/**
	* Initialises the request class, that means it stores all input data in {@link $input input}
	* and then calls {@link phpbb_request_deactivated_super_global phpbb_request_deactivated_super_global}
	*/
	public function __construct(phpbb_request_type_cast_helper_interface $type_cast_helper = null, $disable_super_globals = true)
	{
		if ($type_cast_helper)
		{
			$this->type_cast_helper = $type_cast_helper;
		}
		else
		{
			$this->type_cast_helper = new phpbb_request_type_cast_helper();
		}

		foreach ($this->super_globals as $const => $super_global)
		{
			$this->input[$const] = isset($GLOBALS[$super_global]) ? $GLOBALS[$super_global] : array();
		}

		// simulate request_order = GP
		$this->original_request = $this->input[phpbb_request_interface::REQUEST];
		$this->input[phpbb_request_interface::REQUEST] = $this->input[phpbb_request_interface::POST] + $this->input[phpbb_request_interface::GET];

		if ($disable_super_globals)
		{
			$this->disable_super_globals();
		}
	}

	/**
	* Getter for $super_globals_disabled
	*
	* @return	bool	Whether super globals are disabled or not.
	*/
	public function super_globals_disabled()
	{
		return $this->super_globals_disabled;
	}

	/**
	* Disables access of super globals specified in $super_globals.
	* This is achieved by overwriting the super globals with instances of {@link phpbb_request_deactivated_super_global phpbb_request_deactivated_super_global}
	*/
	public function disable_super_globals()
	{
		if (!$this->super_globals_disabled)
		{
			foreach ($this->super_globals as $const => $super_global)
			{
				unset($GLOBALS[$super_global]);
				$GLOBALS[$super_global] = new phpbb_request_deactivated_super_global($this, $super_global, $const);
			}

			$this->super_globals_disabled = true;
		}
	}

	/**
	* Enables access of super globals specified in $super_globals if they were disabled by {@link disable_super_globals disable_super_globals}.
	* This is achieved by making the super globals point to the data stored within this class in {@link $input input}.
	*/
	public function enable_super_globals()
	{
		if ($this->super_globals_disabled)
		{
			foreach ($this->super_globals as $const => $super_global)
			{
				$GLOBALS[$super_global] = $this->input[$const];
			}

			$GLOBALS['_REQUEST'] = $this->original_request;

			$this->super_globals_disabled = false;
		}
	}

	/**
	* This function allows overwriting or setting a value in one of the super global arrays.
	*
	* Changes which are performed on the super globals directly will not have any effect on the results of
	* other methods this class provides. Using this function should be avoided if possible! It will
	* consume twice the the amount of memory of the value
	*
	* @param	string	$var_name	The name of the variable that shall be overwritten
	* @param	mixed	$value		The value which the variable shall contain.
	* 								If this is null the variable will be unset.
	* @param	phpbb_request_interface::POST|GET|REQUEST|COOKIE	$super_global
	* 								Specifies which super global shall be changed
	*/
	public function overwrite($var_name, $value, $super_global = phpbb_request_interface::REQUEST)
	{
		if (!isset($this->super_globals[$super_global]))
		{
			return;
		}

		$this->type_cast_helper->add_magic_quotes($value);

		// setting to null means unsetting
		if ($value === null)
		{
			unset($this->input[$super_global][$var_name]);
			if (!$this->super_globals_disabled())
			{
				unset($GLOBALS[$this->super_globals[$super_global]][$var_name]);
			}
		}
		else
		{
			$this->input[$super_global][$var_name] = $value;
			if (!$this->super_globals_disabled())
			{
				$GLOBALS[$this->super_globals[$super_global]][$var_name] = $value;
			}
		}

		if (!$this->super_globals_disabled())
		{
			unset($GLOBALS[$this->super_globals[$super_global]][$var_name]);
			$GLOBALS[$this->super_globals[$super_global]][$var_name] = $value;
		}
	}

	/**
	* Central type safe input handling function.
	* All variables in GET or POST requests should be retrieved through this function to maximise security.
	*
	* @param	string|array	$var_name	The form variable's name from which data shall be retrieved.
	* 										If the value is an array this may be an array of indizes which will give
	* 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
	* 										then specifying array("var", 1) as the name will return "a".
	* @param	mixed			$default	A default value that is returned if the variable was not set.
	* 										This function will always return a value of the same type as the default.
	* @param	bool			$multibyte	If $default is a string this paramater has to be true if the variable may contain any UTF-8 characters
	*										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	* @param	phpbb_request_interface::POST|GET|REQUEST|COOKIE	$super_global
	* 										Specifies which super global should be used
	*
	* @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
	*					the same as that of $default. If the variable is not set $default is returned.
	*/
	public function variable($var_name, $default, $multibyte = false, $super_global = phpbb_request_interface::REQUEST)
	{
		return $this->_variable($var_name, $default, $multibyte, $super_global, true);
	}

	/**
	* Get a variable, but without trimming strings
	* Same functionality as variable(), except does not run trim() on strings
	* All variables in GET or POST requests should be retrieved through this function to maximise security.
	*
	* @param	string|array	$var_name	The form variable's name from which data shall be retrieved.
	* 										If the value is an array this may be an array of indizes which will give
	* 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
	* 										then specifying array("var", 1) as the name will return "a".
	* @param	mixed			$default	A default value that is returned if the variable was not set.
	* 										This function will always return a value of the same type as the default.
	* @param	bool			$multibyte	If $default is a string this paramater has to be true if the variable may contain any UTF-8 characters
	*										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	* @param	phpbb_request_interface::POST|GET|REQUEST|COOKIE	$super_global
	* 										Specifies which super global should be used
	*
	* @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
	*					the same as that of $default. If the variable is not set $default is returned.
	*/
	public function untrimmed_variable($var_name, $default, $multibyte, $super_global = phpbb_request_interface::REQUEST)
	{
		return $this->_variable($var_name, $default, $multibyte, $super_global, false);
	}

	/**
	* Shortcut method to retrieve SERVER variables.
	*
	* Also fall back to getenv(), some CGI setups may need it (probably not, but
	* whatever).
	*
	* @param	string|array	$var_name		See phpbb_request_interface::variable
	* @param	mixed			$Default		See phpbb_request_interface::variable
	*
	* @return	mixed	The server variable value.
	*/
	public function server($var_name, $default = '')
	{
		$multibyte = true;

		if ($this->is_set($var_name, phpbb_request_interface::SERVER))
		{
			return $this->variable($var_name, $default, $multibyte, phpbb_request_interface::SERVER);
		}
		else
		{
			$var = getenv($var_name);
			$this->type_cast_helper->recursive_set_var($var, $default, $multibyte);
			return $var;
		}
	}

	/**
	* Shortcut method to retrieve the value of client HTTP headers.
	*
	* @param	string|array	$header_name	The name of the header to retrieve.
	* @param	mixed			$default		See phpbb_request_interface::variable
	*
	* @return	mixed	The header value.
	*/
	public function header($header_name, $default = '')
	{
		$var_name = 'HTTP_' . str_replace('-', '_', strtoupper($header_name));
		return $this->server($var_name, $default);
	}

	/**
	* Checks whether a certain variable was sent via POST.
	* To make sure that a request was sent using POST you should call this function
	* on at least one variable.
	*
	* @param	string	$name	The name of the form variable which should have a
	*							_p suffix to indicate the check in the code that creates the form too.
	*
	* @return	bool			True if the variable was set in a POST request, false otherwise.
	*/
	public function is_set_post($name)
	{
		return $this->is_set($name, phpbb_request_interface::POST);
	}

	/**
	* Checks whether a certain variable is set in one of the super global
	* arrays.
	*
	* @param	string	$var	Name of the variable
	* @param	phpbb_request_interface::POST|GET|REQUEST|COOKIE	$super_global
	*							Specifies the super global which shall be checked
	*
	* @return	bool			True if the variable was sent as input
	*/
	public function is_set($var, $super_global = phpbb_request_interface::REQUEST)
	{
		return isset($this->input[$super_global][$var]);
	}

	/**
	* Checks whether the current request is an AJAX request (XMLHttpRequest)
	*
	* @return	bool			True if the current request is an ajax request
	*/
	public function is_ajax()
	{
		return $this->header('X-Requested-With') == 'XMLHttpRequest';
	}

	/**
	* Checks if the current request is happening over HTTPS.
	*
	* @return	bool			True if the request is secure.
	*/
	public function is_secure()
	{
		return $this->server('HTTPS') == 'on';
	}

	/**
	* Returns all variable names for a given super global
	*
	* @param	phpbb_request_interface::POST|GET|REQUEST|COOKIE	$super_global
	*					The super global from which names shall be taken
	*
	* @return	array	All variable names that are set for the super global.
	*					Pay attention when using these, they are unsanitised!
	*/
	public function variable_names($super_global = phpbb_request_interface::REQUEST)
	{
		if (!isset($this->input[$super_global]))
		{
			return array();
		}

		return array_keys($this->input[$super_global]);
	}

	/**
	* Helper function used by variable() and untrimmed_variable().
	*
	* @param	string|array	$var_name	The form variable's name from which data shall be retrieved.
	* 										If the value is an array this may be an array of indizes which will give
	* 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
	* 										then specifying array("var", 1) as the name will return "a".
	* @param	mixed			$default	A default value that is returned if the variable was not set.
	* 										This function will always return a value of the same type as the default.
	* @param	bool			$multibyte	If $default is a string this paramater has to be true if the variable may contain any UTF-8 characters
	*										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	* @param	phpbb_request_interface::POST|GET|REQUEST|COOKIE	$super_global
	* 										Specifies which super global should be used
	* @param	bool			$trim		Indicates whether trim() should be applied to string values.
	*
	* @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
	*					the same as that of $default. If the variable is not set $default is returned.
	*/
	protected function _variable($var_name, $default, $multibyte = false, $super_global = phpbb_request_interface::REQUEST, $trim = true)
	{
		$path = false;

		// deep direct access to multi dimensional arrays
		if (is_array($var_name))
		{
			$path = $var_name;
			// make sure at least the variable name is specified
			if (empty($path))
			{
				return (is_array($default)) ? array() : $default;
			}
			// the variable name is the first element on the path
			$var_name = array_shift($path);
		}

		if (!isset($this->input[$super_global][$var_name]))
		{
			return (is_array($default)) ? array() : $default;
		}
		$var = $this->input[$super_global][$var_name];

		if ($path)
		{
			// walk through the array structure and find the element we are looking for
			foreach ($path as $key)
			{
				if (is_array($var) && isset($var[$key]))
				{
					$var = $var[$key];
				}
				else
				{
					return (is_array($default)) ? array() : $default;
				}
			}
		}

		$this->type_cast_helper->recursive_set_var($var, $default, $multibyte, $trim);

		return $var;
	}
}
