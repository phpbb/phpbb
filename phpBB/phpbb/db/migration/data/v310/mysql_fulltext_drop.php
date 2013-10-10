<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class mysql_fulltext_drop extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\dev',
		);
	}

	public function update_schema()
	{
		if (strpos($this->db->sql_layer, 'mysql') === false)
		{
			return array();
		}

		return array(
			'drop_keys' => array(
				$this->table_prefix . 'posts' => array(
					'post_subject',
					'post_text',
					'post_content',
				),
			),
		);
	}
}
