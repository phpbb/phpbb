<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_template_twig_node_includejs extends phpbb_template_twig_node_includeasset
{
	public function get_definition_name()
	{
		return 'SCRIPTS';
	}

	/**
	 * Compiles the node to PHP.
	 *
	 * @param Twig_Compiler A Twig_Compiler instance
	 */
	protected function append_asset(Twig_Compiler $compiler)
	{
		$config = $this->environment->get_phpbb_config();

		$compiler
			->raw("<script type=\"text/javascript\" src=\"' . ")
			->raw("\$asset_file")
			->raw(". '\"></script>\n")
		;
	}
}
