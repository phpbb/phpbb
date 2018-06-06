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

namespace phpbb\textformatter\s9e;

use s9e\TextFormatter\Utils;

class mention_helper
{
	/**
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* @var string Base URL for a user profile link, uses {USER_ID} as placeholder
	*/
	protected $user_profile_url;

	/**
	* @var string Base URL for a group profile link, uses {GROUP_ID} as placeholder
	*/
	protected $group_profile_url;

	/**
	* @var array Array of users' and groups' colors for each cached ID
	*/
	protected $cached_colors = [];

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db
	* @param string $root_path
	* @param string $php_ext
	*/
	public function __construct($db, $root_path, $php_ext)
	{
		$this->db = $db;
		$this->user_profile_url = append_sid($root_path . 'memberlist.' . $php_ext, 'mode=viewprofile&u={USER_ID}', false);
		$this->group_profile_url = append_sid($root_path . 'memberlist.' . $php_ext, 'mode=group&g={GROUP_ID}', false);
	}

	/**
	 * Caches colors for specified user IDs and group IDs
	 *
	 * @param array $user_ids
	 * @param array $group_ids
	 */
	protected function get_colors($user_ids, $group_ids)
	{
		$this->cached_colors = [];
		$this->cached_colors['users'] = [];
		$this->cached_colors['groups'] = [];

		if (!empty($user_ids))
		{
			$query = $this->db->sql_build_query('SELECT', [
				'SELECT' => 'u.user_colour, u.user_id',
				'FROM'   => [
					USERS_TABLE => 'u',
				],
				'WHERE'  => 'u.user_id <> ' . ANONYMOUS . '
				AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]) . '
				AND ' . $this->db->sql_in_set('u.user_id', $user_ids),
			]);
			$res = $this->db->sql_query($query);

			while ($row = $this->db->sql_fetchrow($res))
			{
				$this->cached_colors['users'][$row['user_id']] = $row['user_colour'];
			}
		}

		if (!empty($group_ids))
		{
			$query = $this->db->sql_build_query('SELECT', [
				'SELECT' => 'g.group_colour, g.group_id',
				'FROM'   => [
					GROUPS_TABLE => 'g',
				],
				'WHERE'  => $this->db->sql_in_set('g.group_id', $group_ids),
			]);
			$res = $this->db->sql_query($query);

			while ($row = $this->db->sql_fetchrow($res))
			{
				$this->cached_colors['groups'][$row['group_id']] = $row['group_colour'];
			}
		}
	}

	/**
	* Inject dynamic metadata into MENTION tags in given XML
	*
	* @param  string $xml Original XML
	* @return string      Modified XML
	*/
	public function inject_metadata($xml)
	{
		$user_profile_url = $this->user_profile_url;
		$group_profile_url = $this->group_profile_url;

		// TODO: think about optimization for caching colors.
		$this->get_colors(
			Utils::getAttributeValues($xml, 'MENTION', 'user_id'),
			Utils::getAttributeValues($xml, 'MENTION', 'group_id')
		);

		return Utils::replaceAttributes(
			$xml,
			'MENTION',
			function ($attributes) use ($user_profile_url, $group_profile_url)
			{
				if (isset($attributes['user_id']))
				{
					$attributes['profile_url'] = str_replace('{USER_ID}', $attributes['user_id'], $user_profile_url);

					if (!empty($this->cached_colors['users'][$attributes['user_id']]))
					{
						$attributes['color'] = $this->cached_colors['users'][$attributes['user_id']];
					}
				}
				else if (isset($attributes['group_id']))
				{
					$attributes['profile_url'] = str_replace('{GROUP_ID}', $attributes['group_id'], $group_profile_url);

					if (!empty($this->cached_colors['groups'][$attributes['group_id']]))
					{
						$attributes['color'] = $this->cached_colors['groups'][$attributes['group_id']];
					}
				}

				return $attributes;
			}
		);
	}
}
