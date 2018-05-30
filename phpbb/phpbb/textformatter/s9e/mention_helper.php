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

class mention_helper
{
	/**
	* @var string Base URL for a user profile link, uses {USER_ID} as placeholder
	*/
	protected $user_profile_url;

	/**
	* @var string Base URL for a group profile link, uses {GROUP_ID} as placeholder
	*/
	protected $group_profile_url;

	/**
	* Constructor
	*
	* @param string $root_path
	* @param string $php_ext
	*/
	public function __construct($root_path, $php_ext)
	{
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
		$user_profile_url = $this->user_profile_url;
		$group_profile_url = $this->group_profile_url;

		return \s9e\TextFormatter\Utils::replaceAttributes(
			$xml,
			'MENTION',
			function ($attributes) use ($user_profile_url, $group_profile_url)
			{
				if (isset($attributes['user_id']))
				{
					$attributes['profile_url'] = str_replace('{USER_ID}', $attributes['user_id'], $user_profile_url);
				}
				else if (isset($attributes['group_id']))
				{
					$attributes['profile_url'] = str_replace('{GROUP_ID}', $attributes['group_id'], $group_profile_url);
				}

				return $attributes;
			}
		);
	}
}
