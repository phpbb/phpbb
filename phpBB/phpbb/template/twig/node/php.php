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

class php extends \Twig\Node\Node
{
	/** @var \Twig\Environment */
	protected $environment;

	public function __construct(\Twig\Node\TextNode $text, \phpbb\template\twig\environment $environment, $lineno, $tag = null)
	{
		$this->environment = $environment;

		parent::__construct(array('text' => $text), array(), $lineno, $tag);
	}

	/**
	* Compiles the node to PHP.
	*
	* @param \Twig\Compiler A Twig\Compiler instance
	*/
	public function compile(\Twig\Compiler $compiler)
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
