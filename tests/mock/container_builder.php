<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ScopeInterface;

class phpbb_mock_container_builder implements ContainerInterface
{
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
}
