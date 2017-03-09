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

class phpbb_group_helper_test extends phpbb_test_case
{
	/** @var  \phpbb\group\helper */
	protected $group_helper;

	public function setUp()
	{
		global $phpbb_root_path, $phpEx;

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

		// Set up group helper
		$this->group_helper = new \phpbb\group\helper($lang);
	}

	public function test_get_name()
	{
		// They should be totally fine
		$this->assertEquals('Bots', $this->group_helper->get_name('Bots'));
		$this->assertEquals('Some new group', $this->group_helper->get_name('new_group'));
		$this->assertEquals('Should work', $this->group_helper->get_name('group_with_ümlauts'));

		// This should fail (obviously)
		$this->assertNotEquals('They key does not contain uppercase letters', $this->group_helper->get_name('not_uppercase'));

		// The key doesn't exist so just return group name...
		$this->assertEquals('Awesome group', $this->group_helper->get_name('Awesome group'));
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
}
