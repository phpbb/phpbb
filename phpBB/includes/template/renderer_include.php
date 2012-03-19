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
* Template renderer that stores path to php file with template code
* and displays it by including the file.
*
* @package phpBB3
*/
class phpbb_template_renderer_include implements phpbb_template_renderer
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
	* Displays the template managed by this renderer by including
	* the php file containing the template.
	*
	* @param phpbb_template_context $context Template context to use
	* @param array $lang Language entries to use
	*/
	public function render($context, $lang)
	{
		$_template = $this->template;
		$_tpldata = &$context->get_data_ref();
		$_rootref = &$context->get_root_ref();
		$_lang = $lang;

		include($this->path);
	}
}
