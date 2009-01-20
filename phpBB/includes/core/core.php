<?php
/**
*
* @package core
* @version $Id: core.php 9216 2008-12-23 18:40:33Z acydburn $
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit();
}

/**
* phpBB abstract class
*
* @package core
* @author acydburn
*/
abstract class phpbb
{
	/**
	* The phpBB template object
	*/
	public static $template = NULL;

	/**
	* The phpBB user object
	*/
	public static $user = NULL;

	/**
	* The phpBB database object
	*/
	public static $db = NULL;

	/**
	* The phpBB cache system object
	*/
	public static $acm = NULL;

	/**
	* The phpBB permission object
	*/
	public static $acl = NULL;

	/**
	* The phpBB plugins object
	*/
	public static $plugins = NULL;

	/**
	* The phpBB core url object
	* Responsible for handling URL-related tasks as well as redirects, etc.
	*/
	public static $url = NULL;

	/**
	* The phpBB core security object.
	* Responsible for handling security-related tasks, for example password handling, random number generation...
	*/
	public static $security = NULL;

	/**
	* The phpBB core system object
	* Responsible for handling file/server tasks.
	*/
	public static $system = NULL;

	/**
	* The phpBB API object
	*/
	public static $api = NULL;

	/**
	* @var array The phpBB configuration array
	*/
	public static $config = array();

	/**
	* @var array The base configuration array
	*/
	public static $base_config = array(
		'table_prefix'		=> 'phpbb_',
		'admin_folder'		=> 'adm',
		'acm_type'			=> 'file',

		'config_set'		=> false,
		'extensions_set'	=> false,

		'memory_usage'		=> 0,

		'debug'				=> false,
		'debug_extra'		=> false,
		'installed'			=> false,
	);

	/**#@+
	* Permission constant
	*/
	const ACL_NEVER = 0;
	const ACL_YES = 1;
	const ACL_NO = -1;
	/**#@-*/

	/**#@+
	* Global constant for {@link phpbb::$system->chmod()}
	*/
	const CHMOD_ALL = 7;
	const CHMOD_READ = 4;
	const CHMOD_WRITE = 2;
	const CHMOD_EXECUTE = 1;
	/**#@-*/

	/**#@+
	* Constant defining plugin mode for objects
	*/
	const METHOD_ADD = 1;
	const METHOD_OVERRIDE = 2;
	const METHOD_INJECT = 4;
	/**#@-*/

	/**#@+
	* Constant defining plugin mode for functions
	*/
	const FUNCTION_OVERRIDE = 1;
	const FUNCTION_INJECT = 2;
	/**#@-*/

	/**#@+
	* Constant to define user level. See {@link phpbb::$user phpbb::$user}
	*/
	const USER_NORMAL = 0;
	const USER_INACTIVE = 1;
	const USER_IGNORE = 2;
	const USER_FOUNDER = 3;
	/**#@-*/

	/**
	* @var array a static array holding custom objects
	*/
	public static $instances = NULL;

	/**
	* We do not want this class instantiable
	*/
	private function ___construct() { }

	/**
	* A failover error handler to handle errors before we assign our own error handler
	*
	* @access public
	*/
	public static function error_handler($errno, $errstr, $errfile, $errline)
	{
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}

	/**
	* Set base configuration - called from config.php file
	*/
	public static function set_config($config)
	{
		phpbb::$base_config = array_merge(phpbb::$base_config, $config);
		phpbb::$base_config['config_set'] = true;

		if (phpbb::$base_config['debug_extra'] && function_exists('memory_get_usage'))
		{
			phpbb::$base_config['memory_usage'] = memory_get_usage();
		}

		// Load Extensions
		if (!empty(phpbb::$base_config['extensions']) && !phpbb::$base_config['extensions_set'])
		{
			$load_extensions = explode(',', phpbb::$base_config['extensions']);

			foreach ($load_extensions as $extension)
			{
				@dl(trim($extension));
			}

			phpbb::$base_config['extensions_set'] = true;
		}
	}

	/**
	* Get instance of static property
	*
	* @param string	$variable	The name of the instance to retrieve.
	*
	* @return mixed	The property (object/array/...) registered with this name
	* @access public
	*/
	public static function get_instance($variable)
	{
		if (!self::registered($variable))
		{
			return self::register($variable);
		}

		// Please do not try to change it to (expr) ? (true) : (false) - it will not work. ;)
		if (property_exists('phpbb', $variable))
		{
			return self::$$variable;
		}
		else
		{
			return self::$instances[$variable];
		}
	}

	/**
	* Check if the variable is already assigned
	*
	* @param string	$variable	The name of the instance to check
	*
	* @return bool	True if the instance is registered, false if not.
	* @access public
	*/
	public static function registered($variable)
	{
		if (property_exists('phpbb', $variable))
		{
			return (self::$$variable !== NULL) ? true : false;
		}

		return (isset(self::$instances[$variable]) && self::$instances[$variable] !== NULL) ? true : false;
	}

	/**
	* Simpler method to access assigned instances.
	* (Overloading is not possible here due to the object being static and our use of PHP 5.2.x+.)
	*
	* @param string	$variable	The instance name to retrieve
	*
	* @return mixed	The instance
	* @access public
	*/
	public static function get($variable)
	{
		// No error checking done here... returned right away
		return self::$instances[$variable];
	}

	/**
	* Register new class/object.
	* Any additional parameter will be forwarded to the class instantiation.
	*
	* @param string			$variable		The resulting instance name.
	* 										If a property with the given name exists, it will be assigned.
	* 										Else it will be put in the {@link $instances intances} array
	* @param string			$class			Define a custom class name.
	* 										This is useful if the class used does not abide to the rules (phpbb_{$class}).
	* @param string|array	$includes		Define additional files/includes required for this class to be correctly set up. Files are expected to be in /includes/.
	* @param mixed			$arguments,...	Any number of additional arguments passed to the constructor of the object to create
	*
	* @return mixed	The instance of the created object
	* @access public
	*/
	public static function register($variable, $class = false, $includes = false)
	{
		if (self::registered($variable))
		{
			return self::get_instance($variable);
		}

		$arguments = (func_num_args() > 3) ? array_slice(func_get_args(), 3) : array();
		$class = ($class === false) ? 'phpbb_' . $variable : $class;

		if ($includes !== false)
		{
			if (!is_array($includes))
			{
				$includes = array($includes);
			}

			foreach ($includes as $file)
			{
				require_once PHPBB_ROOT_PATH . 'includes/' . $file . '.' . PHP_EXT;
			}
		}

		$reflection = new ReflectionClass($class);

		if (!$reflection->isInstantiable())
		{
			throw new Exception('Assigned classes need to be instantiated.');
		}

		if (!property_exists('phpbb', $variable))
		{
			self::$instances[$variable] = (sizeof($arguments)) ? call_user_func_array(array($reflection, 'newInstance'), $arguments) : $reflection->newInstance();
		}
		else
		{
			self::$$variable = (sizeof($arguments)) ? call_user_func_array(array($reflection, 'newInstance'), $arguments) : $reflection->newInstance();
		}

		return self::get_instance($variable);
	}

	/**
	* Instead of registering we also can assign a variable. This is helpful if we have an application builder or use a factory.
	*
	* @param string	$variable	The resulting instance name.
	* 							If a property with the given name exists, it will be assigned.
	* 							Else it will be put in the {@link $instances intances} array
	* @param mixed	$object		The variable to assign to the instance
	*
	* @return mixed	The instance
	* @access public
	*/
	public static function assign($variable, $object)
	{
		if (self::registered($variable))
		{
			return self::get_instance($variable);
		}

		if (!property_exists('phpbb', $variable))
		{
			self::$instances[$variable] = $object;
		}
		else
		{
			self::$$variable = $object;
		}

		return self::get_instance($variable);
	}

	/**
	* Unset/unregister a specific object.
	*
	* @param string	$variable	The name of the instance to unset
	* @access public
	*/
	public static function unregister($variable)
	{
		if (!self::registered($variable))
		{
			return;
		}

		if (!property_exists('phpbb', $variable))
		{
			unset(self::$instances[$variable]);
		}
		else
		{
			self::$$variable = NULL;
		}
	}

	/**
	* Function to return to a clean state, unregistering everything. This is helpful for unit tests if you want to return to a "clean state"
	*
	* @access public
	*/
	public static function reset()
	{
		$class_vars = array_keys(get_class_vars('phpbb'));
		$class_vars = array_merge(array_keys(self::$instances), $class_vars);

		foreach ($class_vars as $variable)
		{
			self::unregister($variable);
		}
	}
}

/**
* phpBB SPL Autoload Function. A phpbb_ prefix will be stripped from the class name.
*
* The files this function tries to include are:
*	includes/{$class_name}/bootstrap.php
*	includes/{$class_name}/index.php
* Additionally, every _ within $class_name is replaced by / for the following directories:
*	includes/{$class_name}.php
*	includes/classes/{$class_name}.php
*
* @param string	$class_name	The class name. An existing phpbb_ prefix will be removed.
*/
function __phpbb_autoload($class_name)
{
	if (strpos($class_name, 'phpbb_') === 0)
	{
		$class_name = substr($class_name, 6);
	}

	$class_name = basename($class_name);

	$filenames = array(
		'includes/' . $class_name . '/bootstrap',
		'includes/' . $class_name . '/index',
		'includes/' . $class_name,
		'includes/classes/' . $class_name,
	);

	if (strpos($class_name, '_') !== false)
	{
		$class_name = str_replace('_', '/', $class_name);

		$filenames = array_merge($filenames, array(
			'includes/' . $class_name,
			'includes/classes/' . $class_name,
		));
	}

	foreach ($filenames as $filename)
	{
		if (file_exists(PHPBB_ROOT_PATH . $filename . '.' . PHP_EXT))
		{
			include PHPBB_ROOT_PATH . $filename . '.' . PHP_EXT;
			return;
		}
	}
}

/*
class phpbb_exception extends Exception
{
}
*/
?>