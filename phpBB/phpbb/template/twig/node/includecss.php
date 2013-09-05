<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_template_twig_node_includecss extends phpbb_template_twig_node_includeasset
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
	public function append_asset(Twig_Compiler $compiler)
	{
		$compiler
			->raw("<link href=\"' . ")
			->raw("\$asset_file . '\"")
			->raw(' rel="stylesheet" type="text/css" media="screen, projection" />')
		;
	}
}
