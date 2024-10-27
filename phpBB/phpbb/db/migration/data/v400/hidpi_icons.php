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

class hidpi_icons extends migration
{
	private array $default_icons = [
		'misc/fire',
		'misc/heart',
		'misc/radioactive',
		'misc/star',
		'misc/thinking',
		'smile/alert',
		'smile/info',
		'smile/mrgreen',
		'smile/question',
		'smile/redface',
	];

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\dev'
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'gif_to_svg_icons']]],
		];
	}

	public function revert_data(): array
	{
		return [
			['custom', [[$this, 'svg_to_gif_icons']]],
		];
	}

	public function gif_to_svg_icons(): void
	{
		foreach ($this->default_icons as $smiley)
		{
			$sql = 'UPDATE ' . $this->tables['icons'] . "
				SET icons_url = '" . $this->db->sql_escape($smiley) . ".svg'
				WHERE icons_url = '" . $this->db->sql_escape($smiley) . ".gif'";
			$this->db->sql_query($sql);
		}
	}

	public function svg_to_gif_icons(): void
	{
		foreach ($this->default_icons as $smiley)
		{
			$sql = 'UPDATE ' . $this->tables['icons'] . "
				SET icons_url = '" . $this->db->sql_escape($smiley) . ".gif'
				WHERE icons_url = '" . $this->db->sql_escape($smiley) . ".svg'";
			$this->db->sql_query($sql);
		}
	}
}
