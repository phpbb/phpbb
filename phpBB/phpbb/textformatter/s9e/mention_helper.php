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

use s9e\TextFormatter\Utils as TextFormatterUtils;

class mention_helper
{
	/**
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

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
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db
	* @param string $root_path
	* @param string $php_ext
	*/
	public function __construct($db, $root_path, $php_ext)
	{
		$this->db = $db;
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
			function ($attributes) use ($profile_urls)
			{
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
	 * Get a list of mentioned names
	 * TODO: decide what to do with groups
	 *
	 * @param string $xml  Parsed text
	 * @param string $type Name type ('u' for users, 'g' for groups)
	 * @return int[]       List of IDs
	 */
	public function get_mentioned_ids($xml, $type = 'u')
	{
		$ids = array();
		if (strpos($xml, '<MENTION ') === false)
		{
			return $ids;
		}

		$dom = new \DOMDocument;
		$dom->loadXML($xml);
		$xpath = new \DOMXPath($dom);
		/** @var \DOMElement $mention */
		foreach ($xpath->query('//MENTION') as $mention)
		{
			if ($mention->getAttribute('type') === $type)
			{
				$ids[] = (int) $mention->getAttribute('id');
			}
		}

		return $ids;
	}
}
