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

class profilefields_update extends \phpbb\db\migration\migration
{
	/** @var string YouTube URLs matcher: handle or custom URL or channel URL */
	protected $youtube_url_matcher = '(@[a-zA-Z0-9_.-]{3,30}|c/[a-zA-Z][\w\.,\-_]+|(channel|user)/[a-zA-Z][\w\.,\-_]+)';

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v33x\v3310',
			'\phpbb\db\migration\data\v33x\profilefield_youtube_update',
		];
	}

	public function update_schema(): array
	{
		return [
			'change_columns'	=> [
				$this->table_prefix . 'profile_fields'			=> [
					'field_validation'		=> ['VCHAR_UNI:128', ''],
				],
			]
		];
	}

	public function revert_schema(): array
	{
		return [
			'change_columns'	=> [
				$this->table_prefix . 'profile_fields'			=> [
					'field_validation'		=> ['VCHAR_UNI:64', ''],
				],
			]
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'update_youtube_profile_field']]],
			['custom', [[$this, 'update_other_profile_fields']]],
		];
	}

	public function revert_data(): array
	{
		return [
			['custom', [[$this, 'revert_youtube_profile_field']]],
			['custom', [[$this, 'revert_other_profile_fields']]],
		];
	}

	public function update_youtube_profile_field(): bool
	{
		$profile_fields = $this->table_prefix . 'profile_fields';
		$profile_fields_data = $this->table_prefix . 'profile_fields_data';
		$end_time = time() + 5; // allow up to 5 seconds for migration to run

		$field_data = [
			'field_length'			=> 20,
			'field_minlen'			=> 3,
			'field_maxlen'			=> 60,
			'field_validation'		=> $this->youtube_url_matcher,
			'field_contact_url'		=> 'https://youtube.com/%s',
			'field_contact_desc'	=> 'VIEW_YOUTUBE_PROFILE',
		];

		$sql = 'UPDATE ' . $profile_fields . '
			SET ' . $this->db->sql_build_array('UPDATE', $field_data) . "
			WHERE field_name = 'phpbb_youtube'";
		$this->db->sql_query($sql);

		$yt_profile_field = 'pf_phpbb_youtube';
		$has_youtube_url = $this->db->sql_like_expression($this->db->get_any_char() . 'youtube.com/' . $this->db->get_any_char());

		// We're done if the profile field doesn't exist
		if (!$this->db_tools->sql_column_exists($profile_fields_data, $yt_profile_field))
		{
			return true;
		}

		$update_aborted = false;

		$sql = 'SELECT user_id, pf_phpbb_youtube
			FROM ' . $profile_fields_data . "
			WHERE $yt_profile_field <> ''
				AND $yt_profile_field $has_youtube_url";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$updated_youtube_url_part = $this->get_youtube_url_part($row['pf_phpbb_youtube']);
			if ($updated_youtube_url_part != $row['pf_phpbb_youtube'])
			{
				$this->db->sql_query(
					"UPDATE $profile_fields_data
					SET $yt_profile_field = '$updated_youtube_url_part'
					WHERE user_id = {$row['user_id']}"
				);
			}

			if (time() > $end_time)
			{
				$update_aborted = true;
				break;
			}
		}
		$this->db->sql_freeresult($result);

		return $update_aborted != true;
	}

	public function update_other_profile_fields(): void
	{
		$profile_fields = $this->table_prefix . 'profile_fields';

		$this->db->sql_query(
			"UPDATE $profile_fields
				SET field_contact_url = 'https://facebook.com/%s/'
				WHERE field_name = 'phpbb_facebook'"
		);

		$this->db->sql_query(
			"UPDATE $profile_fields
				SET field_contact_url = 'https://twitter.com/%s'
				WHERE field_name = 'phpbb_twitter'"
		);
	}

	public function revert_youtube_profile_field(): void
	{
		$profile_fields = $this->table_prefix . 'profile_fields';
		$profile_fields_data = $this->table_prefix . 'profile_fields_data';

		$field_data = [
			'field_length'		=> 40,
			'field_minlen'		=> strlen('https://youtube.com/c/') + 1,
			'field_maxlen'		=> 255,
			'field_validation'	=> profilefield_youtube_update::$youtube_url_matcher,
			'field_contact_url'	=> '%s'
		];

		$sql = 'UPDATE ' . $profile_fields . '
			SET ' . $this->db->sql_build_array('UPDATE', $field_data) . "
			WHERE field_name = 'phpbb_youtube'";
		$this->db->sql_query($sql);

		$yt_profile_field = 'pf_phpbb_youtube';

		// We're done if the profile field doesn't exist
		if (!$this->db_tools->sql_column_exists($profile_fields_data, $yt_profile_field))
		{
			return;
		}

		$prepend_legacy_youtube_url = $this->db->sql_concatenate(
			"'https://youtube.com/'", $yt_profile_field
		);
		$is_not_already_youtube_url = $this->db->sql_not_like_expression(
			$this->db->get_any_char()
			. 'youtube.com/'
			. $this->db->get_any_char()
		);

		$this->db->sql_query(
			"UPDATE $profile_fields_data SET
				$yt_profile_field = $prepend_legacy_youtube_url
				WHERE $yt_profile_field <> ''
				AND $yt_profile_field $is_not_already_youtube_url"
		);
	}

	public function revert_other_profile_fields(): void
	{
		$profile_fields = $this->table_prefix . 'profile_fields';

		$this->db->sql_query(
			"UPDATE $profile_fields
				SET field_contact_url = 'http://facebook.com/%s/'
				WHERE field_name = 'phpbb_facebook'"
		);

		$this->db->sql_query(
			"UPDATE $profile_fields
				SET field_contact_url = 'http://twitter.com/%s'
				WHERE field_name = 'phpbb_twitter'"
		);
	}

	protected function get_youtube_url_part(string $profile_field_string): string
	{
		return preg_replace('#^https://(?:www\.)?youtube\.com/(.+)$#iu', '$1', $profile_field_string);
	}
}
