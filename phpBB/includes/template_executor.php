<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* Template executor interface.
*
* Objects implementing this interface encapsulate a means of executing
* (i.e. rendering) a template.
*
* @package phpBB3
*/
interface phpbb_template_executor
{
	/**
	* Executes the template managed by this executor.
	* @param phpbb_template_context $context Template context to use
	* @param array $lang Language entries to use
	*/
	public function execute($context, $lang);
}
