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

namespace phpbb\db\migration\data\v33x;

class profilefield_youtube_update extends \phpbb\db\migration\migration
{
	public static $youtube_url_matcher = 'https:\\/\\/(www\\.)?youtube\\.com\\/.+';

	public function effectively_installed()
	{
		$profile_fields = $this->table_prefix . 'profile_fields';

		$result = $this->db->sql_query(
			"SELECT field_validation
				FROM $profile_fields
				WHERE field_name = 'phpbb_youtube'"
		);

		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return !$row || $row['field_validation'] === self::$youtube_url_matcher;
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v33x\v337'];
	}

	public function update_data()
	{
		return [['custom', [[$this, 'update_youtube_profile_field']]]];
	}

	public function update_youtube_profile_field()
	{
		$profile_fields = $this->table_prefix . 'profile_fields';
		$profile_fields_data = $this->table_prefix . 'profile_fields_data';

		$field_data = [
			'field_length'		=> 40,
			'field_minlen'		=> strlen('https://youtube.com/c/') + 1,
			'field_maxlen'		=> 255,
			'field_validation'	=> self::$youtube_url_matcher,
			'field_contact_url'	=> '%s'
		];

		$sql = 'UPDATE ' . $profile_fields . '
			SET ' . $this->db->sql_build_array('UPDATE', $field_data) . "
			WHERE field_name = 'phpbb_youtube'";
		$this->db->sql_query($sql);

		$yt_profile_field = 'pf_phpbb_youtube';
		$prepend_legacy_youtube_url = $this->db->sql_concatenate(
			"'https://youtube.com/user/'", $yt_profile_field
		);
		$is_not_already_youtube_url = $this->db->sql_not_like_expression(
			$this->db->get_any_char()
				. 'youtube.com/'
				. $this->db->get_any_char()
		);

		// We're done if the profile field doesn't exist
		if (!$this->db_tools->sql_column_exists($profile_fields_data, $yt_profile_field))
		{
			return;
		}

		$this->db->sql_query(
			"UPDATE $profile_fields_data SET
				$yt_profile_field = $prepend_legacy_youtube_url
				WHERE $yt_profile_field <> ''
				AND $yt_profile_field $is_not_already_youtube_url"
		);
	}
}
