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

namespace phpbb\legacy\httpkernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 * The legacy kernel
 *
 * This class encapsulates the default HttpKernel, in order to make some
 * private methods accessible, which are needed for bootstrapping the legacy
 * code.
 */
class legacy_http_kernel implements HttpKernelInterface, TerminableInterface
{
	private $kernel;

	public function __construct(HttpKernel $kernel)
	{
		$this->kernel = $kernel;
	}

	/**
	 * @inheritdoc
	 */
	public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
	{
		$request->headers->set('X-Php-Ob-Level', ob_get_level());

		try {
			return $this->handleRaw($request);
		} catch (NotFoundHttpException $e) {
			throw $e;
		} catch (\Exception $e) {
			return $this->handleException($e, $request);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function terminate(Request $request, Response $response)
	{
		return $this->kernel->terminate($request, $response);
	}

	public function filterResponse(Response $response, Request $request)
	{
		return $this->callEmbeddedHttpKernelMethod('filterResponse', $response, $request);
	}

	public function handleException(\Exception $e, Request $request)
	{
		return $this->callEmbeddedHttpKernelMethod('handleException', $e, $request);
	}

	private function handleRaw(Request $request)
	{
		return $this->callEmbeddedHttpKernelMethod('handleRaw', $request);
	}

	/**
	 * Calls a private method of the embedded HttpKernel
	 *
	 * @return mixed
	 */
	private function callEmbeddedHttpKernelMethod()
	{
		$args = func_get_args();
		$method = array_shift($args);
		array_unshift($args, $this->kernel);
		array_push($args, HttpKernelInterface::MASTER_REQUEST);
		$reflObject = new \ReflectionObject($this->kernel);
		$reflMethod = $reflObject->getMethod($method);
		$reflMethod->setAccessible(true);

		return call_user_func_array(array($reflMethod, 'invoke'), $args);
	}
}
