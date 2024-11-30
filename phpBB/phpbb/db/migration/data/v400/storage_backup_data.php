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

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\migration;

class storage_backup_data extends migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\storage_backup',
		];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'update_backup_data']]],
		];
	}

	public function update_backup_data()
	{
		$methods = ['sql', 'sql.gz', 'sql.bz2'];

		$dir = $this->phpbb_root_path . 'store/';
		$dh = @opendir($dir);

		if ($dh)
		{
			while (($file = readdir($dh)) !== false)
			{
				echo "FILE $file\n";
				if (preg_match('#^backup_(\d{10,})_(?:[a-z\d]{16}|[a-z\d]{32})\.(sql(?:\.(?:gz|bz2))?)$#i', $file, $matches))
				{
					if (in_array($matches[2], $methods))
					{
						$insert_ary = [
							'filename'	=> $file,
						];

						$sql = 'INSERT INTO ' . $this->table_prefix . 'backups ' . $this->db->sql_build_array('INSERT', $insert_ary);

						$this->db->sql_query($sql);
					}
				}

			}

			closedir($dh);
		}
	}
}
