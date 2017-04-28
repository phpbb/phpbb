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

class quote_helper
{
	/**
	* @var string Base URL for a post link, uses {POST_ID} as placeholder
	*/
	protected $post_url;

	/**
	* @var string Base URL for a profile link, uses {USER_ID} as placeholder
	*/
	protected $profile_url;

	/**
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\user $user
	* @param string $root_path
	* @param string $php_ext
	*/
	public function __construct(\phpbb\user $user, $root_path, $php_ext)
	{
		$this->post_url = append_sid($root_path . 'viewtopic.' . $php_ext, 'p={POST_ID}#p{POST_ID}', false);
		$this->profile_url = append_sid($root_path . 'memberlist.' . $php_ext, 'mode=viewprofile&u={USER_ID}', false);
		$this->user = $user;
	}

	/**
	* Inject dynamic metadata into QUOTE tags in given XML
	*
	* @param  string $xml Original XML
	* @return string      Modified XML
	*/
	public function inject_metadata($xml)
	{
		$post_url = $this->post_url;
		$profile_url = $this->profile_url;
		$user = $this->user;

		return \s9e\TextFormatter\Utils::replaceAttributes(
			$xml,
			'QUOTE',
			function ($attributes) use ($post_url, $profile_url, $user)
			{
				if (isset($attributes['post_id']))
				{
					$attributes['post_url'] = str_replace('{POST_ID}', $attributes['post_id'], $post_url);
				}
				if (isset($attributes['time']))
				{
					$attributes['date'] = $user->format_date($attributes['time']);
				}
				if (isset($attributes['user_id']))
				{
					$attributes['profile_url'] = str_replace('{USER_ID}', $attributes['user_id'], $profile_url);
				}

				return $attributes;
			}
		);
	}
}
