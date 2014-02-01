<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_dbal_migration_revert_with_dependency extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('phpbb_dbal_migration_revert');
	}
}
