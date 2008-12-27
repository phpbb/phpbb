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
*/
class phpbb_install
{
	public function install() {}
	public function uninstall() {}
}

/**
* Main class handling plugins/hooks
*/
class phpbb_plugins
{
	public $phpbb_required = array();
	public $phpbb_optional = array();

	public $plugin_path = false;
	public $plugins = array();

	private $hooks = array();
	private $current_plugin = false;

	/**
	*
	* @return
	* @param string $plugin_path
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
	*
	* @return
	* @param string $phpbb_name
	*/
	public function add_plugin($phpbb_name)
	{
		if (!file_exists($this->plugin_path . $phpbb_name . '/' . $phpbb_name . '.' . PHP_EXT))
		{
			return false;
		}

		// Include desired plugin
		require_once $this->plugin_path . $phpbb_name . '/' . $phpbb_name . '.' . PHP_EXT;

		// Create new setup for this plugin
		$this->plugins[$phpbb_name] = new phpbb_plugin_structure($phpbb_name);
		$this->current_plugin = $this->plugins[$phpbb_name];

		// Setup plugin
		$this->current_plugin->setup->setup_plugin($this);
	}

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

					$instance = phpbb::get_instance($object->phpbb_plugin);
				}
				else
				{
					$instance = ${$object->object_plugin};

					if (!is_subclass_of($instance, 'phpbb_plugin_support'))
					{
						trigger_error('The Class ' . get_class($instance) . ' defined in ' . get_class($object) . ' is not pluggable.', E_USER_ERROR);
					}
				}

				// Setup/Register plugin...
				$object->setup_plugin($instance);
//				$plugin->objects[$key] = $object;
			}

			// Now setup the functions... this is a special case...
			foreach ($plugin->functions as $params)
			{
				$function = array_shift($params);
				$hook = array_shift($params);
				$mode = (!empty($params)) ? array_shift($params) : phpbb::FUNCTION_INJECT;
				$action = (!empty($params)) ? array_shift($params) : 'default';

				// Check if the function is already overridden.
				if ($mode == phpbb::FUNCTION_OVERRIDE && isset($this->hooks[$function][$mode]))
				{
					trigger_error('Function ' . $function . ' is already overwriten by ' . $this->hooks[$function][$mode] . '.', E_USER_ERROR);
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

			// Call init method?
			if (method_exists($plugin->setup, 'init'))
			{
				$plugin->setup->init();
			}
		}
	}

	public function register_includes()
	{
		$arguments = func_get_args();
		$this->current_plugin->includes = $arguments;
	}

	public function register_plugins()
	{
		$arguments = func_get_args();
		$this->current_plugin->objects = $arguments;
	}

	public function register_function()
	{
		$arguments = func_get_args();
		$this->current_plugin->functions[] = $arguments;
	}

	public function function_override($function)
	{
		return isset($this->hooks[$function][phpbb::FUNCTION_OVERRIDE]);
	}

	public function function_inject($function, $action = 'default')
	{
		return isset($this->hooks[$function][phpbb::FUNCTION_INJECT][$action]);
	}

	public function call_override()
	{
		$arguments = func_get_args();
		$function = array_shift($arguments);

		return call_user_func_array($this->hooks[$function][phpbb::FUNCTION_OVERRIDE], $arguments);
	}

	/**
	* Call injected function.
	*
	* Arguments are layed out in the following way:
	*	action: The action:
	*		'default':	If $action is default, then the hook is called in the beginning, original parameter passed by reference
	*		'return':	If $action is return, then the hook is called at the end and the result will be returned. The hook expects the $result as the first parameter, all other parameters passed by name
	*		If $action is not default and not return it could be a custom string. Please refer to the plugin documentation to determine possible combinations. Parameters are passed by reference.
	*
	* @param string $function Original function name this method is called from
	* @param array $arguments Arguments
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

// Object used to hold plugin information. Per plugin one instance
class phpbb_plugin_structure
{
	public $phpbb_name;
	public $name;
	public $description;
	public $author;
	public $version;

	public $includes = array();
	public $objects = array();
	public $functions = array();

	/**
	*
	* @return
	* @param string $phpbb_name
	*/
	public function __construct($phpbb_name)
	{
		$this->phpbb_name = $phpbb_name;

		$class = 'phpbb_' . $phpbb_name . '_info';
		$this->setup = new $class();

		foreach (array('name', 'description', 'author', 'version') as $required_property)
		{
			$this->$required_property = $this->setup->$required_property;
		}
	}
}

interface phpbb_plugin_info
{
	public function setup_plugin(phpbb_plugins $object);
}

interface phpbb_plugin_setup
{
	function setup_plugin(phpbb_plugin_support $object);
}


abstract class phpbb_plugin_support
{
	private $plugin_methods;
	private $plugin_attributes;

	public function register_method($name, $method, $object, $mode = phpbb::PLUGIN_ADD, $action = 'default')
	{
		// Method reachable by:
		// For plugin_add: plugin_methods[method] = object
		// For plugin_override: plugin_methods[name][mode][method] = object
		// For plugin_inject: plugin_methods[name][mode][action][method] = object

		// Set to PLUGIN_ADD if method does not exist
		if ($name === false || !method_exists($this, $name))
		{
			$mode = phpbb::PLUGIN_ADD;
		}

		// But if it exists and we try to add one, then print out an error
		if ($mode == phpbb::PLUGIN_ADD && (method_exists($this, $method) || isset($this->plugin_methods[$method])))
		{
			trigger_error('Method ' . $method. ' in class ' . get_class($object) . ' is not able to be added, because it conflicts with the existing method ' . $method . ' in ' . get_class($this) . '.', E_USER_ERROR);
		}

		// Check if the same method name is already used for $name for overriding the method.
		if ($mode == phpbb::PLUGIN_OVERRIDE && isset($this->plugin_methods[$name][$mode][$method]))
		{
			trigger_error('Method ' . $method . ' in class ' . get_class($object) . ' is not able to override . ' . $name . ' in ' . get_class($this) . ', because it is already overridden in ' . get_class($this->plugin_methods[$name][$mode][$method]) . '.', E_USER_ERROR);
		}

		// Check if another method is already defined...
		if ($mode == phpbb::PLUGIN_INJECT && isset($this->plugin_methods[$name][$mode][$action][$method]))
		{
			trigger_error('Method ' . $method . ' in class ' . get_class($object) . ' for ' . $name . ' is already defined in class ' . get_class($this->plugin_methods[$name][$mode][$action][$method]), E_USER_ERROR);
		}

		if (($function_signature = $this->valid_parameter($object, $method, $mode, $action)) !== true)
		{
			trigger_error('Method ' . $method . ' in class ' . get_class($object) . ' has invalid function signature. Please use: ' . $function_signature, E_USER_ERROR);
		}

		if ($mode == phpbb::PLUGIN_ADD)
		{
			$this->plugin_methods[$method] = $object;
		}
		else if ($mode == phpbb::PLUGIN_OVERRIDE)
		{
			$this->plugin_methods[$name][$mode][$method] = $object;
		}
		else
		{
			$this->plugin_methods[$name][$mode][$action][$method] = $object;
		}
	}

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

	protected function method_override($name)
	{
		return isset($this->plugin_methods[$name][phpbb::PLUGIN_OVERRIDE]);
	}

	protected function method_inject($name, $action = 'default')
	{
		return isset($this->plugin_methods[$name][phpbb::PLUGIN_INJECT][$action]);
	}

	public function call_override()
	{
		$arguments = func_get_args();
		$name = array_shift($arguments);

		list($method, $object) = each($this->plugin_methods[$name][phpbb::PLUGIN_OVERRIDE]);
		return call_user_func_array(array($object, $method), array_merge(array($this), $arguments));
	}

	/**
	* Call injected method.
	*
	* Arguments are layed out in the following way:
	*	action: The action:
	*		'default':	If $action is default, then the plugin is called in the beginning, original parameter passed by reference
	*		'return':	If $action is return, then the plugin is called at the end and the result will be returned. The plugin expects the $result as the first parameter, all other parameters passed by name
	*		If $action is not default and not return it could be a custom string. Please refer to the plugin documentation to determine possible combinations. Parameters are passed by reference.
	*
	* @param string $name Original method name this method is called from
	* @param array $arguments Arguments
	*/
	public function call_inject($name, $arguments)
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

			foreach ($this->plugin_methods[$name][phpbb::PLUGIN_INJECT][$action] as $method => $object)
			{
				$args = array_merge(array($this, $result), $arguments);
				$result = call_user_func_array(array($object, $method), $args);
			}

			return $result;
		}

		foreach ($this->plugin_methods[$name][phpbb::PLUGIN_INJECT][$action] as $method => $object)
		{
			call_user_func_array(array($object, $method), array_merge(array($this), $arguments));
		}
	}

	// Getter/Setter
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

	public function __call($name, $arguments)
	{
		array_unshift($arguments, $this);
		return call_user_func_array(array($this->plugin_methods[$name], $name), $arguments);
	}

	private function valid_parameter($object, $method, $mode, $action)
	{
		// We cache the results... no worry. These checks are quite resource intensive, but will hopefully educate and guide developers

		// Check for correct first parameter. This must be an instance of phpbb_$phpbb_plugin
		$instance_of = 'phpbb_' . $object->phpbb_plugin;

		// Define the required function layout
		$function_layout = 'public function ' . $method . '(' . $instance_of . ' $object';

		// Result for PLUGIN_INJECT and action == 'return'
		if ($mode == phpbb::PLUGIN_INJECT && $action == 'return')
		{
			$function_layout .= ', $result';
		}

		$function_layout .= ', [...]) { [...] }';

		$reflection = new ReflectionMethod($object, $method);
		$parameters = $reflection->getParameters();
		$first_param = array_shift($parameters);

		// Try to get class
		if (empty($first_param))
		{
			return $function_layout;
		}

		try
		{
			$first_param->getClass()->name;
		}
		catch (Exception $e)
		{
			return $function_layout;
		}

		if ($first_param->getClass()->name !== $instance_of || $first_param->getName() !== 'object')
		{
			return $function_layout;
		}

		if ($mode == phpbb::PLUGIN_INJECT && $action == 'return')
		{
			$first_param = array_shift($parameters);

			if (empty($first_param) || $first_param->getName() !== 'result' || $first_param->isOptional())
			{
				return $function_layout;
			}
		}

		return true;
	}
}

?>