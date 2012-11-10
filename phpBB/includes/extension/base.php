<?php
/**
*
* @package extension
* @copyright (c) 2011 phpBB Group
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

/**
* A base class for extensions without custom enable/disable/purge code.
*
* @package extension
*/
class phpbb_extension_base implements phpbb_extension_interface
{
	protected $umil_instructions = array();

	/**
	* Single enable step that does nothing
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return false Indicates no further steps are required
	*/
	public function enable_step($old_state)
	{
		if (!empty($this->umil_instructions))
		{
			global $phpbb_root_path, $phpEx, $db;

			if (!class_exists('umil'))
			{
				include($phpbb_root_path . 'includes/umil/umil.' . $phpEx);
			}

			$umil = new umil(true, $db);

			$umil->run_actions('update', $this->umil_instructions, __CLASS__);
		}

		return false;
	}

	/**
	* Single disable step that does nothing
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return false Indicates no further steps are required
	*/
	public function disable_step($old_state)
	{
		return false;
	}

	/**
	* Single purge step that does nothing
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return false Indicates no further steps are required
	*/
	public function purge_step($old_state)
	{
		if (!empty($this->umil_instructions))
		{
			global $phpbb_root_path, $phpEx, $db;

			if (!class_exists('umil'))
			{
				include($phpbb_root_path . 'includes/umil/umil.' . $phpEx);
			}

			$umil = new umil(true, $db);

			$umil->run_actions('uninstall', $this->umil_instructions, __CLASS__);
		}

		return false;
	}
}
