<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\template\twig\node;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


class php extends \Twig_Node
{
	/** @var Twig_Environment */
	protected $environment;

	public function __construct(\Twig_Node_Text $text, \phpbb\template\twig\environment $environment, $lineno, $tag = null)
	{
		$this->environment = $environment;

		parent::__construct(array('text' => $text), array(), $lineno, $tag);
	}

	/**
	 * Compiles the node to PHP.
	 *
	 * @param Twig_Compiler A Twig_Compiler instance
	 */
	public function compile(\Twig_Compiler $compiler)
	{
		$compiler->addDebugInfo($this);

		$config = $this->environment->get_phpbb_config();

		if (!$config['tpl_allow_php'])
		{
			$compiler
				->write("// PHP Disabled\n")
			;

			return;
		}

		$compiler
			->raw($this->getNode('text')->getAttribute('data'))
		;
	}
}
