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

use Symfony\Component\HttpFoundation\JsonResponse;

class phpbb_manifest_controller_test extends phpbb_test_case
{
	protected $config;
	protected $user;
	protected $manifest;

	protected function setUp(): void
	{
		global $config, $user, $phpbb_root_path, $phpEx;

		parent::setUp();

		$config = $this->config = new phpbb\config\config([
			'sitename'			=> 'phpBB Testing Framework',
			'sitename_short'	=> '',
			'force_server_vars'	=> false,
			'script_path'		=> '',
		]);

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$lang_loader = new phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$language = new phpbb\language\language($lang_loader);
		$user = $this->user = new phpbb\user($language, '\phpbb\datetime');

		$this->manifest = new phpbb\manifest($this->config, $phpbb_dispatcher, $this->user);
	}

	public static function manifest_data()
	{
		return [
			'using board url root path' => [
				[
					'force_server_vars'	=> false,
				],
				[
					'name'		=> 'phpBB Testing Framework',
					'short_name'	=> 'phpBB Testin',
					'display'		=> 'standalone',
					'orientation'	=> 'portrait',
					'start_url'		=> '/',
					'scope'			=> '/',
				],
			],
			'using script path' => [
				[
					'force_server_vars'	=> true,
					'script_path'		=> '/foo/',
				],
				[
					'name'		=> 'phpBB Testing Framework',
					'short_name'	=> 'phpBB Testin',
					'display'		=> 'standalone',
					'orientation'	=> 'portrait',
					'start_url'		=> '/foo/',
					'scope'			=> '/foo/',
				],
			],
			'with shortname' => [
				[
					'sitename_short'	=> 'phpBB Test',
				],
				[
					'name'		=> 'phpBB Testing Framework',
					'short_name'	=> 'phpBB Test',
					'display'		=> 'standalone',
					'orientation'	=> 'portrait',
					'start_url'		=> '/',
					'scope'			=> '/',
				],
			],
			'without shortname or script path' => [
				[],
				[
					'name'		=> 'phpBB Testing Framework',
					'short_name'	=> 'phpBB Testin',
					'display'		=> 'standalone',
					'orientation'	=> 'portrait',
					'start_url'		=> '/',
					'scope'			=> '/',
				],
			],
		];
	}

	/**
	 * @dataProvider manifest_data
	 */
	public function test_manifest($configs, $expected)
	{
		foreach ($configs as $key => $value)
		{
			$this->config->set($key, $value);
		}

		$response = $this->manifest->handle();

		$this->assertInstanceOf(JsonResponse::class, $response);

		$this->assertEquals($expected, json_decode($response->getContent(), true));
	}

	public static function manifest_with_bot_data()
	{
		return [
			'is a bot' => [true, 'yes'],
			'not a bot' => [false, null],
		];
	}

	/**
	 * @dataProvider manifest_with_bot_data
	 */
	public function test_manifest_with_bot($is_bot, $expected)
	{
		$this->user->data['is_bot'] = $is_bot;

		$response = $this->manifest->handle();

		$this->assertInstanceOf(JsonResponse::class, $response);

		$this->assertEquals($expected, $response->headers->get('X-PHPBB-IS-BOT'));
	}
}
