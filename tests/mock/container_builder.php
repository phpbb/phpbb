<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ScopeInterface;

class phpbb_mock_container_builder implements ContainerInterface
{
	protected $services = array();
	protected $parameters = array();

	/**
	* Sets a service.
	*
	* @param string $id      The service identifier
	* @param object $service The service instance
	* @param string $scope   The scope of the service
	*
	* @api
	*/
	public function set($id, $service, $scope = self::SCOPE_CONTAINER)
	{
		$this->services[$id] = $service;
	}

	/**
	* Gets a service.
	*
	* @param string $id              The service identifier
	* @param int    $invalidBehavior The behavior when the service does not exist
	*
	* @return object The associated service
	*
	* @throws InvalidArgumentException if the service is not defined
	* @throws ServiceCircularReferenceException When a circular reference is detected
	* @throws ServiceNotFoundException When the service is not defined
	*
	* @see Reference
	*
	* @api
	*/
	public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
	{
		if ($this->has($id))
		{
			$service = $this->services[$id];
			if (is_array($service) && is_callable($service[0]))
			{
				return call_user_func_array($service[0], $service[1]);
			}
			else
			{
				return $service;
			}
		}

		throw new Exception('Could not find service: ' . $id);
	}

	/**
	* Returns true if the given service is defined.
	*
	* @param string $id The service identifier
	*
	* @return Boolean true if the service is defined, false otherwise
	*
	* @api
	*/
	public function has($id)
	{
		return isset($this->services[$id]);
	}

	/**
	* Gets a parameter.
	*
	* @param string $name The parameter name
	*
	* @return mixed  The parameter value
	*
	* @throws InvalidArgumentException if the parameter is not defined
	*
	* @api
	*/
	public function getParameter($name)
	{
		if ($this->hasParameter($name))
		{
			return $this->parameters[$name];
		}

		throw new Exception('Could not find parameter: ' . $name);
	}

	/**
	* Checks if a parameter exists.
	*
	* @param string $name The parameter name
	*
	* @return Boolean The presence of parameter in container
	*
	* @api
	*/
	public function hasParameter($name)
	{
		return isset($this->parameters[$name]);
	}

	/**
	* Sets a parameter.
	*
	* @param string $name  The parameter name
	* @param mixed  $value The parameter value
	*
	* @api
	*/
	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	/**
	* Enters the given scope
	*
	* @param string $name
	*
	* @api
	*/
	public function enterScope($name)
	{
	}

	/**
	* Leaves the current scope, and re-enters the parent scope
	*
	* @param string $name
	*
	* @api
	*/
	public function leaveScope($name)
	{
	}

	/**
	* Adds a scope to the container
	*
	* @param ScopeInterface $scope
	*
	* @api
	*/
	public function addScope(ScopeInterface $scope)
	{
	}

	/**
	* Whether this container has the given scope
	*
	* @param string $name
	*
	* @return Boolean
	*
	* @api
	*/
	public function hasScope($name)
	{
	}

	/**
	* Determines whether the given scope is currently active.
	*
	* It does however not check if the scope actually exists.
	*
	* @param string $name
	*
	* @return Boolean
	*
	* @api
	*/
	public function isScopeActive($name)
	{
	}

	public function isFrozen()
	{
		return false;
	}
}
