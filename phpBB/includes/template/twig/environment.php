<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_template_twig_environment extends Twig_Environment
{
	/**
	* recursive helper to set variables into $context so that Twig can properly fetch them for display
	*
	* @param array $data Data to set at the end of the chain
	* @param array $blocks Array of blocks to loop into still
	* @param mixed $current_location Current location in $context (recursive!)
	*/
	public function context_recursive_loop_builder($data, $blocks, &$current_location)
	{
		$block = array_shift($blocks);

		if (empty($blocks))
		{
			$current_location[$block] = $data;
		}
		else
		{
			$this->context_recursive_loop_builder($data, $blocks, $current_location[$block]);
		}
	}
}
