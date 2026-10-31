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

/**
* @group functional
*/
class phpbb_functional_report_notification_test extends phpbb_functional_test_case
{
	public function test_post_report_notification_history()
	{
		$this->add_lang('mcp');
		$this->create_user('report_link_user');
		$this->login('report_link_user');
		$first_id = $this->submit_report('report.php?p=1', 'First exact report');
		$this->logout();
		$this->login();

		$original_url = $this->notification_url('report_post', $first_id);
		$this->assertStringContainsString('p=1', $original_url);
		$this->assert_details($original_url, $first_id, 'First exact report', false);
		$this->close_report($original_url);
		$this->assert_details($original_url, $first_id, 'First exact report', true);

		$legacy_url = 'mcp.php?p=1&i=reports&mode=report_details';
		$crawler = self::request('GET', $legacy_url);
		$this->assertStringContainsString($this->lang('REPORT_CLOSED'), $crawler->text());
		$this->assertCount(0, $crawler->filter('input[name="report_id_list[]"]'));

		$this->logout();
		$this->login('report_link_user');
		$second_id = $this->submit_report('report.php?p=1', 'Second exact report');
		$this->assertNotEquals($first_id, $second_id);
		$this->logout();
		$this->login();

		$second_url = $this->notification_url('report_post', $second_id);
		$this->assert_details($second_url, $second_id, 'Second exact report', false);
		$this->assert_details($original_url, $first_id, 'First exact report', true);
		$this->assert_details($legacy_url, $second_id, 'Second exact report', false);

		$crawler = self::request('GET', 'mcp.php?p=1&i=reports&mode=report_details&r=2147483647');
		$this->assertStringContainsString($this->lang('NO_REPORT'), $crawler->text());
		$this->assertCount(0, $crawler->filter('input[name="report_id_list[]"]'));

		$this->logout();
		$this->login('report_link_user');
		$crawler = self::request('GET', 'mcp.php?p=1&i=reports&mode=report_details&r=' . $first_id, array(), false);
		$this->assertStringNotContainsString('First exact report', $crawler->text());
		$this->assertCount(0, $crawler->filter('input[name="report_id_list[]"]'));
		$this->logout();
		$this->login();

		$crawler = self::request('GET', $original_url);
		$crawler = self::submit($crawler->selectButton($this->lang('DELETE_REPORT'))->form());
		self::submit($crawler->selectButton($this->lang('YES'))->form());
		$crawler = self::request('GET', $original_url);
		$this->assertStringContainsString($this->lang('NO_REPORT'), $crawler->text());
		$this->assertCount(0, $crawler->filter('input[name="report_id_list[]"]'));

		// Leave the shared fixture post available to subsequent report tests.
		$this->close_report($second_url);
	}

	public function test_pm_report_notification_after_close()
	{
		$this->add_lang(array('mcp', 'ucp', 'posting'));
		$this->create_user('pm_report_link_user');
		$this->login('pm_report_link_user');
		$crawler = self::request('GET', 'ucp.php?i=pm&mode=compose');
		$crawler = self::submit($crawler->selectButton($this->lang('ADD'))->form(), array('username_list' => 'admin'));
		$crawler = self::submit($crawler->selectButton($this->lang('SUBMIT'))->form(), array(
			'subject' => 'Exact PM report subject',
			'message' => 'Exact PM report message',
		));
		$this->assertStringContainsString($this->lang('MESSAGE_STORED'), $crawler->text());

		$db = $this->get_db();
		$result = $db->sql_query("SELECT msg_id FROM phpbb_privmsgs WHERE message_subject = 'Exact PM report subject'");
		$pm_id = (int) $db->sql_fetchfield('msg_id');
		$db->sql_freeresult($result);
		$this->assertGreaterThan(0, $pm_id);

		$report_id = $this->submit_report('report.php?pm=' . $pm_id, 'Exact private message report');
		$this->logout();
		$this->login();
		$url = $this->notification_url('report_pm', $report_id);
		$this->assert_details($url, $report_id, 'Exact private message report', false);
		$this->close_report($url);
		$this->assert_details($url, $report_id, 'Exact private message report', true);

		$this->logout();
		$this->login('pm_report_link_user');
		$crawler = self::request('GET', 'mcp.php?i=pm_reports&mode=pm_report_details&r=' . $report_id, array(), false);
		$this->assertStringNotContainsString('Exact private message report', $crawler->text());
		$this->assertCount(0, $crawler->filter('input[name="report_id_list[]"]'));
	}

	/**
	* Submit a real report and return its database id.
	*/
	protected function submit_report($url, $text)
	{
		$crawler = self::request('GET', $url);
		$crawler = self::submit($crawler->selectButton($this->lang('SUBMIT'))->form(), array('report_text' => $text));
		$this->assertStringContainsString($this->lang(strpos($url, 'pm=') === false ? 'POST_REPORTED_SUCCESS' : 'PM_REPORTED_SUCCESS'), $crawler->text());

		$db = $this->get_db();
		parse_str(parse_url($url, PHP_URL_QUERY), $parameters);
		$where = isset($parameters['pm']) ? 'pm_id = ' . (int) $parameters['pm'] : 'post_id = ' . (int) $parameters['p'];
		$result = $db->sql_query_limit('SELECT report_id, report_text FROM phpbb_reports WHERE ' . $where . ' ORDER BY report_id DESC', 1);
		$report = $db->sql_fetchrow($result);
		$this->assertSame($text, $report['report_text']);
		$report_id = (int) $report['report_id'];
		$db->sql_freeresult($result);
		$this->assertGreaterThan(0, $report_id);
		return $report_id;
	}

	/**
	* Verify persisted notification identity and return its rendered URL.
	*/
	protected function notification_url($type, $report_id)
	{
		$db = $this->get_db();
		$result = $db->sql_query("SELECT n.* FROM phpbb_notifications n, phpbb_notification_types t
			WHERE n.notification_type_id = t.notification_type_id
				AND t.notification_type_name = 'notification.type." . $db->sql_escape($type) . "'
				AND n.user_id = 2");
		$rows = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}
		$db->sql_freeresult($result);
		$this->assertCount(1, $rows);
		if ($type === 'report_post')
		{
			$data = unserialize($rows[0]['notification_data']);
			$this->assertEquals($report_id, $data['report_id']);
			$this->assertEquals(1, $rows[0]['item_id']);
			$this->assertEquals(1, $rows[0]['item_parent_id']);
		}
		else
		{
			$this->assertEquals($report_id, $rows[0]['item_parent_id']);
		}

		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_list');
		if ($type === 'report_post')
		{
			// Unread notifications first mark the item read, then redirect to its destination.
			$unread = $crawler->filter('a[href*="mark_notification=' . $rows[0]['notification_id'] . '&"], a[href*="/mark-read/' . $rows[0]['notification_id'] . '?"]');
			$this->assertGreaterThan(0, $unread->count());
			$redirected = self::request('GET', preg_replace('#^.*?(index\\.php)#', '$1', html_entity_decode($unread->first()->attr('href'), ENT_COMPAT)));
			$this->assertSame((string) $report_id, $redirected->filter('input[name="report_id_list[]"]')->attr('value'));
		}
		else
		{
			$mark_all = $crawler->selectLink($this->lang('NOTIFICATIONS_MARK_ALL_READ'))->first()->link()->getUri();
			self::request('GET', substr($mark_all, strpos($mark_all, 'ucp.php')));
		}
		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_list');
		$links = $crawler->filter('a[href*="mode=' . ($type === 'report_post' ? 'report_details' : 'pm_report_details') . '"]');
		$this->assertGreaterThan(0, $links->count());
		$url = html_entity_decode($links->first()->attr('href'), ENT_COMPAT);
		$this->assertStringContainsString('r=' . $report_id, $url);
		return preg_replace('#^.*?(mcp\\.php)#', '$1', $url);
	}

	/**
	* Assert exact report identity and the existing closed/open controls.
	*/
	protected function assert_details($url, $report_id, $text, $closed)
	{
		$crawler = self::request('GET', $url);
		$this->assertSame((string) $report_id, $crawler->filter('input[name="report_id_list[]"]')->attr('value'));
		$this->assertStringContainsString($text, $crawler->filter('.postbody')->text());
		$this->assertCount($closed ? 0 : 1, $crawler->selectButton($this->lang('CLOSE_REPORT')));
	}

	/**
	* Close a report through MCP's existing confirmation form.
	*/
	protected function close_report($url)
	{
		$crawler = self::request('GET', $url);
		$crawler = self::submit($crawler->selectButton($this->lang('CLOSE_REPORT'))->form());
		self::submit($crawler->selectButton($this->lang('YES'))->form());
	}
}
