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
* Controller helper class, contains methods that do things for controllers
* @package phpBB3
*/
class phpbb_controller_helper
{
	/**
	* Constructor
	*
	* @param phpbb_template
	*/
	public function __construct(phpbb_template $template)
	{
		$this->template = $template;
	}

	/**
	* Return the contents of the rendered file as a string. This will go
	* directly into the Response object.
	*
	* @param string $handle The template handle to render
	* @return string Rendered contents of the template file
	*/
	public function render_template($handle)
	{
		return $this->template->return_display($handle);
	}
}
