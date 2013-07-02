<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_template_twig_node_include extends Twig_Node_Include
{
	/**
	 * Compiles the node to PHP.
	 *
	 * @param Twig_Compiler A Twig_Compiler instance
	 */
	public function compile(Twig_Compiler $compiler)
	{
		$compiler->addDebugInfo($this);

		$compiler
			->write("\$location = ")
			->subcompile($this->getNode('expr'))
			->raw(";\n")
			->write("\$namespace = false;\n")
			->write("if (strpos(\$location, '@') === 0) {\n")
			->indent()
				->write("\$namespace = substr(\$location, 1, strpos(\$location, '/') - 1);\n")
				->write("\$previous_look_up_order = \$this->env->getNamespaceLookUpOrder();\n")

				// We set the namespace lookup order to be this namespace first, then the main path
				->write("\$this->env->setNamespaceLookUpOrder(array(\$namespace, '__main__'));\n")
			->outdent()
			->write("}\n")
		;

		parent::compile($compiler);

		$compiler
			->write("if (\$namespace) {\n")
			->indent()
				->write("\$this->env->setNamespaceLookUpOrder(\$previous_look_up_order);\n")
			->outdent()
			->write("}\n")
		;
	}
}
