<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\template\twig\node;


class includenode extends \Twig_Node_Include
{
	/**
	* Compiles the node to PHP.
	*
	* @param \Twig_Compiler A Twig_Compiler instance
	*/
	public function compile(\Twig_Compiler $compiler)
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
