<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\template\twig\node;

class includeasset extends \Twig_Node
{
	/** @var Twig_Environment */
	protected $environment;

	public function __construct(\Twig_Node_Expression $expr, \phpbb\template\twig\environment $environment, $lineno, $tag = null)
	{
		$this->environment = $environment;

		parent::__construct(array('expr' => $expr), array(), $lineno, $tag);
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

		$compiler
			->write("\$asset_file = ")
			->subcompile($this->getNode('expr'))
			->raw(";\n")
			->write("\$asset = new \phpbb\\template\asset(\$asset_file);\n")
			->write("if (substr(\$asset_file, 0, 2) !== './' && \$asset->is_relative()) {\n")
			->indent()
				->write("\$asset_path = \$asset->get_path();")
				->write("\$local_file = \$this->getEnvironment()->get_phpbb_root_path() . \$asset_path;\n")
				->write("if (!file_exists(\$local_file)) {\n")
				->indent()
					->write("\$local_file = \$this->getEnvironment()->getLoader()->getCacheKey(\$asset_path);\n")
					->write("\$asset->set_path(\$local_file, true);\n")
				->outdent()
				->write("\$asset->add_assets_version({$config['assets_version']});\n")
				->write("\$asset_file = \$asset->get_url();\n")
				->write("}\n")
			->outdent()
			->write("}\n")
			->write("\$context['definition']->append('{$this->get_definition_name()}', '")
		;

		$this->append_asset($compiler);

		$compiler
			->raw("\n');\n")
		;
	}
}
