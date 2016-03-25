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

namespace phpbb\db\migration\data\v310;

class mysql_fulltext_drop extends \phpbb\db\migration\migration
{
	protected $indexes;

	public function effectively_installed()
	{
		// This migration is irrelevant for all non-MySQL DBMSes.
		if (strpos($this->db->get_sql_layer(), 'mysql') === false)
		{
			return true;
		}

		$this->find_indexes_to_drop();
		return empty($this->indexes);
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\dev',
		);
	}

	public function update_schema()
	{
		if (empty($this->indexes))
		{
			return array();
		}

		/*
		* Drop FULLTEXT indexes related to MySQL fulltext search.
		* Doing so is equivalent to dropping the search index from the ACP.
		* Possibly time-consuming recreation of the search index (i.e.
		* FULLTEXT indexes) is left as a task to the admin to not
		* unnecessarily stall the upgrade process. The new search index will
		* then require about 40% less table space (also see PHPBB3-11621).
		*/
		return array(
			'drop_keys' => array(
				$this->table_prefix . 'posts' => $this->indexes,
			),
		);
	}

	public function find_indexes_to_drop()
	{
		if ($this->indexes !== null)
		{
			return $this->indexes;
		}

		$this->indexes = array();
		$potential_keys = array('post_subject', 'post_text', 'post_content');
		foreach ($potential_keys as $key)
		{
			if ($this->db_tools->sql_index_exists($this->table_prefix . 'posts', $key))
			{
				$this->indexes[] = $key;
			}
		}

		return $this->indexes;
	}
}
