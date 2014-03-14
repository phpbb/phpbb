<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class postgres_fulltext_drop extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		// This migration is irrelevant for all non-PostgreSQL DBMSes.
		return strpos($this->db->sql_layer, 'postgres') === false;
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\dev',
		);
	}

	public function update_schema()
	{
		/*
		* Drop FULLTEXT indexes related to PostgreSQL fulltext search.
		* Doing so is equivalent to dropping the search index from the ACP.
		* Possibly time-consuming recreation of the search index (i.e.
		* FULLTEXT indexes) is left as a task to the admin to not
		* unnecessarily stall the upgrade process. The new search index will
		* then require about 40% less table space (also see PHPBB3-11040).
		*/
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
