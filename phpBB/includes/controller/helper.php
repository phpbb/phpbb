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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
* Controller helper class, contains methods that do things for controllers
* @package phpBB3
*/
class phpbb_controller_helper
{
	/**
	* Container
	* @var ContainerBuilder
	*/
	protected $container;

	/**
	* Template object
	* @var phpbb_template
	*/
	protected $template;

	/**
	* phpBB Root Path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* PHP Extension
	* @var string
	*/
	protected $php_ext;

	/**
	* Constructor
	*
	* @param ContainerBuilder $container DI Container
	*/
	public function __construct(ContainerBuilder $container)
	{
		$this->container = $container;

		$this->template = $this->container->get('template');
		$this->phpbb_root_path = $this->container->getParameter('core.root_path');
		$this->php_ext = $this->container->getParameter('core.php_ext');
	}

	/**
	* Automate setting up the page and creating the response object.
	*
	* @param string $handle The template handle to render
	* @param string $page_title The title of the page to output
	* @param int $status_code The status code to be sent to the page header
	* @return Response object containing rendered page
	*/
	public function render($template_file, $page_title = '', $status_code = 200)
	{
		if (!function_exists('page_header'))
		{
			include("{$this->phpbb_root_path}includes/functions.{$this->php_ext}");
		}

		page_header($page_title);

		$this->template->set_filenames(array(
			'body'	=> $template_file,
		));

		page_footer(true, false, false);

		return new Response($this->template->return_display('body'), $status_code);
	}

	/**
	* Output an error
	*
	* @param string $controller Destination controller name
	* @param array $query Request query string parameters
	* @param array $attributes Request attributes
	* @return Response A Reponse instance
	*/
	public function error($message, $type = 500)
	{
		return $this->forward('controller.error:error_' . $type, array(
			'message'	=> $message,
			'title'		=> $this->container->get('user')->lang('INFORMATION'),
		));
	}

	/**
	* Forward a request to another controller
	*
	* @param string $controller Destination controller name
	* @param array $attributes Request attributes
	* @param array $query Request query string parameters
	* @return Response A Reponse instance
	*/
	public function forward($controller, array $attributes = array(), array $query = array())
	{
		return $this->container->get('kernel')->forward($controller, $attributes, $query);
	}
}
