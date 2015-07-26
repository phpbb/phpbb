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

class includecss extends \phpbb\template\twig\node\includeasset
{
	/**
	* {@inheritdoc}
	*/
	public function get_definition_name()
	{
		return 'STYLESHEETS';
	}

	/**
	* {@inheritdoc}
	*/
	public function append_asset(\Twig_Compiler $compiler)
	{
		$compiler
			->raw("<link href=\"' . ")
			->raw("\$asset_file . '\"")
			->raw(' rel="stylesheet" type="text/css" media="screen" />')
		;
	}
}
