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
class phpbb_functional_notification_email_types_test extends phpbb_functional_email_test_case
{
	public function test_new_topic_and_forum_notifications()
	{
		$watcher_id = $this->create_user('emailwatcher', 'emailwatcher@example.com');
		$this->create_user('emailposter', 'emailposter@example.com');
		$this->assertGreaterThan(0, $watcher_id);

		$this->enable_email_notification($watcher_id, 'notification.type.topic');
		$this->enable_email_notification($watcher_id, 'notification.type.forum');
		$this->watch_forum($watcher_id, 2);

		$topic_title = 'Email notification topic ' . uniqid();

		$this->login('emailposter');
		$topic = $this->create_topic(2, $topic_title, 'New topic body for e-mail notification test');
		$this->logout();

		$this->assert_email_sent('emailwatcher@example.com', 'New topic in', $topic_title);

		$this->clear_captured_emails();

		$this->login('emailposter');
		$this->create_post(2, $topic['topic_id'], 'Re: ' . $topic_title, 'Reply body for e-mail notification test');
		$this->logout();

		$this->assert_email_sent('emailwatcher@example.com', 'Reply in', $topic_title);
	}

	public function test_quote_notification()
	{
		$quoted_id = $this->create_user('emailquoted', 'emailquoted@example.com');
		$this->create_user('emailquoter', 'emailquoter@example.com');
		$this->assertGreaterThan(0, $quoted_id);

		$this->enable_email_notification($quoted_id, 'notification.type.quote');

		$topic_title = 'Email quote topic ' . uniqid();

		$this->login('emailquoted');
		$topic = $this->create_topic(2, $topic_title, 'Original post that will be quoted');
		$this->logout();

		$this->clear_captured_emails();

		$this->login('emailquoter');
		$this->create_post(
			2,
			$topic['topic_id'],
			'Re: ' . $topic_title,
			'[quote="emailquoted"]Original post that will be quoted[/quote] Reply with quote'
		);
		$this->logout();

		$this->assert_email_sent('emailquoted@example.com', 'Quoted in', 'emailquoter');
	}

	public function test_mention_notification()
	{
		$mentioned_id = $this->create_user('emailmentioned', 'emailmentioned@example.com');
		$this->create_user('emailmentioner', 'emailmentioner@example.com');
		$this->assertGreaterThan(0, $mentioned_id);

		$this->enable_email_notification($mentioned_id, 'notification.type.mention');
		self::set_board_config(['allow_mentions' => 1]);

		$topic_title = 'Email mention topic ' . uniqid();

		$this->login('emailmentioner');
		$this->create_topic(2, $topic_title, 'Hello @emailmentioned, this topic contains a mention.');
		$this->logout();

		$this->assert_email_sent('emailmentioned@example.com', 'Topic reply notification', 'mentioned you');
	}

	public function test_bookmark_notification()
	{
		$bookmarker_id = $this->create_user('emailbookmark', 'emailbookmark@example.com');
		$this->create_user('emailbookposter', 'emailbookposter@example.com');
		$this->assertGreaterThan(0, $bookmarker_id);

		$this->enable_email_notification($bookmarker_id, 'notification.type.bookmark');

		$topic_title = 'Email bookmark topic ' . uniqid();

		$this->login('emailbookposter');
		$topic = $this->create_topic(2, $topic_title, 'Bookmarked topic body');
		$this->logout();

		$this->bookmark_topic($bookmarker_id, $topic['topic_id']);
		$this->clear_captured_emails();

		$this->login('emailbookposter');
		$this->create_post(2, $topic['topic_id'], 'Re: ' . $topic_title, 'Reply to bookmarked topic');
		$this->logout();

		$this->assert_email_sent('emailbookmark@example.com', 'Reply in', 'bookmarked topic');
	}

	public function test_group_request_notification()
	{
		$leader_id = $this->create_user('emailgroupleader', 'emailgroupleader@example.com');
		$this->create_user('emailgroupreq', 'emailgroupreq@example.com');
		$this->assertGreaterThan(0, $leader_id);

		$this->enable_email_notification($leader_id, 'notification.type.group_request');

		$group_name = 'Email request group ' . uniqid();

		$this->login();
		$this->admin_login();
		$group_id = $this->create_group_with_leader($group_name, 'emailgroupleader');
		$this->set_group_type($group_id, GROUP_OPEN);
		$this->logout();

		$this->login('emailgroupreq');
		$this->add_lang('groups');

		$crawler = self::request('GET', 'ucp.php?i=ucp_groups&mode=membership&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, [
			'selected'	=> $group_id,
			'action'	=> 'join',
		]);
		$this->assertContainsLang('GROUP_JOIN_PENDING_CONFIRM', $crawler->text());

		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('GROUP_JOINED_PENDING', $crawler->text());
		$this->logout();

		$this->assert_email_sent('emailgroupleader@example.com', 'A request to join your group has been made', 'emailgroupreq');
	}

	public function test_post_report_and_closed_notifications()
	{
		$reporter_id = $this->create_user('emailreporter', 'emailreporter@example.com');
		$this->create_user('emailreported', 'emailreported@example.com');
		$admin_id = $this->get_user_id('admin');
		$admin_email = $this->get_admin_email();
		$this->assertGreaterThan(0, $reporter_id);

		$this->enable_email_notification($admin_id, 'notification.type.report');
		$this->enable_email_notification($reporter_id, 'notification.type.report_post_closed');

		$topic_title = 'Email report topic ' . uniqid();

		$this->login('emailreported');
		$topic = $this->create_topic(2, $topic_title, 'Reported post body');
		$this->logout();

		$this->clear_captured_emails();

		$this->login('emailreporter');
		$crawler = self::request('GET', 'index.php/post/' . $topic['post_id'] . '/report');
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['notify']->select('1');
		$form['report_text']->setValue('Reported for functional e-mail test');
		$crawler = self::submit($form);
		$this->assertContainsLang('POST_REPORTED_SUCCESS', $crawler->text());
		$this->logout();

		$this->assert_email_sent($admin_email, 'Post report', $topic_title);

		$this->clear_captured_emails();

		$this->login();
		$this->add_lang('mcp');
		$crawler = self::request('GET', 'mcp.php?i=reports&mode=report_details&p=' . $topic['post_id'] . '&sid=' . $this->sid);
		$form = $crawler->filter('#mcp_report')->selectButton($this->lang('CLOSE_REPORT'))->form();
		$crawler = self::submit($form);
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('REPORT_CLOSED_SUCCESS', $crawler->text());

		$this->assert_email_sent('emailreporter@example.com', 'Report closed', $topic_title);
	}

	public function test_pm_report_and_closed_notifications()
	{
		$reporter_id = $this->create_user('emailpmreporter', 'emailpmreporter@example.com');
		$this->create_user('emailpmsender2', 'emailpmsender2@example.com');
		$admin_id = $this->get_user_id('admin');
		$admin_email = $this->get_admin_email();
		$this->assertGreaterThan(0, $reporter_id);

		$this->enable_email_notification($admin_id, 'notification.type.report_pm');
		$this->enable_email_notification($reporter_id, 'notification.type.report_pm_closed');

		$pm_subject = 'Email report PM ' . uniqid();

		$this->login('emailpmsender2');
		$msg_id = $this->create_private_message($pm_subject, 'Reported PM body', [$reporter_id]);
		$this->logout();

		$this->clear_captured_emails();

		$this->login('emailpmreporter');
		$crawler = self::request('GET', 'index.php/pm/' . $msg_id . '/report');
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['notify']->select('1');
		$form['report_text']->setValue('Reported PM for functional e-mail test');
		$crawler = self::submit($form);
		$this->assertContainsLang('PM_REPORTED_SUCCESS', $crawler->text());
		$this->logout();

		$this->assert_email_sent($admin_email, 'Private Message report', $pm_subject);

		$this->clear_captured_emails();

		$report_id = $this->get_report_id(0, $msg_id);
		$this->assertGreaterThan(0, $report_id);

		$this->login();
		$this->add_lang('mcp');
		$crawler = self::request('GET', 'mcp.php?i=pm_reports&mode=pm_report_details&r=' . $report_id . '&sid=' . $this->sid);
		$form = $crawler->filter('#mcp_report')->selectButton($this->lang('CLOSE_REPORT'))->form();
		$crawler = self::submit($form);
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('PM_REPORT_CLOSED_SUCCESS', $crawler->text());

		$this->assert_email_sent('emailpmreporter@example.com', 'Private message report closed', $pm_subject);
	}

	public function test_topic_moderation_notifications()
	{
		$admin_id = $this->get_user_id('admin');
		$admin_email = $this->get_admin_email();
		$author_id = $this->create_user('emailmodauthor', 'emailmodauthor@example.com');
		$this->assertGreaterThan(0, $author_id);

		$this->enable_email_notification($admin_id, 'notification.type.needs_approval');
		$this->enable_email_notification($author_id, 'moderation_queue');
		$this->add_user_group('NEWLY_REGISTERED', ['emailmodauthor']);
		self::set_board_config(['flood_interval' => 0]);

		$approved_title = 'Email approved topic ' . uniqid();

		$this->login('emailmodauthor');
		$this->create_topic(2, $approved_title, 'Unapproved topic body', [], 'POST_STORED_MOD');
		$this->logout();

		$approved_topic_id = $this->get_topic_id_by_title($approved_title);
		$this->assertGreaterThan(0, $approved_topic_id);
		$this->assert_email_sent($admin_email, 'Topic needs approval', $approved_title);

		$this->clear_captured_emails();

		$this->login();
		$crawler = self::request('GET', 'viewtopic.php?t=' . $approved_topic_id . '&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('APPROVE'))->form();
		$crawler = self::submit($form);
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('TOPIC_APPROVED_SUCCESS', $crawler->text());
		$this->assert_email_sent('emailmodauthor@example.com', 'Topic approved', $approved_title);
		$this->logout();

		$disapproved_title = 'Email disapproved topic ' . uniqid();

		$this->login('emailmodauthor');
		$this->create_topic(2, $disapproved_title, 'Disapproved topic body', [], 'POST_STORED_MOD');
		$this->logout();

		$disapproved_topic_id = $this->get_topic_id_by_title($disapproved_title);
		$this->assertGreaterThan(0, $disapproved_topic_id);
		$this->clear_captured_emails();

		$this->login();
		$crawler = self::request('GET', 'viewtopic.php?t=' . $disapproved_topic_id . '&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('DISAPPROVE'))->form();
		$crawler = self::submit($form);
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('TOPIC_DISAPPROVED_SUCCESS', $crawler->text());
		$this->assert_email_sent('emailmodauthor@example.com', 'Topic disapproved', $disapproved_title);
	}

	public function test_post_moderation_notifications()
	{
		$admin_id = $this->get_user_id('admin');
		$admin_email = $this->get_admin_email();
		$author_id = $this->create_user('emailpostmod', 'emailpostmod@example.com');
		$this->assertGreaterThan(0, $author_id);

		$this->enable_email_notification($admin_id, 'notification.type.needs_approval');
		$this->enable_email_notification($author_id, 'moderation_queue');
		$this->add_user_group('NEWLY_REGISTERED', ['emailpostmod']);
		self::set_board_config(['flood_interval' => 0]);

		$topic_title = 'Email post moderation topic ' . uniqid();

		$this->login();
		$topic = $this->create_topic(2, $topic_title, 'Approved topic body');
		$this->logout();

		$approved_subject = 'Re: ' . $topic_title . ' approved ' . uniqid();
		$this->login('emailpostmod');
		$this->create_post(2, $topic['topic_id'], $approved_subject, 'Unapproved reply body', [], 'POST_STORED_MOD');
		$this->logout();

		$approved_post_id = $this->get_post_id_by_subject($approved_subject);
		$this->assertGreaterThan(0, $approved_post_id);
		$this->assert_email_sent($admin_email, 'Post needs approval', $approved_subject);

		$this->clear_captured_emails();

		$this->login();
		$crawler = self::request('GET', 'viewtopic.php?t=' . $topic['topic_id'] . '&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('APPROVE'))->form();
		$crawler = self::submit($form);
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('POST_APPROVED_SUCCESS', $crawler->text());
		$this->assert_email_sent('emailpostmod@example.com', 'Post approved', $approved_subject);
		$this->logout();

		$disapproved_subject = 'Re: ' . $topic_title . ' disapproved ' . uniqid();
		$this->login('emailpostmod');
		$this->create_post(2, $topic['topic_id'], $disapproved_subject, 'Disapproved reply body', [], 'POST_STORED_MOD');
		$this->logout();

		$disapproved_post_id = $this->get_post_id_by_subject($disapproved_subject);
		$this->assertGreaterThan(0, $disapproved_post_id);
		$this->clear_captured_emails();

		$this->login();
		$crawler = self::request('GET', 'viewtopic.php?t=' . $topic['topic_id'] . '&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('DISAPPROVE'))->form();
		$crawler = self::submit($form);
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('POST_DISAPPROVED_SUCCESS', $crawler->text());
		$this->assert_email_sent('emailpostmod@example.com', 'Post disapproved', $disapproved_subject);
	}

	public function test_update_notifications()
	{
		$admin_id = $this->get_user_id('admin');
		$admin_email = $this->get_admin_email();
		$this->enable_email_notification($admin_id, 'notification.type.update_maintenance');
		$this->login();

		$templates = [
			'update_maintenance'	=> 'update available for',
			'update_critical'		=> 'Critical update required',
			'update_security'		=> 'Security update available',
		];

		foreach ($templates as $template => $subject_contains)
		{
			$this->clear_captured_emails();

			self::request('GET', 'index.php/email-catcher/trigger?type=update&template=' . $template, [], false);

			$this->assert_email_sent($admin_email, $subject_contains, '99.0.0');
		}
	}
}
