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
class phpbb_functional_posting_test extends phpbb_functional_test_case
{
	public function test_post_new_topic()
	{
		$this->login();

		// Test creating topic
		$post = $this->create_topic(2, 'Test Topic 1', 'This is a test topic posted by the testing framework.');

		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertStringContainsString('This is a test topic posted by the testing framework.', $crawler->filter('html')->text());

		// Test creating a reply with bbcode
		$post2 = $this->create_post(2, $post['topic_id'], 'Re: Test Topic 1', 'This is a test [b]post[/b] posted by the testing framework.');

		$crawler = self::request('GET', "viewtopic.php?p={$post2['post_id']}&sid={$this->sid}");
		$this->assertStringContainsString('This is a test post posted by the testing framework.', $crawler->filter('html')->text());

		// Test quoting a message
		$crawler = self::request('GET', "posting.php?mode=quote&p={$post2['post_id']}&sid={$this->sid}");
		$this->assertStringContainsString('This is a test post posted by the testing framework.', $crawler->filter('html')->text());
	}

	public function test_unsupported_characters()
	{
		$this->login();

		$post = $this->create_topic(2, "Test Topic \xF0\x9F\xA4\x94 3\xF0\x9D\x94\xBB\xF0\x9D\x95\x9A", 'This is a test with emoji character in the topic title.');
		$this->create_post(2, $post['topic_id'], "Re: Test Topic 1 \xF0\x9F\xA4\x94 3\xF0\x9D\x94\xBB\xF0\x9D\x95\x9A", 'This is a test with emoji characters in the topic title.');
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertStringContainsString("\xF0\x9F\xA4\x94 3\xF0\x9D\x94\xBB\xF0\x9D\x95\x9A", $crawler->text());
	}

	public function test_supported_unicode_characters()
	{
		$this->login();

		$post = $this->create_topic(2, 'Test Topic 1', 'This is a test topic posted by the testing framework.');
		$this->create_post(2, $post['topic_id'], 'Re: Test Topic 1', "This is a test with these weird characters: \xF0\x9F\x84\x90 \xF0\x9F\x84\x91");
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertStringContainsString("\xF0\x9F\x84\x90 \xF0\x9F\x84\x91", $crawler->text());
	}

	public function test_html_entities()
	{
		$this->login();

		$post = $this->create_topic(2, 'Test Topic 1', 'This is a test topic posted by the testing framework.');
		$this->create_post(2, $post['topic_id'], 'Re: Test Topic 1', '&#128512;');
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertStringContainsString('&#128512;', $crawler->text());
	}

	public function test_quote()
	{
		$text     = 'Test post </textarea>"\' &&amp;amp;';
		$expected = "(\[quote=admin[^\]]*\]\s?" . preg_quote($text) . "\s?\[\/quote\])";

		$this->login();
		$topic = $this->create_topic(2, 'Test Topic 1', 'Test topic');
		$post  = $this->create_post(2, $topic['topic_id'], 'Re: Test Topic 1', $text);

		$crawler = self::request('GET', "posting.php?mode=quote&p={$post['post_id']}&sid={$this->sid}");

		$this->assertMatchesRegularExpression($expected, $crawler->filter('textarea#message')->text());
	}

	/**
	 * @see https://tracker.phpbb.com/browse/PHPBB3-14962
	 */
	public function test_edit()
	{
		$this->login();
		$this->create_topic(2, 'Test Topic post', 'Test topic post');

		$url =  self::$client->getCrawler()->selectLink('Edit')->link()->getUri();
		$post_id = $this->get_parameter_from_link($url, 'p');
		$crawler = self::request('GET', "posting.php?mode=edit&p={$post_id}&sid={$this->sid}");
		$form = $crawler->selectButton('Submit')->form();
		$form->setValues(array('message' => 'Edited post'));
		$crawler = self::submit($form);

		$this->assertStringContainsString('Edited post', $crawler->filter("#post_content{$post_id} .content")->text());
	}

	/**
	* @testdox max_quote_depth is applied to the text populating the posting form
	*/
	public function test_quote_depth_form()
	{
		$text = '0[quote]1[quote]2[/quote]1[/quote]0';
		$expected = array(
			0 => '0[quote]1[quote]2[/quote]1[/quote]0',
			1 => '00',
			2 => '0[quote]11[/quote]0',
			3 => '0[quote]1[quote]2[/quote]1[/quote]0',
		);

		$this->login();
		$topic = $this->create_topic(2, 'Test Topic 1', 'Test topic');
		$post  = $this->create_post(2, $topic['topic_id'], 'Re: Test Topic 1', $text);
		$quote_url = "posting.php?mode=quote&p={$post['post_id']}&sid={$this->sid}";

		$this->admin_login();
		foreach ($expected as $quote_depth => $expected_text)
		{
			$this->set_quote_depth($quote_depth);
			$crawler = self::request('GET', $quote_url);
			$this->assertMatchesRegularExpression(
				"(\[quote=admin[^\]]*\]\s?" . preg_quote($expected_text) . "\s?\[\/quote\])",
				$crawler->filter('textarea#message')->text()
			);
		}
	}

	/**
	* @testdox max_quote_depth is applied to the submitted text
	*/
	public function test_quote_depth_submit()
	{
		$text = 'depth:0[quote]depth:1[quote]depth:2[quote]depth:3[/quote][/quote][/quote]';
		$contains = array(
			0 => array('depth:0', 'depth:1', 'depth:2', 'depth:3'),
			1 => array('depth:0', 'depth:1'),
			2 => array('depth:0', 'depth:1', 'depth:2'),
			3 => array('depth:0', 'depth:1', 'depth:2', 'depth:3'),
		);
		$not_contains = array(
			0 => array(),
			1 => array('depth:2', 'depth:3'),
			2 => array('depth:3'),
			3 => array(),
		);

		$this->login();
		$this->admin_login();
		$topic = $this->create_topic(2, 'Test Topic 1', 'Test topic');

		for ($quote_depth = 0; $quote_depth <= 2; ++$quote_depth)
		{
			$this->set_quote_depth($quote_depth);

			$post = $this->create_post(2, $topic['topic_id'], "Re: Test Topic 1#$quote_depth", $text);
			$url  = "viewtopic.php?p={$post['post_id']}&sid={$this->sid}";

			$crawler = self::request('GET', $url);
			$text_content = $crawler->filter('#p' . $post['post_id'])->text();
			foreach ($contains[$quote_depth] as $contains_text)
			{
				$this->assertStringContainsString($contains_text, $text_content);
			}
			foreach ($not_contains[$quote_depth] as $not_contains_text)
			{
				$this->assertStringNotContainsString($not_contains_text, $text_content);
			}
		}
	}

	public function test_post_poll()
	{
		$this->login();

		$post = $this->create_topic(
			2,
			'[ticket/14802] Test Poll Option Spacing',
			'Empty/blank lines should not be additional poll options.',
			array('poll_title' => 'Poll Title', 'poll_option_text' => "\n A \nB\n\nC \n D\nE\n\n \n")
		);

		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertEquals('Poll Title', $crawler->filter('.poll-title')->text());
		$this->assertEquals(5, $crawler->filter('*[data-poll-option-id]')->count());
	}

	protected function set_quote_depth($depth)
	{
		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid . '&i=acp_board&mode=post');
		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();
		$values['config[max_quote_depth]'] = $depth;
		$form->setValues($values);
		$crawler = self::submit($form);
		$this->assertEquals(1, $crawler->filter('.successbox')->count());
	}

	public function test_ticket_8420()
	{
		$text = '[b][url=http://example.org] :arrow: here[/url][/b]';

		$this->login();
		$crawler = self::request('GET', 'posting.php?mode=post&f=2');
		$form = $crawler->selectButton('Preview')->form(array(
			'subject' => 'Test subject',
			'message' => $text
		));
		$crawler = self::submit($form);
		$this->assertEquals($text, $crawler->filter('#message')->text());
	}

	public function test_old_signature_in_preview()
	{
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_sig = '[b:2u8sdcwb]My signature[/b:2u8sdcwb]',
				user_sig_bbcode_uid = '2u8sdcwb',
				user_sig_bbcode_bitfield = 'QA=='
			WHERE user_id = 2";
		$this->get_db()->sql_query($sql);

		$this->login();
		$crawler = self::request('GET', 'posting.php?mode=post&f=2');
		$form = $crawler->selectButton('Preview')->form(array(
			'subject' => 'Test subject',
			'message' => 'My post',
		));
		$crawler = self::submit($form);
		$this->assertStringContainsString(
			'<strong class="text-strong">My signature</strong>',
			$crawler->filter('#preview .signature')->html()
		);
	}

	/**
	* @ticket PHPBB3-10628
	*/
	public function test_www_links_preview()
	{
		$text = 'www.example.org';
		$url  = 'http://' . $text;

		$this->add_lang('posting');
		$this->login();

		$crawler = self::request('GET', 'posting.php?mode=post&f=2');
		$form = $crawler->selectButton('Preview')->form(array(
			'subject' => 'Test subject',
			'message' => $text
		));
		$crawler = self::submit($form);

		// Test that the textarea remains unchanged
		$this->assertEquals($text, $crawler->filter('#message')->text());

		// Test that the preview contains the correct link
		$this->assertEquals($url, $crawler->filter('#preview a')->attr('href'));
	}

	public function test_allowed_schemes_links()
	{
		$text = 'http://example.org/ tcp://localhost:22/ServiceName';

		$this->login();
		$this->admin_login();

		// Post with default settings
		$crawler = self::request('GET', 'posting.php?mode=post&f=2');
		$form = $crawler->selectButton('Preview')->form(array(
			'subject' => 'Test subject',
			'message' => $text,
		));
		$crawler = self::submit($form);
		$this->assertStringContainsString(
			'<a href="http://example.org/" class="postlink">http://example.org/</a> tcp://localhost:22/ServiceName',
			$crawler->filter('#preview .content')->html()
		);

		// Update allowed schemes
		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid . '&i=acp_board&mode=post');
		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();
		$values['config[allowed_schemes_links]'] = 'https,tcp';
		$form->setValues($values);
		$crawler = self::submit($form);
		$this->assertEquals(1, $crawler->filter('.successbox')->count());

		// Post with new settings
		$crawler = self::request('GET', 'posting.php?mode=post&f=2');
		$form = $crawler->selectButton('Preview')->form(array(
			'subject' => 'Test subject',
			'message' => $text,
		));
		$crawler = self::submit($form);
		$this->assertStringContainsString(
			'http://example.org/ <a href="tcp://localhost:22/ServiceName" class="postlink">tcp://localhost:22/ServiceName</a>',
			$crawler->filter('#preview .content')->html()
		);
	}

	public function nonexistent_post_id_data()
	{
		$nonexistent_post_id = 999999; // Random value
		return [
			['edit', $nonexistent_post_id],
			['delete', $nonexistent_post_id],
			['quote', $nonexistent_post_id],
			['soft_delete', $nonexistent_post_id],
		];
	}

	/**
	 * @dataProvider nonexistent_post_id_data
	 */
	public function test_nonexistent_post_id($mode, $nonexistent_post_id)
	{
		$this->add_lang('posting');
		$this->login();
		$crawler = self::request('GET', "posting.php?mode={$mode}&p={$nonexistent_post_id}&sid={$this->sid}");
		$this->assertContainsLang('NO_POST', $crawler->text());
	}
}
