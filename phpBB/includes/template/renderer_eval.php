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
* Template renderer that stores compiled template's php code and
* displays it via eval.
*
* @package phpBB3
*/
class phpbb_template_renderer_eval implements phpbb_template_renderer
{
	/**
	* Template code to be eval'ed.
	*/
	private $code;

	/**
	* Constructor. Stores provided code for future evaluation.
	* Template includes are delegated to template object $template.
	*
	* @param string $code php code of the template
	* @param phpbb_template $template template object
	*/
	public function __construct($code, $template)
	{
		$this->code = $code;
		$this->template = $template;
	}

	/**
	* Displays the template managed by this renderer by eval'ing php code
	* of the template.
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

		eval(' ?>' . $this->code . '<?php ');
	}
}
