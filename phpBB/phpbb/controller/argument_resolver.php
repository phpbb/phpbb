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

namespace phpbb\controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

/**
 * Resolves controller arguments, applying phpBB type-casting to string URL attributes.
 */
class argument_resolver implements ArgumentResolverInterface
{
	/**
	 * Request type cast helper object
	 * @var \phpbb\request\type_cast_helper
	 */
	protected $type_cast_helper;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->type_cast_helper = new \phpbb\request\type_cast_helper();
	}

	/**
	 * {@inheritdoc}
	 *
	 * Returns the arguments to pass to the controller, applying phpBB type-casting
	 * to string URL attribute values for security.
	 *
	 * @param Request $request Symfony Request object
	 * @param callable $controller A callable (controller class, method)
	 * @param \ReflectionFunctionAbstract|null $reflector Pre-built reflector (optional)
	 * @return array An array of arguments to pass to the controller
	 * @throws \phpbb\controller\exception
	 */
	public function getArguments(Request $request, callable $controller, \ReflectionFunctionAbstract|null $reflector = null): array
	{
		if ($reflector === null)
		{
			if (is_array($controller))
			{
				[$object, $method] = $controller;
				$reflector = new \ReflectionMethod($object, $method);
			}
			else if (is_object($controller) && !($controller instanceof \Closure))
			{
				/** @var object $controller */
				$reflector = (new \ReflectionObject($controller))->getMethod('__invoke');
			}
			else
			{
				/** @var \Closure|string $controller */
				$reflector = new \ReflectionFunction($controller);
			}
		}

		$arguments = [];
		$parameters = $reflector->getParameters();
		$attributes = $request->attributes->all();

		foreach ($parameters as $param)
		{
			if (array_key_exists($param->name, $attributes))
			{
				if (is_string($attributes[$param->name]))
				{
					$value = $attributes[$param->name];
					$this->type_cast_helper->set_var($value, $attributes[$param->name], 'string', true, false);
					$arguments[] = $value;
				}
				else
				{
					$arguments[] = $attributes[$param->name];
				}
			}
			else if ($param->getType() !== null && !$param->getType() instanceof \ReflectionNamedType)
			{
				// Union/intersection type - skip request injection
				if ($param->isDefaultValueAvailable())
				{
					$arguments[] = $param->getDefaultValue();
				}
				else
				{
					throw new \phpbb\controller\exception('CONTROLLER_ARGUMENT_VALUE_MISSING', [$param->getPosition() + 1, '', $param->name]);
				}
			}
			else if ($param->getType() instanceof \ReflectionNamedType && $param->getType()->getName() === Request::class)
			{
				$arguments[] = $request;
			}
			else if ($param->isDefaultValueAvailable())
			{
				$arguments[] = $param->getDefaultValue();
			}
			else
			{
				if (is_array($controller))
				{
					[$object, $method] = $controller;
					$context = get_class($object) . ':' . $method;
				}
				else
				{
					$context = '';
				}
				throw new \phpbb\controller\exception('CONTROLLER_ARGUMENT_VALUE_MISSING', [$param->getPosition() + 1, $context, $param->name]);
			}
		}

		return $arguments;
	}
}
