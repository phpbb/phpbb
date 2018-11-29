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

namespace phpbb\group;

class helper
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string PHP file extension */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth Authentication object
	 * @param \phpbb\cache\service $cache Cache object
	 * @param \phpbb\config\config $config Configuration object
	 * @param \phpbb\language\language $language Language object
	 * @param \phpbb\event\dispatcher_interface $phpbb_dispatcher Event dispatcher object
	 * @param \phpbb\path_helper $phpbb_path_helper Path helper object
	 * @param \phpbb\user $user User object
	 * @param string $phpbb_root_path phpBB root path
	 * @param string $php_ext PHP file extension
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\language\language $language, \phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\path_helper $phpbb_path_helper, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->language = $language;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->phpbb_path_helper = $phpbb_path_helper;
		$this->user = $user;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @param $group_name string	The stored group name
	 *
	 * @return string		Group name or translated group name if it exists
	 */
	public function get_name($group_name)
	{
		return $this->language->is_set('G_' . utf8_strtoupper($group_name)) ? $this->language->lang('G_' . utf8_strtoupper($group_name)) : $group_name;
	}

	/**
	 * Get group name details for placing into templates.
	 *
	 * @param string	$mode				Can be profile (for getting an url to the profile), group_name (for obtaining the group name), colour (for obtaining the group colour), full (for obtaining a html string representing a coloured link to the group's profile) or no_profile (the same as full but forcing no profile link)
	 * @param int		$group_id			The group id
	 * @param string	$group_name			The group name
	 * @param string	$group_colour		The group colour
	 * @param mixed		$custom_profile_url	optional parameter to specify a profile url. The group id gets appended to this url as &amp;g={group_id}
	 *
	 * @return string A string consisting of what is wanted based on $mode.
	 */
	public function get_name_string($mode, $group_id, $group_name, $group_colour = '', $custom_profile_url = false)
	{
		$_profile_cache = array(
			'base_url'				=> append_sid("{$this->phpbb_root_path}memberlist.{$this->php_ext}", 'mode=group&amp;g={GROUP_ID}'),
			'tpl_noprofile'			=> '<span class="username">{GROUP_NAME}</span>',
			'tpl_noprofile_colour'	=> '<span class="username-coloured" style="color: {GROUP_COLOUR};">{GROUP_NAME}</span>',
			'tpl_profile'			=> '<a class="username" href="{PROFILE_URL}">{GROUP_NAME}</a>',
			'tpl_profile_colour'	=> '<a class="username-coloured" href="{PROFILE_URL}" style="color: {GROUP_COLOUR};">{GROUP_NAME}</a>',
		);

		// This switch makes sure we only run code required for the mode
		switch ($mode)
		{
			case 'full':
			case 'no_profile':
			case 'colour':

				// Build correct group colour
				$group_colour = ($group_colour) ? '#' . $group_colour : '';

				// Return colour
				if ($mode === 'colour')
				{
					$group_name_string = $group_colour;
					break;
				}

			// no break;

			case 'group_name':

				// Build correct group name
				$group_name = $this->get_name($group_name);

				// Return group name
				if ($mode === 'group_name')
				{
					$group_name_string = $group_name;
					break;
				}

			// no break;

			case 'profile':

				// Build correct profile url - only show if not anonymous and permission to view profile if registered user
				// For anonymous the link leads to a login page.
				if ($group_id && ($this->user->data['user_id'] == ANONYMOUS || $this->auth->acl_get('u_viewprofile')))
				{
					$profile_url = ($custom_profile_url !== false) ? $custom_profile_url . '&amp;g=' . (int) $group_id : str_replace(array('={GROUP_ID}', '=%7BGROUP_ID%7D'), '=' . (int) $group_id, $_profile_cache['base_url']);
				}
				else
				{
					$profile_url = '';
				}

				// Return profile
				if ($mode === 'profile')
				{
					$group_name_string = $profile_url;
					break;
				}

			// no break;
		}

		if (!isset($group_name_string))
		{
			if (($mode === 'full' && !$profile_url) || $mode === 'no_profile')
			{
				$group_name_string = str_replace(array('{GROUP_COLOUR}', '{GROUP_NAME}'), array($group_colour, $group_name), (!$group_colour) ? $_profile_cache['tpl_noprofile'] : $_profile_cache['tpl_noprofile_colour']);
			}
			else
			{
				$group_name_string = str_replace(array('{PROFILE_URL}', '{GROUP_COLOUR}', '{GROUP_NAME}'), array($profile_url, $group_colour, $group_name), (!$group_colour) ? $_profile_cache['tpl_profile'] : $_profile_cache['tpl_profile_colour']);
			}
		}

		/**
		 * Use this event to change the output of the group name
		 *
		 * @event core.modify_group_name_string
		 * @var string	mode				profile|group_name|colour|full|no_profile
		 * @var int		group_id			The group identifier
		 * @var string	group_name			The group name
		 * @var string	group_colour		The group colour
		 * @var string	custom_profile_url	Optional parameter to specify a profile url.
		 * @var string	group_name_string	The string that has been generated
		 * @var array	_profile_cache		Array of original return templates
		 * @since 3.2.5-RC1
		 */
		$vars = array(
			'mode',
			'group_id',
			'group_name',
			'group_colour',
			'custom_profile_url',
			'group_name_string',
			'_profile_cache',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.modify_group_name_string', compact($vars)));

		return $group_name_string;
	}

	/**
	 * Get group rank title and image
	 *
	 * @param array		$group_data		the current stored group data
	 *
	 * @return array					An associative array containing the rank title (title),
	 * 									the rank image as full img tag (img) and the rank image source (img_src)
	 */
	public function get_rank($group_data)
	{
		$group_rank_data = array(
			'title'		=> null,
			'img'		=> null,
			'img_src'	=> null,
		);

		/**
		 * Preparing a group's rank before displaying
		 *
		 * @event core.get_group_rank_before
		 * @var	array	group_data		Array with group's data
		 * @since 3.2.5-RC1
		 */

		$vars = array('group_data');
		extract($this->phpbb_dispatcher->trigger_event('core.get_group_rank_before', compact($vars)));

		if (!empty($group_data['group_rank']))
		{
			// Only obtain ranks if group rank is set
			$ranks = $this->cache->obtain_ranks();

			if (isset($ranks['special'][$group_data['group_rank']]))
			{
				$rank = $ranks['special'][$group_data['group_rank']];

				$group_rank_data['title'] = $rank['rank_title'];

				$group_rank_data['img_src'] = (!empty($rank['rank_image'])) ? $this->phpbb_path_helper->update_web_root_path($this->phpbb_root_path . $this->config['ranks_path'] . '/' . $rank['rank_image']) : '';

				$group_rank_data['img'] = (!empty($rank['rank_image'])) ? '<img src="' . $group_rank_data['img_src'] . '" alt="' . $rank['rank_title'] . '" title="' . $rank['rank_title'] . '" />' : '';
			}
		}

		/**
		 * Modify a group's rank before displaying
		 *
		 * @event core.get_group_rank_after
		 * @var	array	group_data		Array with group's data
		 * @var	array	group_rank_data	Group rank data
		 * @since 3.2.5-RC1
		 */

		$vars = array(
			'group_data',
			'group_rank_data',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.get_group_rank_after', compact($vars)));

		return $group_rank_data;
	}

	/**
	 * Get group avatar.
	 * Wrapper function for phpbb_get_group_avatar()
	 *
	 * @param array		$group_row		Row from the groups table
	 * @param string	$alt			Optional language string for alt tag within image, can be a language key or text
	 * @param bool		$ignore_config	Ignores the config-setting, to be still able to view the avatar in the UCP
	 * @param bool		$lazy			If true, will be lazy loaded (requires JS)
	 *
	 * @return string 					Avatar html
	 */
	function get_avatar($group_row, $alt = 'GROUP_AVATAR', $ignore_config = false, $lazy = false)
	{
		return phpbb_get_group_avatar($group_row, $alt, $ignore_config, $lazy);
	}
}
