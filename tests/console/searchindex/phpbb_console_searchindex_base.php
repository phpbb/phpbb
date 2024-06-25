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

use phpbb\config\config;
use phpbb\di\service_collection;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\posting\post_helper;
use phpbb\search\search_backend_factory;
use phpbb\search\state_helper;
use phpbb\user;

require_once __DIR__ . '/../../mock/search_backend_mock.php';
require_once __DIR__ . '/../../mock/search_backend_mock_not_available.php';

class phpbb_console_searchindex_base extends phpbb_test_case
{
	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var log */
	protected $log;

	/** @var post_helper */
	protected $post_helper;

	/** @var user */
	protected $user;

	/** @var search_backend_factory */
	protected $search_backend_factory;

	/** @var state_helper */
	protected $state_helper;

	/** @var service_collection */
	protected $search_backend_collection;

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		$this->config = new \phpbb\config\config([
			'search_indexing_state' => [],
			'search_type' => 'search_backend_mock'
		]);

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->language = new \phpbb\language\language($lang_loader);

		$this->log = $this->createMock('\phpbb\log\log');

		$this->post_helper = $this->createMock('\phpbb\posting\post_helper');
		$this->post_helper
			->method('get_max_post_id')
			->willReturn(1000);

		$this->user = $this->createMock('\phpbb\user');

		$phpbb_container = new phpbb_mock_container_builder();
		$this->search_backend_collection = new \phpbb\di\service_collection($phpbb_container);

		$search_backend_mock = new search_backend_mock();
		$this->search_backend_collection->add('search_backend_mock');
		$this->search_backend_collection->add_service_class('search_backend_mock', 'search_backend_mock');
		$phpbb_container->set('search_backend_mock', $search_backend_mock);

		$search_backend_mock_not_available = new search_backend_mock_not_available();
		$this->search_backend_collection->add('search_backend_mock_not_available');
		$this->search_backend_collection->add_service_class('search_backend_mock_not_available', 'search_backend_mock_not_available');
		$phpbb_container->set('search_backend_mock_not_available', $search_backend_mock_not_available);

		$this->search_backend_factory = new search_backend_factory($this->config, $this->search_backend_collection);

		$this->state_helper = new state_helper($this->config, $this->search_backend_factory);

		parent::setUp();
	}
}

