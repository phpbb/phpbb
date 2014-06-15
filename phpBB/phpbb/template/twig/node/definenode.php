<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @copyright Portions (c) 2009 Fabien Potencier, Armin Ronacher
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\template\twig\node;


class definenode extends \Twig_Node
{
	public function __construct($capture, \Twig_NodeInterface $name, \Twig_NodeInterface $value, $lineno, $tag = null)
	{
		parent::__construct(array('name' => $name, 'value' => $value), array('capture' => $capture, 'safe' => false), $lineno, $tag);
	}

	/**
	* Compiles the node to PHP.
	*
	* @param \Twig_Compiler A Twig_Compiler instance
	*/
	public function compile(\Twig_Compiler $compiler)
	{
		$compiler->addDebugInfo($this);

		if ($this->getAttribute('capture')) {
			$compiler
				->write("ob_start();\n")
				->subcompile($this->getNode('value'))
			;

			$compiler->write("\$value = ('' === \$value = ob_get_clean()) ? '' : new \Twig_Markup(\$value, \$this->env->getCharset());\n");
		}
		else
		{
			$compiler
				->write("\$value = ")
				->subcompile($this->getNode('value'))
				->raw(";\n")
			;
		}

		$compiler
			->write("\$context['definition']->set('")
			->raw($this->getNode('name')->getAttribute('name'))
			->raw("', \$value);\n")
		;
	}
}
