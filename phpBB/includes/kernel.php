<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
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

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* Controller manager class
* @package phpBB3
*/
class phpbb_kernel implements HttpKernelInterface
{
	/**
	* Event Dispatcher object
	* @var EventDispatcherInterface
	*/
	protected $dispatcher;

	/**
	* Controller Resolver object
	* @var ControllerResolverInterface
	*/
	protected $resolver;

	/**
	* User object
	* @var phpbb_user
	*/
	protected $user;

	/**
     * Constructor
     *
     * @param EventDispatcherInterface    $dispatcher An EventDispatcherInterface instance
     * @param ControllerResolverInterface $resolver   A ControllerResolverInterface instance
     */
    public function __construct(EventDispatcherInterface $dispatcher, ControllerResolverInterface $resolver, phpbb_user $user)
    {
        $this->dispatcher = $dispatcher;
        $this->resolver = $resolver;
        $this->user = $user;
    }

	/**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param integer $type    The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param Boolean $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws RuntimeException When an Exception occurs during processing
     */
	public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
	{
		try
		{
			$controller_class = $this->resolver->getController($request);
			$controller = new $controller_class;
			$response = call_user_func(array($controller, 'handle'));

			if (!$response instanceof Response)
			{
				trigger_error($this->user->lang());
			}
		}
		catch (RuntimeException $e)
		{
			if ($catch)
			{
				trigger_error($e->getMessage());
			}

			throw new RuntimeException($e);
		}
	}
}
