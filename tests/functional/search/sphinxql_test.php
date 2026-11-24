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

require_once __DIR__ . '/base.php';

/**
 * @group functional
 */
class phpbb_functional_search_sphinxql_test extends phpbb_functional_search_base
{
	protected $search_backend = 'phpbb\search\backend\fulltext_sphinxql';
	protected const TEST_SPHINX_ID = 'saw9zf2fdhp1goue';

	protected function setUp(): void
	{
		parent::setUp();

		if (!extension_loaded('pdo_mysql') && !extension_loaded('mysqli'))
		{
			$this->markTestSkipped('Neither pdo_mysql nor mysqli extension is loaded.');
		}

		$socket = @fsockopen('127.0.0.1', 9306, $errno, $errstr, 1.0);
		if ($socket === false)
		{
			$this->markTestSkipped('SphinxQL searchd mysql41 listener is not running on 127.0.0.1:9306');
		}
		fclose($socket);

		$this->ensure_sphinxql_board_config();
	}

	protected function ensure_sphinxql_board_config(): void
	{
		$db = $this->get_db();
		$defaults = [
			'fulltext_sphinxql_id'				=> self::TEST_SPHINX_ID,
			'fulltext_sphinxql_host'			=> '127.0.0.1',
			'fulltext_sphinxql_port'			=> '9306',
			'fulltext_sphinxql_data_path'		=> '/var/run/sphinxsearch/',
			'fulltext_sphinxql_rt_mem_limit'	=> '128',
		];

		foreach ($defaults as $name => $value)
		{
			$sql = 'SELECT config_value
				FROM ' . CONFIG_TABLE . "
				WHERE config_name = '" . $db->sql_escape($name) . "'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($row === false)
			{
				$sql = 'INSERT INTO ' . CONFIG_TABLE . ' ' . $db->sql_build_array('INSERT', [
					'config_name'	=> $name,
					'config_value'	=> $value,
					'is_dynamic'	=> 0,
				]);
				$db->sql_query($sql);
			}
			else
			{
				$sql = 'UPDATE ' . CONFIG_TABLE . "
					SET config_value = '" . $db->sql_escape($value) . "'
					WHERE config_name = '" . $db->sql_escape($name) . "'";
				$db->sql_query($sql);
			}
		}

		$this->purge_cache();
	}

	public function test_realtime_post_lifecycle_and_visibility(): void
	{
		$this->add_lang(['common', 'search', 'posting']);
		$this->login();
		$this->admin_login();
		$this->set_flood_interval(0);

		// Activate SphinxQL backend and build initial RT index
		$crawler = self::request('GET', 'adm/index.php?i=acp_search&mode=settings&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$values = $form->getValues();
		if ($values['config[search_type]'] !== $this->search_backend)
		{
			$values['config[search_type]'] = $this->search_backend;
			$form->setValues($values);
			$crawler = self::submit($form);
			$this->purge_cache();

			$confirm_form = $crawler->selectButton($this->lang('YES'))->form();
			self::submit($confirm_form);
		}
		$this->create_search_index();

		// 1. Real-time indexing on new topic creation (no re-index required)
		$topic = $this->create_topic(2, 'SphinxQL Realtime Subject Uniquetokenalpha', 'Initial body containing uniquetokenbeta for realtime test.');
		$this->assert_search_found('uniquetokenalpha+uniquetokenbeta', 1, 2);

		// 2. Real-time update on post edit (updated text & subject immediately searchable)
		$crawler = self::request('GET', "posting.php?mode=edit&p={$topic['post_id']}&sid={$this->sid}");
		$edit_form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'subject'	=> 'SphinxQL Edited Subject Uniquetokengamma',
			'message'	=> 'Updated body containing uniquetokendelta after editing.',
		]);
		self::submit($edit_form);

		$this->assert_search_not_found('uniquetokenbeta');
		$this->assert_search_found('uniquetokengamma+uniquetokendelta', 1, 2);

		// 3. Soft-delete visibility: admin/moderator with m_approve still finds it; guest does not
		$crawler = self::request('GET', "posting.php?mode=delete&p={$topic['post_id']}&sid={$this->sid}");
		$soft_delete_form = $crawler->selectButton($this->lang('YES'))->form();
		self::submit($soft_delete_form);

		// Admin has m_approve in forum 2
		$this->assert_search_found('uniquetokendelta', 1, 1);

		// Guest does not have m_approve in forum 2 -> 0 matches
		$this->logout();
		$this->assert_search_not_found('uniquetokendelta');

		// 4. Restore topic: guest can find the post again in real time
		$this->login();
		$this->admin_login();
		$restore_crawler = $this->get_quickmod_page($topic['topic_id'], 'RESTORE_TOPIC');
		$restore_form = $restore_crawler->selectButton($this->lang('YES'))->form();
		self::submit($restore_form);

		$this->logout();
		$this->assert_search_found('uniquetokendelta', 1, 1);

		// 5. Hard delete removes document from RT index completely
		$this->login();
		$this->admin_login();
		$this->delete_topic($topic['topic_id']);
		$this->assert_search_not_found('uniquetokendelta');

		$this->delete_search_index();
		$this->set_flood_interval(15);
	}
}
