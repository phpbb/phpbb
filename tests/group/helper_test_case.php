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

class phpbb_group_helper_test_case extends phpbb_test_case
{
	/** @var  \phpbb\group\helper */
	protected $group_helper;

	protected function config_defaults()
	{
		$defaults = array(
			'ranks_path' => 'images/ranks'
		);
		return $defaults;
	}

	protected function get_test_language_data_set()
	{
		return array(
			'G_BOTS'					=> 'Bots',
			'G_NEW_GROUP'				=> 'Some new group',
			'G_not_uppercase'			=> 'The key does not contain uppercase letters',
			'G_GROUP_WITH_ÜMLAUTS'		=> 'Should work',
		);
	}

	protected function get_test_rank_data_set()
	{
		return array(
			'special' => array(
				1 => array(
					'rank_id'		=> 1,
					'rank_title'	=> 'Site admin',
					'rank_special'	=> 1,
					'rank_image'	=> 'siteadmin.png',
				),
				2 => array(
					'rank_id'		=> 2,
					'rank_title'	=> 'Test member',
					'rank_special'	=> 1,
					'rank_image'	=> '',
				)
			)
		);
	}

	protected function setup_engine(array $new_config = array())
	{
		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		// Set up authentication data for testing
		$auth = $this->getMockBuilder('\phpbb\auth\auth')->disableOriginalConstructor()->getMock();
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'), $this->anything())
			->will($this->returnValueMap(array(
				array('u_viewprofile', true),
			)));

		// Set up cache service
		$cache_service = $this->getMockBuilder('\phpbb\cache\service')->disableOriginalConstructor()->getMock();
		$cache_service->expects($this->any())
			->method('obtain_ranks')
			->will($this->returnValue($this->get_test_rank_data_set()));

		// Set up configuration
		$defaults = $this->config_defaults();
		$config = new \phpbb\config\config(array_merge($defaults, $new_config));

		// Set up language service
		$lang = new \phpbb\language\language(
			new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)
		);

		// Set up language data for testing
		$reflection_class = new ReflectionClass('\phpbb\language\language');

		// Set default language files loaded flag to true
		$loaded_flag = $reflection_class->getProperty('common_language_files_loaded');
		$loaded_flag->setAccessible(true);
		$loaded_flag->setValue($lang, true);

		// Set up test language data
		$lang_array = $reflection_class->getProperty('lang');
		$lang_array->setAccessible(true);
		$lang_array->setValue($lang, $this->get_test_language_data_set());

		// Set up event dispatcher
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		// Set up path helper
		$path_helper = $this->getMockBuilder('\phpbb\path_helper')
			->disableOriginalConstructor()
			->setMethods(array())
			->getMock();
		$path_helper->method('get_phpbb_root_path')
			->willReturn($phpbb_root_path);
		$path_helper->method('get_php_ext')
			->willReturn($phpEx);
		$path_helper->method('update_web_root_path')
			->will($this->returnArgument(0));

		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->data['user_id'] = ANONYMOUS;

		$avatar_helper = $this->getMockBuilder('\phpbb\avatar\helper')
			->disableOriginalConstructor()
			->getMock();

		$this->group_helper = new \phpbb\group\helper($auth, $avatar_helper, $cache_service, $config, $lang, $phpbb_dispatcher, $path_helper, $user);
	}

	protected function setUp(): void
	{
		$this->setup_engine();
	}
}
