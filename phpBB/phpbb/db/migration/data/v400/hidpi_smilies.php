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

class hidpi_smilies extends migration
{
	private array $default_smilies = [
		'icon_arrow',
		'icon_cool',
		'icon_cry',
		'icon_e_biggrin',
		'icon_e_confused',
		'icon_e_geek',
		'icon_e_sad',
		'icon_e_smile',
		'icon_e_surprised',
		'icon_e_ugeek',
		'icon_e_wink',
		'icon_eek',
		'icon_evil',
		'icon_exclaim',
		'icon_idea',
		'icon_lol',
		'icon_mad',
		'icon_mrgreen',
		'icon_neutral',
		'icon_question',
		'icon_razz',
		'icon_redface',
		'icon_rolleyes',
		'icon_twisted',
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
			['custom', [[$this, 'gif_to_svg_smilies']]],
		];
	}

	public function revert_data(): array
	{
		return [
			['custom', [[$this, 'svg_to_gif_smilies']]],
		];
	}

	public function gif_to_svg_smilies(): void
	{
		foreach ($this->default_smilies as $smiley)
		{
			$sql = 'UPDATE ' . $this->tables['smilies'] . "smilies
				SET smiley_url = '" . $this->db->sql_escape($smiley) . ".svg'
				WHERE smiley_url = '" . $this->db->sql_escape($smiley) . ".gif'";
			$this->db->sql_query($sql);
		}
	}

	public function svg_to_gif_smilies(): void
	{
		foreach ($this->default_smilies as $smiley)
		{
			$sql = 'UPDATE ' . $this->tables['smilies'] . "
				SET smiley_url = '" . $this->db->sql_escape($smiley) . ".gif'
				WHERE smiley_url = '" . $this->db->sql_escape($smiley) . ".svg'";
			$this->db->sql_query($sql);
		}
	}
}
