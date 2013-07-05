<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
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


class phpbb_template_twig_node_includejs extends Twig_Node
{
	/** @var Twig_Environment */
	protected $environment;

	public function __construct(Twig_Node_Expression $expr, phpbb_template_twig_environment $environment, $lineno, $tag = null)
	{
		$this->environment = $environment;

		parent::__construct(array('expr' => $expr), array(), $lineno, $tag);
	}

	/**
	 * Compiles the node to PHP.
	 *
	 * @param Twig_Compiler A Twig_Compiler instance
	 */
	public function compile(Twig_Compiler $compiler)
	{
		$compiler->addDebugInfo($this);

		$config = $this->environment->get_phpbb_config();

		$compiler
			->write("\$js_file = ")
			->subcompile($this->getNode('expr'))
			->raw(";\n")
			->write("if (!file_exists(\$js_file)) {\n")
			->indent()
				->write("\$js_file = \$this->getEnvironment()->getLoader()->getCacheKey(\$js_file);\n")
			->outdent()
			->write("}\n")
			->write("\$context['definition']->append('SCRIPTS', '<script type=\"text/javascript\" src=\"' . ")
			->raw("\$js_file")
			->raw(" . '?assets_version=" . $config['assets_version'] . "\"></script>\n');\n")
		;
	}
}
