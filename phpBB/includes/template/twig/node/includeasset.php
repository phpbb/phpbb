<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

abstract class phpbb_template_twig_node_includeasset extends Twig_Node
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
			->write("\$asset_file = ")
			->subcompile($this->getNode('expr'))
			->raw(";\n")
			->write("\$argument_string = \$anchor_string = '';\n")
			->write("if ((\$argument_string_start = strpos(\$asset_file, '?')) !== false) {\n")
			->indent()
				->write("\$argument_string = substr(\$asset_file, \$argument_string_start);\n")
				->write("\$asset_file = substr(\$asset_file, 0, \$argument_string_start);\n")
				->write("if ((\$anchor_string_start = strpos(\$argument_string, '#')) !== false) {\n")
				->indent()
					->write("\$anchor_string = substr(\$argument_string, \$anchor_string_start);\n")
					->write("\$argument_string = substr(\$argument_string, 0, \$anchor_string_start);\n")
				->outdent()
				->write("}\n")
			->outdent()
			->write("}\n")
			->write("if (strpos(\$asset_file, '//') !== 0 && strpos(\$asset_file, 'http://') !== 0 && strpos(\$asset_file, 'https://') !== 0 && !file_exists(\$asset_file)) {\n")
			->indent()
				->write("\$asset_file = \$this->getEnvironment()->getLoader()->getCacheKey(\$asset_file);\n")
				->write("\$argument_string .= ((\$argument_string) ? '&' : '?') . 'assets_version={$config['assets_version']}';\n")
			->outdent()
			->write("}\n")
			->write("\$asset_file .= \$argument_string . \$anchor_string;\n")
			->write("\$context['definition']->append('{$this->get_definition_name()}', '")
		;

		$this->append_asset($compiler);

		$compiler
			->raw("\n');\n")
		;
	}
}
