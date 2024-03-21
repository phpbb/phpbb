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

namespace phpbb\avatar;

use phpbb\avatar\driver\driver_interface;
use phpbb\config\config;
use phpbb\event\dispatcher;
use phpbb\language\language;
use phpbb\path_helper;
use phpbb\user;

/**
 * Avatar helper object.
 *
 * Generates avatars and their variables for display.
 */
class helper
{
	/** @var config */
	protected $config;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var language */
	protected $language;

	/** @var manager */
	protected $manager;

	/** @var path_helper */
	protected $path_helper;

	/** @var user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param config		$config			Config object
	 * @param dispatcher	$dispatcher		Event dispatcher object
	 * @param language		$language		Language object
	 * @param manager		$manager		Avatar manager object
	 * @param path_helper	$path_helper	Path helper object
	 * @param user			$user			User object
	 */
	public function __construct(
		config $config,
		dispatcher $dispatcher,
		language $language,
		manager $manager,
		path_helper $path_helper,
		user $user
	)
	{
		$this->config		= $config;
		$this->dispatcher	= $dispatcher;
		$this->language		= $language;
		$this->manager		= $manager;
		$this->path_helper	= $path_helper;
		$this->user			= $user;
	}

	/**
	 * Get an avatar's template variables.
	 *
	 * @param array		$avatar			The avatar's data
	 * @param string	$prefix			The variables' prefix
	 * @return array					The avatar's template variables
	 */
	public function get_template_vars(array $avatar, string $prefix = ''): array
	{
		$prefix = $prefix && substr($prefix, -1) !== '_' ? "{$prefix}_" : $prefix;

		return [
			"{$prefix}AVATAR"			=> $avatar,

			"{$prefix}AVATAR_SOURCE"	=> $avatar['src'],
			"{$prefix}AVATAR_TITLE"		=> $avatar['title'],
			"{$prefix}AVATAR_TYPE"		=> $avatar['type'],

			"{$prefix}AVATAR_WIDTH"		=> $avatar['width'],
			"{$prefix}AVATAR_HEIGHT"	=> $avatar['height'],

			"{$prefix}AVATAR_LAZY"		=> $avatar['lazy'],
			"{$prefix}AVATAR_HTML"		=> $avatar['html'],
		];
	}

	/**
	 * Get user avatar data.
	 *
	 * @param array		$row			The user's table row
	 * @param string	$title			Optional language string/key for the title
	 * @param bool		$ignore_config	Ignores the config setting, to still be able to view the avatar in the UCP
	 * @param bool		$lazy			Indicator whether the avatar should be lazy loaded (requires JS) or not
	 * @return array					The avatar data array
	 */
	public function get_user_avatar(array $row, string $title = 'USER_AVATAR', bool $ignore_config = false, bool $lazy = false): array
	{
		$row = manager::clean_row($row, 'user');

		return $this->get_avatar($row, $title, $ignore_config, $lazy);
	}

	/**
	 * Get group avatar data.
	 *
	 * @param array		$row			The group's table row
	 * @param string	$title			Optional language string/key for the title
	 * @param bool		$ignore_config	Ignores the config setting, to still be able to view the avatar in the UCP
	 * @param bool		$lazy			Indicator whether the avatar should be lazy loaded (requires JS) or not
	 * @return array					The avatar data array
	 */
	public function get_group_avatar(array $row, string $title = 'GROUP_AVATAR', bool $ignore_config = false, bool $lazy = false): array
	{
		$row = manager::clean_row($row, 'group');

		return $this->get_avatar($row, $title, $ignore_config, $lazy);
	}

	/**
	 * Get avatar data.
	 *
	 * @param array		$row			The cleaned table row
	 * @param string	$title			Optional language string/key for the title
	 * @param bool		$ignore_config	Ignores the config setting, to still be able to view the avatar in the UCP
	 * @param bool		$lazy			Indicator whether the avatar should be lazy loaded (requires JS) or not
	 * @return array					The avatar data array
	 */
	public function get_avatar(array $row, string $title, bool $ignore_config = false, bool $lazy = false): array
	{
		if (!$this->config['allow_avatar'] && !$ignore_config)
		{
			return [
				'html'		=> '',
				'lazy'		=> false,
				'src'		=> '',
				'title'		=> '',
				'type'		=> '',
				'width'		=> 0,
				'height'	=> 0,
			];
		}

		$data = [
			'src'		=> $row['avatar'],
			'width'		=> $row['avatar_width'],
			'height'	=> $row['avatar_height'],
			'title'		=> $this->language->lang($title),
			'lazy'		=> $lazy,
			'type'		=> '',
			'html'		=> '',
		];

		/** @var driver_interface $driver */
		$driver = $this->manager->get_driver($row['avatar_type'], !$ignore_config);

		if ($driver !== null)
		{
			$data = array_merge($data, $driver->get_data($row), [
				'type'	=> $driver->get_name(),
				'html'	=> $driver->get_custom_html($this->user, $row, $title),
			]);

			/**
			 * The type is used in the template to determine what driver is used,
			 * and potentially to add an additional class to the avatar <img> element.
			 *
			 * While it's possible to str_replace('avatar.driver.', '', $data['type'])
			 * for all the core drivers, this will be awkward for extensions' avatar drivers.
			 * As they will most likely want to adjust the type in the event below,
			 * and then have to search for a different definition than they used in their services.yml
			 *
			 * For example, 'ext.vendor.avatar.driver.custom_driver'
			 * They will then have to look for: 'ext.vendor.custom_driver'
			 *
			 * So only remove 'avatar.driver.' if it is at the beginning of the driver's name.
			 */
			if (strpos($data['type'], 'avatar.driver.') === 0)
			{
				$data['type'] = substr($data['type'], strlen('avatar.driver.'));
			}
		}
		else
		{
			$data['src'] = '';
		}

		if (empty($data['html']) && !empty($data['src']))
		{
			$data['html'] = $this->get_avatar_html($data);
		}

		/**
		 * Event to modify avatar data array
		 *
		 * @event core.avatar_helper_get_avatar
		 * @var	array	row				The cleaned table row
		 * @var	string	title			The language string/key for the title
		 * @var	bool	ignore_config	Ignores the config setting, to still be able to view the avatar in the UCP
		 * @var bool	lazy			Indicator whether the avatar should be lazy loaded (requires JS) or not
		 * @var	array	data			The avatar data array
		 * @since 4.0.0
		 */
		$vars = ['row', 'title', 'ignore_config', 'lazy', 'data'];
		extract($this->dispatcher->trigger_event('core.avatar_helper_get_avatar', compact($vars)));

		return $data;
	}

	/**
	 * Get the "no avatar" source string.
	 *
	 * @return string					The "no avatar" source string
	 */
	public function get_no_avatar_source(): string
	{
		/**
		 * We need to correct the phpBB root path in case this is called from a controller,
		 * because the web path will be incorrect otherwise.
		 */
		$web_path	= $this->path_helper->get_web_root_path();
		$style_path	= rawurlencode($this->user->style['style_path']);

		return "{$web_path}styles/{$style_path}/theme/images/no_avatar.gif";
	}

	/**
	 * Get an avatar's HTML <img> element.
	 *
	 * Created for Backwards Compatibility (BC).
	 * Styles should generate their own HTML element instead.
	 *
	 * @deprecated 4.1.0				After admin style is reworked aswell
	 *
	 * @param array		$data			The avatar data array
	 * @return string					The avatar's HTML <img> element
	 */
	private function get_avatar_html(array $data): string
	{
		if ($data['lazy'])
		{
			$data['src'] = $this->get_no_avatar_source() . '" data-src="' . $data['src'];
		}

		$src = ' src="' . $data['src'] . '"';
		$alt = ' alt="' . $data['title'] . '"';

		$width = $data['width'] ? ' width="' . $data['width'] . '"' : '';
		$height = $data['height'] ? ' height="' . $data['height'] . '"' : '';

		return '<img class="avatar"' . $src . $width . $height . $alt . ' />';
	}
}
