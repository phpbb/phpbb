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
* Template executor that stores path to php file with template code
* and evaluates it by including the file.
*
* @package phpBB3
*/
class phpbb_template_executor_include implements phpbb_template_executor
{
	/**
	* Template path to be included.
	*/
	private $path;

	/**
	* Constructor. Stores path to the template for future inclusion.
	* Template includes are delegated to template object $template.
	*
	* @param string $path path to the template
	*/
	public function __construct($path, $template)
	{
		$this->path = $path;
		$this->template = $template;
	}

	/**
	* Executes the template managed by this executor by including
	* the php file containing the template.
	* @param phpbb_template_context $context Template context to use
	* @param array $lang Language entries to use
	*/
	public function execute($context, $lang)
	{
		$_template = &$this->template;
		$_tpldata = &$context->get_data_ref();
		$_rootref = &$context->get_root_ref();
		$_lang = &$lang;

		include($this->path);
	}
}
