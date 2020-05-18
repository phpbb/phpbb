<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\textformatter\s9e;

use s9e\TextFormatter\Utils as TextFormatterUtils;

class mention_helper
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\auth\auth
	 */
	protected $auth;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var string Base URL for a user profile link, uses {USER_ID} as placeholder
	 */
	protected $user_profile_url;

	/**
	 * @var string Base URL for a group profile link, uses {GROUP_ID} as placeholder
	 */
	protected $group_profile_url;

	/**
	 * @var array Array of group IDs allowed to be mentioned by current user
	 */
	protected $mentionable_groups = null;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\auth\auth                  $auth
	 * @param \phpbb\user                       $user
	 * @param string                            $root_path
	 * @param string                            $php_ext
	 */
	public function __construct($db, $auth, $user, $root_path, $php_ext)
	{
		$this->db = $db;
		$this->auth = $auth;
		$this->user = $user;
		$this->user_profile_url = append_sid($root_path . 'memberlist.' . $php_ext, 'mode=viewprofile&u={USER_ID}', false);
		$this->group_profile_url = append_sid($root_path . 'memberlist.' . $php_ext, 'mode=group&g={GROUP_ID}', false);
	}

	/**
	 * Inject dynamic metadata into MENTION tags in given XML
	 *
	 * @param  string $xml Original XML
	 * @return string      Modified XML
	 */
	public function inject_metadata($xml)
	{
		$profile_urls = [
			'u' => $this->user_profile_url,
			'g' => $this->group_profile_url,
		];

		return TextFormatterUtils::replaceAttributes(
			$xml,
			'MENTION',
			function ($attributes) use ($profile_urls)
			{
				if (isset($attributes['user_id']))
				{
					$attributes['profile_url'] = str_replace('{USER_ID}', $attributes['user_id'], $profile_urls['u']);
				}
				else if (isset($attributes['group_id']))
				{
					$attributes['profile_url'] = str_replace('{GROUP_ID}', $attributes['group_id'], $profile_urls['g']);
				}

				return $attributes;
			}
		);
	}

	/**
	 * Get group IDs allowed to be mentioned by current user
	 *
	 * @return array
	 */
	protected function get_mentionable_groups()
	{
		if (is_array($this->mentionable_groups))
		{
			return $this->mentionable_groups;
		}

		$hidden_restriction = (!$this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel')) ? ' AND (g.group_type <> ' . GROUP_HIDDEN . ' OR (ug.user_pending = 0 AND ug.user_id = ' . (int) $this->user->data['user_id'] . '))' : '';

		$query = $this->db->sql_build_query('SELECT', [
			'SELECT'    => 'g.group_id',
			'FROM'      => [
				GROUPS_TABLE => 'g',
			],
			'LEFT_JOIN' => [[
				'FROM' => [
					USER_GROUP_TABLE => 'ug',
				],
				'ON'   => 'g.group_id = ug.group_id',
			]],
			'WHERE'     => '(g.group_type <> ' . GROUP_SPECIAL . ' OR ' . $this->db->sql_in_set('g.group_name', ['ADMINISTRATORS', 'GLOBAL_MODERATORS']) . ')' . $hidden_restriction,
		]);
		$result = $this->db->sql_query($query);

		$this->mentionable_groups = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->mentionable_groups[] = $row['group_id'];
		}

		$this->db->sql_freeresult($result);

		return $this->mentionable_groups;
	}

	/**
	 * Selects IDs of user members of a certain group
	 *
	 * @param array $user_ids Array of already selected user IDs
	 * @param int   $group_id ID of the group to search members in
	 */
	protected function get_user_ids_for_group(&$user_ids, $group_id)
	{
		if (!in_array($group_id, $this->get_mentionable_groups()))
		{
			return;
		}

		$query = $this->db->sql_build_query('SELECT', [
			'SELECT' => 'ug.user_id, ug.group_id',
			'FROM'   => [
				USER_GROUP_TABLE => 'ug',
				GROUPS_TABLE     => 'g',
			],
			'WHERE'  => 'g.group_id = ug.group_id',
		]);
		// Cache results for 5 minutes
		$result = $this->db->sql_query($query, 300);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['group_id'] == $group_id)
			{
				$user_ids[] = (int) $row['user_id'];
			}
		}

		$this->db->sql_freeresult($result);
	}

	/**
	 * Get a list of mentioned user IDs
	 *
	 * @param string $xml  Parsed text
	 * @return int[]       List of user IDs
	 */
	public function get_mentioned_user_ids($xml)
	{
		$ids = array();
		if (strpos($xml, '<MENTION ') === false)
		{
			return $ids;
		}

		// Add IDs of users mentioned directly
		$user_ids = TextFormatterUtils::getAttributeValues($xml, 'MENTION', 'user_id');
		$ids = array_merge($ids, array_map('intval', $user_ids));

		// Add IDs of users mentioned as group members
		$group_ids = TextFormatterUtils::getAttributeValues($xml, 'MENTION', 'group_id');
		foreach ($group_ids as $group_id)
		{
			$this->get_user_ids_for_group($ids, (int) $group_id);
		}

		return $ids;
	}
}
