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

require_once __DIR__ . '/../mock/container_builder.php';
require_once __DIR__ . '/../test_framework/phpbb_database_test_case.php';

class phpbb_text_reparser_manager_test extends phpbb_database_test_case
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\textreparser\manager */
	protected $reparser_manager;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/config_text.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new \phpbb\config\config(array(
			'test_reparser_cron_interval'	=> 0,
			'my_reparser_cron_interval'		=> 100,
		));

		$db = $this->new_dbal();
		$this->config_text = new \phpbb\config\db_text($db, 'phpbb_config_text');

		$service_collection = new \phpbb\di\service_collection(new phpbb_mock_container_builder());
		$service_collection->add('test_reparser');
		$service_collection->add('another_reparser');
		$service_collection->add('my_reparser');

		$this->reparser_manager = new \phpbb\textreparser\manager($this->config, $this->config_text, $service_collection);
	}

	public function test_get_resume_data()
	{
		$resume_data = array(
			'test_reparser'	=> array(
				'range-min'		=> 0,
				'range-max'		=> 100,
				'range-size'	=> 50,
			),
		);
		$this->config_text->set('reparser_resume', serialize($resume_data));

		$this->assert_array_content_equals($resume_data['test_reparser'], $this->reparser_manager->get_resume_data('test_reparser'));
		$this->assertEmpty($this->reparser_manager->get_resume_data('another_reparser'));
	}

	public function test_update_resume_data()
	{
		$resume_data = array(
			'test_reparser'	=> array(
				'range-min'		=> 0,
				'range-max'		=> 100,
				'range-size'	=> 50,
			),
		);
		$this->config_text->set('reparser_resume', serialize($resume_data));

		$this->reparser_manager->update_resume_data('another_reparser', 5, 20, 10, false);
		$this->assert_array_content_equals($resume_data, unserialize($this->config_text->get('reparser_resume')));

		$this->reparser_manager->update_resume_data('test_reparser', 0, 50, 50);
		$resume_data = array(
			'test_reparser'	=> array(
				'range-min'		=> 0,
				'range-max'		=> 50,
				'range-size'	=> 50,
			),
			'another_reparser'	=> array(
				'range-min'		=> 5,
				'range-max'		=> 20,
				'range-size'	=> 10,
			),
		);
		$this->assert_array_content_equals($resume_data, unserialize($this->config_text->get('reparser_resume')));
	}

	public function test_schedule()
	{
		$this->reparser_manager->schedule('no_reparser', 21);
		$this->assertArrayNotHasKey('no_reparser_cron_interval', $this->config);

		$this->reparser_manager->schedule('another_reparser', 42);
		$this->assertArrayNotHasKey('another_reparser_cron_interval', $this->config);

		$this->reparser_manager->schedule('test_reparser', 20);
		$this->assertEquals(20, $this->config['test_reparser_cron_interval']);
	}

	public function test_schedule_all()
	{
		$this->reparser_manager->schedule_all(180);
		$this->assertEquals(180, $this->config['test_reparser_cron_interval']);
		$this->assertEquals(180, $this->config['my_reparser_cron_interval']);
		$this->assertArrayNotHasKey('another_reparser_cron_interval', $this->config);
	}
}
