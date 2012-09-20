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

/**
* Controller base class
* @package phpBB3
*/
abstract class phpbb_controller_base implements phpbb_controller_interface
{
	/**
	* Handle the loading of the controller page.
	*
	* @return Symfony\Component\HttpFoundation\Response Symfony Response
	*/
	abstract public function handle();

	/**
	* Return the contents of the rendered file as a string. This will go
	* directly into the Response object.
	*
	* @param string $handle The template handle to render
	* @return string Rendered contents of the template file.
	*/
	public function render_template($handle)
	{
		if (!isset($this->template))
		{
			throw new RuntimeException();
		}
		return $this->template->return_display($handle);
	}

}
