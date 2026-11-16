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
			if (is_array($controller) && method_exists(...$controller))
			{
				$reflector = new \ReflectionMethod(...$controller);
			}
			else if (is_string($controller) && str_contains($controller, '::'))
			{
				$reflector = new \ReflectionMethod(...explode('::', $controller, 2));
			}
			else
			{
				$reflector = new \ReflectionFunction($controller(...));
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
			else if ($this->accepts_request($param->getType()))
			{
				$arguments[] = $request;
			}
			else if ($param->isDefaultValueAvailable())
			{
				$arguments[] = $param->getDefaultValue();
			}
			else if ($param->isVariadic())
			{
				break;
			}
			else
			{
				$context = $this->get_controller_context($controller);
				throw new \phpbb\controller\exception('CONTROLLER_ARGUMENT_VALUE_MISSING', [$param->getPosition() + 1, $context, $param->name]);
			}
		}

		return $arguments;
	}

	/**
	 * Checks whether the given reflection type accepts a Request instance.
	 *
	 * @param \ReflectionType|null $type
	 * @return bool
	 */
	protected function accepts_request(\ReflectionType|null $type): bool
	{
		if ($type === null)
		{
			return false;
		}

		if ($type instanceof \ReflectionNamedType)
		{
			$name = $type->getName();
			return $name === Request::class || is_subclass_of($name, Request::class);
		}

		if ($type instanceof \ReflectionUnionType)
		{
			foreach ($type->getTypes() as $sub_type)
			{
				if ($this->accepts_request($sub_type))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Returns the controller context used in missing argument error messages.
	 *
	 * @param callable $controller
	 * @return string
	 */
	protected function get_controller_context(callable $controller): string
	{
		if (is_array($controller))
		{
			[$object, $method] = $controller;
			$class = is_object($object) ? get_class($object) : $object;
			return $class . ':' . $method;
		}

		if (is_object($controller) && !($controller instanceof \Closure))
		{
			return get_class($controller) . ':__invoke';
		}

		if (is_string($controller))
		{
			return str_replace('::', ':', $controller);
		}

		return '';
	}
}
