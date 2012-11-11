<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v301rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v301');
	}

	function update_schema()
	{
		return array();
	}

	function update_data()
	{
		return array(
			array('config.add', array('referer_validation', '1')),
			array('config.add', array('check_attachment_content', '1')),
			array('config.add', array('mime_triggers', 'body|head|html|img|plaintext|a href|pre|script|table|title')),
		);
	}
}
