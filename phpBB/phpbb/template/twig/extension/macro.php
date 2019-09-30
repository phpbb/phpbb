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

namespace phpbb\template\twig\extension;

use phpbb\template\twig\environment;

abstract class macro extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
	/** @var environment */
	protected $twig;

	/**
	 * Constructor.
	 *
	 * @param environment	$twig		Twig environment object
	 */
	public function __construct(environment $twig)
	{
		$this->twig = $twig;
	}

	/**
	 * Returns the name of this extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'macro';
	}

	/**
	 * Returns a list of global variables to add to the existing list.
	 *
	 * @return array An array of global variables
	 */
	public function getGlobals()
	{
		$macros = null;

		try
		{
			$macros = $this->twig->loadTemplate('macros.html');
		}
		catch (\Twig\Error\Error $e)
		{
		}

		return [
			'macros' => $macros,
		];
	}
}
