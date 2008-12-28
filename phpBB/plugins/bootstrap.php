<?php
/**
*
* @package plugins
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* Class used by plugins/modules/etc. for installation/uninstallation
* @package plugins
*/
class phpbb_install
{
	public function install() {}
	public function uninstall() {}
}

/**
* This class holds plugin information.
* One instance will be assigned to each plugin.
* @package plugins
*/
class phpbb_plugin_structure
{
	/**
	* @var string The plugins plain phpBB name. This is also the directory name.
	*/
	public $phpbb_name;

	/**
	* @var string The plugins full name as the plugin defines it
	*/
	public $name;

	/**
	* @var string The plugins full description as the plugin defines it
	*/
	public $description;

	/**
	* @var string The plugins author
	*/
	public $author;

	/**
	* @var string The plugins version string
	*/
	public $version;

	/**
	* @var array The plugins defined includes
	*/
	public $includes = array();

	/**
	* @var array The plugins defined objects to plug in
	*/
	public $objects = array();

	/**
	* @var array The plugins defined hooks/functions to plug in
	*/
	public $functions = array();

	/**
	* Set up information object
	*
	* @param string	$phpbb_name	The plugins directory/simple/plain name
	* @access public
	*/
	public function __construct($phpbb_name)
	{
		$this->phpbb_name = $phpbb_name;

		$class = 'phpbb_' . $phpbb_name . '_info';

		if (!class_exists($class))
		{
			trigger_error('Plugin ' . $phpbb_name . ' does not define required ' . $class . ' info class.', E_USER_ERROR);
		}

		$this->setup = new $class();

		foreach (array('name', 'description', 'author', 'version') as $required_property)
		{
			if (!property_exists($this->setup, $required_property))
			{
				trigger_error('Plugin ' . $phpbb_name . ' does not define required property ' . $required_property . ' in info class ' . $class . '.', E_USER_ERROR);
			}

			$this->$required_property = $this->setup->$required_property;
		}
	}
}

/**
* Main class handling plugins/hooks
* @package plugins
*/
class phpbb_plugins
{
	/**
	* @var array required phpBB objects
	*/
	public $phpbb_required = array();

	/**
	* @var array Optional phpBB objects
	*/
	public $phpbb_optional = array();

	/**
	* @var string Plugin path plugins are searched at.
	*/
	public $plugin_path = false;

	/**
	* @var array Collection of assigned plugins
	*/
	private $plugins = array();

	/**
	* @var array Collection of assigned hooks (functions)
	*/
	private $hooks = array();

	/**
	* Pointer to currently processed plugin.
	*/
	private $current_plugin = false;

	/**
	* Init plugins
	*
	* Search {@link $plugin_path plugin path} for plugins and include the information for later processing.
	* Plugins will not be called here, just collected.
	*
	* @param string	$plugin_path	The plugin path to search.
	* @access public
	*/
	public function init($plugin_path)
	{
		$this->plugin_path = $plugin_path;
		$this->plugins = array();

		// search for plugin files
		if ($dh = @opendir($this->plugin_path))
		{
			while (($file = readdir($dh)) !== false)
			{
				// If is directory and a PHP file with the same name as the directory within this dir?
				if ($file[0] != '.' && is_readable($this->plugin_path . $file) && is_dir($this->plugin_path . $file) && file_exists($this->plugin_path . $file . '/' . $file . '.' . PHP_EXT))
				{
					$this->add_plugin($file);
				}
			}
			closedir($dh);
		}
	}

	/**
	* Add information about specific plugin
	*
	* @param string	$name	Plugin directory name
	* @return bool	False if plugin does not exist
	* @access public
	*/
	public function add_plugin($name)
	{
		if (!file_exists($this->plugin_path . $name . '/' . $name . '.' . PHP_EXT))
		{
			return false;
		}

		// Include desired plugin
		require_once $this->plugin_path . $name . '/' . $name . '.' . PHP_EXT;

		// Create new setup for this plugin
		$this->plugins[$name] = new phpbb_plugin_structure($name);
		$this->current_plugin = $this->plugins[$name];

		// Setup plugin
		$this->current_plugin->setup->setup_plugin($this);

		return true;
	}

	/**
	* Setup all previously collected plugins
	*
	* @return bool	False if there are no plugins to setup
	* @access public
	*/
	public function setup()
	{
		if (empty($this->plugins))
		{
			return false;
		}

		foreach ($this->plugins as $name => $plugin)
		{
			// Add includes
			foreach ($plugin->includes as $file)
			{
				include_once $this->plugin_path . $name . '/' . $file . '.' . PHP_EXT;
			}

			// Setup objects
			foreach ($plugin->objects as $key => $class)
			{
				$object = new $class();

				if (!property_exists($object, 'phpbb_plugin') && !property_exists($object, 'class_plugin'))
				{
					trigger_error('Class ' . get_class($object) . ' does not define public $phpbb_plugin and public $class_plugin.', E_USER_ERROR);
				}

				if (property_exists($object, 'phpbb_plugin') && !empty($object->phpbb_plugin))
				{
					// Is the plugin the mod author wants to influence pluggable?
					if (!is_subclass_of(phpbb::get_instance($object->phpbb_plugin), 'phpbb_plugin_support'))
					{
						trigger_error('The phpBB Class ' . get_class(phpbb::get_instance($object->phpbb_plugin)) . ' defined in ' . get_class($object) . ' is not pluggable.', E_USER_ERROR);
					}

					// Get instance of phpBB Object to pass later
					$instance = phpbb::get_instance($object->phpbb_plugin);
				}
				else
				{
					// Assign custom object...
					$instance = ${$object->class_plugin};

					if (!is_subclass_of($instance, 'phpbb_plugin_support'))
					{
						trigger_error('The Class ' . get_class($instance) . ' defined in ' . get_class($object) . ' is not pluggable.', E_USER_ERROR);
					}
				}

				// Setup/Register plugin...
				$object->setup_plugin($instance);
			}

			// Now setup hooks... this is a special case...
			foreach ($plugin->functions as $params)
			{
				$function = array_shift($params);
				$hook = array_shift($params);
				$mode = (!empty($params)) ? array_shift($params) : phpbb::FUNCTION_INJECT;
				$action = (!empty($params)) ? array_shift($params) : 'default';

				// Check if the function is already overridden.
				if ($mode == phpbb::FUNCTION_OVERRIDE && isset($this->hooks[$function][$mode]))
				{
					trigger_error('Function ' . $function . ' is already overridden by ' . $this->hooks[$function][$mode] . '.', E_USER_ERROR);
				}

				if ($mode == phpbb::FUNCTION_OVERRIDE)
				{
					$this->hooks[$function][$mode] = $hook;
				}
				else
				{
					$this->hooks[$function][$mode][$action][] = $hook;
				}
			}

			// Call plugins init method?
			if (method_exists($plugin->setup, 'init'))
			{
				$plugin->setup->init();
			}
		}
	}

	/**
	* Register files to be included on plugin setup
	* Used by plugin setup
	*
	* @param string	$arguments,...	List of files to include
	* @access public
	*/
	public function register_includes()
	{
		$arguments = func_get_args();
		$this->current_plugin->includes = $arguments;
	}

	/**
	* Define the plugins classes registered within the setup process.
	* Used by plugin setup.
	*
	* @param string	$arguments,...	List of classes to instantiate
	* @access public
	*/
	public function register_plugins()
	{
		$arguments = func_get_args();

		// Make sure the class names are unique by checking for the phpbb name
		foreach ($arguments as $class)
		{
			if (strpos($class, '_' . $this->current_plugin->phpbb_name . '_') === false)
			{
				trigger_error('Class ' . $class . ' has an invalid name in plugin ' . $this->current_plugin->phpbb_name . '. The class must include the name of the plugin.', E_USER_ERROR);
			}
		}

		$this->current_plugin->objects = $arguments;
	}

	/**
	* Define the hook setup for functions.
	* Used by plugin setup.
	*
	* @param string	$function	The function name to hook into
	* @param string	$hook		The hooks function name
	* @param phpbb::FUNCTION_OVERRIDE|phpbb::FUNCTION_INJECT	$mode
	* 							If set to phpbb::FUNCTION_OVERRIDE, the hook is called instead of the function and returns the result.
	* 							If set to phpbb::FUNCTION_INJECT (default), then the hook is called based on the $action parameter
	* @param string	$action		Defines the hooks action. Default parameters are 'default' and 'return'.
	* 							Other actions are defined within the relevant functions and are documented within the plugins documentation.
	*							'default':	This is the default action used for the first hook called, usually at the start of the function. Parameter are passed by reference.
	*							'return':	This is the action used to specify the hook used at the end of the function. Result is passed, returns the result.
	*
	* @access public
	*/
	public function register_function()
	{
		$arguments = func_get_args();
		$this->current_plugin->functions[] = $arguments;
	}

	/**
	* Checks if a specific function is overridden by a hook
	* Called within functions to check if they are overridden.
	*
	* @param string	$function	The functions name, usually passed as __FUNCTION__
	*
	* @return bool	True if the function is overridden, false if not
	* @access public
	*/
	public function function_override($function)
	{
		return isset($this->hooks[$function][phpbb::FUNCTION_OVERRIDE]);
	}

	/**
	* Checks if a specific function is injected by a hook
	* Called within functions to check if they are injected.
	*
	* @param string	$function	The functions name, usually passed as __FUNCTION__
	* @param string	$action		The action to check. Can be 'default', 'return' or any other action defined.
	*
	* @return bool	True if the function is injected for the particular action, false if not
	* @access public
	*/
	public function function_inject($function, $action = 'default')
	{
		return isset($this->hooks[$function][phpbb::FUNCTION_INJECT][$action]);
	}

	/**
	* Call hook function overriding function.
	* This is called within a function to actually override the function with the hook.
	*
	* @param string	$function		The function name, usually passed as __FUNCTION__
	* @param mixed	$arguments,...	Optional number of arguments passed by the function to the hook
	*
	* @return mixed	The hooks result
	* @access public
	*/
	public function call_override()
	{
		$arguments = func_get_args();
		$function = array_shift($arguments);

		return call_user_func_array($this->hooks[$function][phpbb::FUNCTION_OVERRIDE], $arguments);
	}

	/**
	* Call injected function.
	* This is called within functions to call hooks for specific actions.
	*
	* @param string	$function		The function name, usually passed as __FUNCTION__
	* @param array	$arguments		Arguments passed to the hook as an array
	* 								The first parameter is the action.
	* 								If the action is 'return', the second parameter is the result
	* 								The remaining parameter are the functions parameter, passed by reference if action is not 'return'.
	*
	* @return mixed	Returns result if action is 'return'
	* @access public
	*/
	public function call_inject($function, $arguments)
	{
		$result = NULL;

		if (!is_array($arguments))
		{
			$action = $arguments;
			$arguments = array();
		}
		else
		{
			$action = array_shift($arguments);
		}

		// Return action... handle like override
		if ($action == 'return')
		{
			$result = array_shift($arguments);

			foreach ($this->hooks[$function][phpbb::FUNCTION_INJECT][$action] as $key => $hook)
			{
				$args = array_merge(array($result), $arguments);
				$result = call_user_func_array($hook, $args);
			}

			return $result;
		}

		foreach ($this->hooks[$function][phpbb::FUNCTION_INJECT][$action] as $key => $hook)
		{
			call_user_func_array($hook, $arguments);
		}
	}
}

/**
* Plugin support class.
* Objects supporting plugins must extend this class
* @package plugins
*/
abstract class phpbb_plugin_support
{
	/**
	* @var array Methods injected
	*/
	private $plugin_methods;

	/**
	* @var array Attributes injected
	*/
	private $plugin_attributes;

	/**
	* Register a new method, overrides one or inject into one.
	*
	* @param string	$name		The method name to inject into. False if a new method is registered.
	* @param string	$method		The hook name to use.
	* @param object $object		Always $this
	* @param phpbb::METHOD_ADD|phpbb::METHOD_OVERRIDE|phpbb::METHOD_INJECT	$mode
	*							If set to phpbb::METHOD_ADD (default) the $name is added as a new method for the object plugged into.
	* 							If set to phpbb::METHOD_OVERRIDE the hook is called instead of the method and returns the result.
	* 							If set to phpbb::METHOD_INJECT, then the hook is called based on the $action parameter
	* @param string	$action		Defines the hooks action. Default parameters are 'default' and 'return'.
	* 							Other actions are defined within the relevant methods and are documented within the plugins documentation.
	*							'default':	This is the default action used for the first hook called, usually at the start of the method. Parameter are passed by reference.
	*							'return':	This is the action used to specify the hook used at the end of the method. Result is passed, returns the result.
	*
	* @access public
	*/
	public function register_method($name, $method, $object, $mode = phpbb::METHOD_ADD, $action = 'default')
	{
		// Method reachable by:
		// For plugin_add: plugin_methods[method] = object
		// For plugin_override: plugin_methods[name][mode][method] = object
		// For plugin_inject: plugin_methods[name][mode][action][method] = object

		// Set to PLUGIN_ADD if method does not exist
		if ($name === false || !method_exists($this, $name))
		{
			$mode = phpbb::METHOD_ADD;
		}

		// But if it exists and we try to add one, then print out an error
		if ($mode == phpbb::METHOD_ADD && (method_exists($this, $method) || isset($this->plugin_methods[$method])))
		{
			trigger_error('Method ' . $method. ' in class ' . get_class($object) . ' is not able to be added, because it conflicts with the existing method ' . $method . ' in ' . get_class($this) . '.', E_USER_ERROR);
		}

		// Check if the same method name is already used for $name for overriding the method.
		if ($mode == phpbb::METHOD_OVERRIDE && isset($this->plugin_methods[$name][$mode][$method]))
		{
			trigger_error('Method ' . $method . ' in class ' . get_class($object) . ' is not able to override . ' . $name . ' in ' . get_class($this) . ', because it is already overridden in ' . get_class($this->plugin_methods[$name][$mode][$method]) . '.', E_USER_ERROR);
		}

		// Check if another method is already defined...
		if ($mode == phpbb::METHOD_INJECT && isset($this->plugin_methods[$name][$mode][$action][$method]))
		{
			trigger_error('Method ' . $method . ' in class ' . get_class($object) . ' for ' . $name . ' is already defined in class ' . get_class($this->plugin_methods[$name][$mode][$action][$method]), E_USER_ERROR);
		}

		if (($function_signature = $this->valid_parameter($object, $method, $mode, $action)) !== true)
		{
			trigger_error('Method ' . $method . ' in class ' . get_class($object) . ' has invalid function signature. Please use: ' . $function_signature, E_USER_ERROR);
		}

		if ($mode == phpbb::METHOD_ADD)
		{
			$this->plugin_methods[$method] = $object;
		}
		else if ($mode == phpbb::METHOD_OVERRIDE)
		{
			$this->plugin_methods[$name][$mode][$method] = $object;
		}
		else
		{
			$this->plugin_methods[$name][$mode][$action][$method] = $object;
		}
	}

	/**
	* Register a new attribute.
	* If the attribute already exists within the object then it will be overwritten.
	*
	* @param string	$name	Attribute name to register.
	* @param object $object	Always $this
	*
	* @access public
	*/
	public function register_attribute($name, $object)
	{
		if (property_exists($this, $name))
		{
			unset($this->$name);
		}

		if (isset($this->plugin_attributes[$name]))
		{
			trigger_error('Attribute ' . $name . ' in class ' . get_class($object) . ' already defined in class ' . get_class($this->plugin_attributes[$name]), E_USER_ERROR);
		}

		$this->plugin_attributes[$name] = $object;
	}

	/**#@+
	* Magic method for attributes. See {@link register_attribute() register_attribute}.
	* @access public
	*/
	public function __get($name)
	{
		return $this->plugin_attributes[$name]->$name;
	}

	public function __set($name, $value)
	{
		return $this->plugin_attributes[$name]->$name = $value;
	}

	public function __isset($name)
	{
		return isset($this->plugin_attributes[$name]->$name);
	}

	public function __unset($name)
	{
		unset($this->plugin_attributes[$name]->$name);
	}
	/**#@-*/

	/**
	* Call added method. See {@link register_method() register_method} with phpbb::METHOD_ADD mode.
	* @access public
	*/
	public function __call($name, $arguments)
	{
		array_unshift($arguments, $this);
		return call_user_func_array(array($this->plugin_methods[$name], $name), $arguments);
	}

	/**
	* Checks if a specific method is overridden by a hook
	* Called within methods to check if they are overridden.
	*
	* @param string	$name	The methods name, usually passed as __FUNCTION__
	*
	* @return bool	True if the method is overridden, false if not
	* @access protected
	*/
	protected function method_override($name)
	{
		return isset($this->plugin_methods[$name][phpbb::METHOD_OVERRIDE]);
	}

	/**
	* Checks if a specific method is injected by a hook
	* Called within methods to check if they are injected.
	*
	* @param string	$name		The methods name, usually passed as __FUNCTION__
	* @param string	$action		The action to check. Can be 'default', 'return' or any other action defined.
	*
	* @return bool	True if the method is injected for the particular action, false if not
	* @access protected
	*/
	protected function method_inject($name, $action = 'default')
	{
		return isset($this->plugin_methods[$name][phpbb::METHOD_INJECT][$action]);
	}

	/**
	* Call hook method overriding method.
	* This is called within a method to actually override the method with the hook.
	*
	* @param string	$name			The method name, usually passed as __FUNCTION__
	* @param mixed	$arguments,...	Optional number of arguments passed by the method to the hook
	*
	* @return mixed	The hooks result
	* @access protected
	*/
	protected function call_override()
	{
		$arguments = func_get_args();
		$name = array_shift($arguments);

		list($method, $object) = each($this->plugin_methods[$name][phpbb::METHOD_OVERRIDE]);
		return call_user_func_array(array($object, $method), array_merge(array($this), $arguments));
	}

	/**
	* Call injected method.
	* This is called within methods to call hooks for specific actions.
	*
	* @param string	$name			The method name, usually passed as __FUNCTION__
	* @param array	$arguments		Arguments passed to the hook as an array
	* 								The first parameter is the action.
	* 								If the action is 'return', the second parameter is the result
	* 								The remaining parameter are the methods parameter, passed by reference if action is not 'return'.
	*
	* @return mixed	Returns result if action is 'return'
	* @access protected
	*/
	protected function call_inject($name, $arguments)
	{
		$result = NULL;

		if (!is_array($arguments))
		{
			$action = $arguments;
			$arguments = array();
		}
		else
		{
			$action = array_shift($arguments);
		}

		// Return action... handle like override
		if ($action == 'return')
		{
			$result = array_shift($arguments);

			foreach ($this->plugin_methods[$name][phpbb::METHOD_INJECT][$action] as $method => $object)
			{
				$args = array_merge(array($this, $result), $arguments);
				$result = call_user_func_array(array($object, $method), $args);
			}

			return $result;
		}

		foreach ($this->plugin_methods[$name][phpbb::METHOD_INJECT][$action] as $method => $object)
		{
			call_user_func_array(array($object, $method), array_merge(array($this), $arguments));
		}
	}

	/**
	* Check function signature for passed methods in {@link register_method() register_method()}.
	*
	* @param object	$object		The plugin
	* @param string	$method		The method name
	* @param phpbb::METHOD_ADD|phpbb::METHOD_OVERRIDE|phpbb::METHOD_INJECT	$mode	The mode
	* @param string	$action		The action
	*
	* @return mixed	True if the signature is valid, else the correct function signature
	* @access private
	*/
	private function valid_parameter($object, $method, $mode, $action)
	{
		// We cache the results... no worry. These checks are quite resource intensive, but will hopefully educate and guide developers

		// Check for correct first parameter. This must be an instance of phpbb_$phpbb_plugin
		$instance_of = 'phpbb_' . $object->phpbb_plugin;

		// Define the required function layout
		$function_layout = 'public function ' . $method . '(' . $instance_of . ' $object';

		// Result for METHOD_INJECT and action == 'return'
		if ($mode == phpbb::METHOD_INJECT && $action == 'return')
		{
			$function_layout .= ', $result';
		}

		// Optional method parameter
		$function_layout .= ', [...]) { [...] }';

		// Now check the method
		$reflection = new ReflectionMethod($object, $method);
		$parameters = $reflection->getParameters();

		// First parameter needs to be defined
		$first_param = array_shift($parameters);

		// Signature is wrong if first parameter is empty
		if (empty($first_param))
		{
			return $function_layout;
		}

		// Try to get class from first parameter
		try
		{
			$first_param->getClass()->name;
		}
		catch (Exception $e)
		{
			return $function_layout;
		}

		// First parameter needs to be an instance of phpbb_$phpbb_plugin and parameter must be $object
		if ($first_param->getClass()->name !== $instance_of || $first_param->getName() !== 'object')
		{
			return $function_layout;
		}

		// If the action is 'return' we also check for an existing $result parameter
		if ($mode == phpbb::METHOD_INJECT && $action == 'return')
		{
			$first_param = array_shift($parameters);

			// If no result is passed, or the name not $result or the $result being optional, then the signature is wrong
			if (empty($first_param) || $first_param->getName() !== 'result' || $first_param->isOptional())
			{
				return $function_layout;
			}
		}

		// Everything ok
		return true;
	}
}

/**
* Interface for phpBB plugin info
*/
interface phpbb_plugin_info
{
	/**
	* Setup plugin information
	*
	* @param phpbb_plugins $object	The {@link phpbb_plugins plugins object} passed
	*/
	public function setup_plugin(phpbb_plugins $object);
}

/**
* Interface for phpBB plugin setup
*/
interface phpbb_plugin_setup
{
	/**
	* Setup specific class within the plugin
	*
	* @param phpbb_plugin_support $object	The injected object... supports {@link phpbb_plugin_support plugins}
	*/
	function setup_plugin(phpbb_plugin_support $object);
}

?>