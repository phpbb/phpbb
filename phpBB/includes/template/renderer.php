<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
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
* Template renderer interface.
*
* Objects implementing this interface encapsulate a means of displaying
* a template.
*
* @package phpBB3
*/
interface phpbb_template_renderer
{
	/**
	* Displays the template managed by this renderer.
	*
	* @param phpbb_template_context $context Template context to use
	* @param array $lang Language entries to use
	*/
	public function render($context, $lang);
}
