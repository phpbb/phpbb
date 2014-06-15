<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* Sections (c) 2009 Fabien Potencier, Armin Ronacher
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\template\twig\node;


class includephp extends \Twig_Node
{
	/** @var \Twig_Environment */
	protected $environment;

	public function __construct(\Twig_Node_Expression $expr, \phpbb\template\twig\environment $environment, $lineno, $ignoreMissing = false, $tag = null)
	{
		$this->environment = $environment;

		parent::__construct(array('expr' => $expr), array('ignore_missing' => (Boolean) $ignoreMissing), $lineno, $tag);
	}

	/**
	* Compiles the node to PHP.
	*
	* @param \Twig_Compiler A Twig_Compiler instance
	*/
	public function compile(\Twig_Compiler $compiler)
	{
		$compiler->addDebugInfo($this);

		$config = $this->environment->get_phpbb_config();

		if (!$config['tpl_allow_php'])
		{
			$compiler
				->write("// INCLUDEPHP Disabled\n")
			;

			return;
		}

		if ($this->getAttribute('ignore_missing')) {
			$compiler
				->write("try {\n")
				->indent()
			;
		}

		$compiler
			->write("\$location = ")
			->subcompile($this->getNode('expr'))
			->raw(";\n")
			->write("if (phpbb_is_absolute(\$location)) {\n")
			->indent()
				// Absolute path specified
				->write("require(\$location);\n")
			->outdent()
			->write("} else if (file_exists(\$this->getEnvironment()->get_phpbb_root_path() . \$location)) {\n")
			->indent()
				// PHP file relative to phpbb_root_path
				->write("require(\$this->getEnvironment()->get_phpbb_root_path() . \$location);\n")
			->outdent()
			->write("} else {\n")
			->indent()
				// Local path (behaves like INCLUDE)
				->write("require(\$this->getEnvironment()->getLoader()->getCacheKey(\$location));\n")
			->outdent()
			->write("}\n")
		;

		if ($this->getAttribute('ignore_missing')) {
			$compiler
				->outdent()
				->write("} catch (\Twig_Error_Loader \$e) {\n")
				->indent()
				->write("// ignore missing template\n")
				->outdent()
				->write("}\n\n")
			;
		}
	}
}
