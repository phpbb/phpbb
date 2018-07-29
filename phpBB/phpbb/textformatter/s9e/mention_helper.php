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
	 * @var string Base URL for a user profile link, uses {ID} as placeholder
	 */
	protected $user_profile_url;

	/**
	 * @var string Base URL for a group profile link, uses {ID} as placeholder
	 */
	protected $group_profile_url;

	/**
	 * @var array Array of users' and groups' colours for each cached ID
	 */
	protected $cached_colours = [];

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
		$this->user_profile_url = append_sid($root_path . 'memberlist.' . $php_ext, 'mode=viewprofile&u={ID}', false);
		$this->group_profile_url = append_sid($root_path . 'memberlist.' . $php_ext, 'mode=group&g={ID}', false);
	}

	/**
	 * Returns SQL query data for colour SELECT request
	 *
	 * @param string $type Name type ('u' for users, 'g' for groups)
	 * @param array  $ids  Array of IDs
	 * @return array Array of SQL SELECT query data for extracting colours for names
	 */
	protected function get_colours_sql($type, $ids)
	{
		switch ($type)
		{
			default:
			case 'u':
				return [
					'SELECT' => 'u.user_colour as colour, u.user_id as id',
					'FROM'   => [
						USERS_TABLE => 'u',
					],
					'WHERE'  => 'u.user_id <> ' . ANONYMOUS . '
						AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]) . '
						AND ' . $this->db->sql_in_set('u.user_id', $ids),
				];
			case 'g':
				return [
					'SELECT' => 'g.group_colour as colour, g.group_id as id',
					'FROM'   => [
						GROUPS_TABLE => 'g',
					],
					'WHERE'  => $this->db->sql_in_set('g.group_id', $ids),
				];
		}
	}

	/**
	 * Caches colours for selected IDs of the specified type
	 *
	 * @param string $type Name type ('u' for users, 'g' for groups)
	 * @param array  $ids  Array of IDs
	 */
	protected function get_colours($type, $ids)
	{
		$this->cached_colours[$type] = [];

		if (!empty($ids))
		{
			$query = $this->db->sql_build_query('SELECT', $this->get_colours_sql($type, $ids));
			$result = $this->db->sql_query($query);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->cached_colours[$type][$row['id']] = $row['colour'];
			}

			$this->db->sql_freeresult($result);
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
		$profile_urls = [
			'u' => $this->user_profile_url,
			'g' => $this->group_profile_url,
		];

		// TODO: think about optimization for caching colors.
		$this->cached_colours = [];
		$this->get_colours('u', $this->get_mentioned_ids($xml, 'u'));
		$this->get_colours('g', $this->get_mentioned_ids($xml, 'g'));

		return TextFormatterUtils::replaceAttributes(
			$xml,
			'MENTION',
			function ($attributes) use ($profile_urls) {
				if (isset($attributes['type']) && isset($attributes['id']))
				{
					$type = $attributes['type'];
					$id = $attributes['id'];

					$attributes['profile_url'] = str_replace('{ID}', $id, $profile_urls[$type]);

					if (!empty($this->cached_colours[$type][$id]))
					{
						$attributes['color'] = $this->cached_colours[$type][$id];
					}
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
	 * Get a list of mentioned names
	 *
	 * @param string $xml  Parsed text
	 * @param string $type Name type ('u' for users, 'g' for groups,
	 *                     'ug' for usernames mentioned separately or as group members)
	 * @return int[]       List of IDs
	 */
	public function get_mentioned_ids($xml, $type = 'ug')
	{
		$ids = array();
		if (strpos($xml, '<MENTION ') === false)
		{
			return $ids;
		}

		$dom = new \DOMDocument;
		$dom->loadXML($xml);
		$xpath = new \DOMXPath($dom);

		if ($type === 'ug')
		{
			/** @var \DOMElement $mention */
			foreach ($xpath->query('//MENTION') as $mention)
			{
				if ($mention->getAttribute('type') === 'u')
				{
					$ids[] = (int) $mention->getAttribute('id');
				}
				else if ($mention->getAttribute('type') === 'g')
				{
					$this->get_user_ids_for_group($ids, (int) $mention->getAttribute('id'));
				}
			}
		}
		else
		{
			/** @var \DOMElement $mention */
			foreach ($xpath->query('//MENTION') as $mention)
			{
				if ($mention->getAttribute('type') === $type)
				{
					$ids[] = (int) $mention->getAttribute('id');
				}
			}
		}

		return $ids;
	}
}
