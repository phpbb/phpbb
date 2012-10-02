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

/**
* Controller helper class, contains methods that do things for controllers
* @package phpBB3
*/
class phpbb_controller_helper
{
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
	* @param phpbb_template
	*/
	public function __construct(phpbb_template $template, $phpbb_root_path = "./", $php_ext = ".php")
	{
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
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
			include("{$this->phpbb_root_path}includes/functions{$this->php_ext}");
		}

		page_header($page_title);

		$this->template->set_filenames(array(
			'body'	=> $template_file,
		));

		page_footer(true, false, false);

		return new Response($this->template->return_display($handle), $status_code);
	}
}
