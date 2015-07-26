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
		$this->assertContains('This is a test topic posted by the testing framework.', $crawler->filter('html')->text());

		// Test creating a reply with bbcode
		$post2 = $this->create_post(2, $post['topic_id'], 'Re: Test Topic 1', 'This is a test [b]post[/b] posted by the testing framework.');

		$crawler = self::request('GET', "viewtopic.php?t={$post2['topic_id']}&sid={$this->sid}");
		$this->assertContains('This is a test post posted by the testing framework.', $crawler->filter('html')->text());

		// Test quoting a message
		$crawler = self::request('GET', "posting.php?mode=quote&f=2&t={$post2['topic_id']}&p={$post2['post_id']}&sid={$this->sid}");
		$this->assertContains('This is a test post posted by the testing framework.', $crawler->filter('html')->text());
	}

	public function test_unsupported_characters()
	{
		$this->login();

		$this->add_lang('posting');

		self::create_post(2,
			1,
			"Unsupported: \xF0\x9F\x88\xB3 \xF0\x9F\x9A\xB6",
			'This is a test with emoji characters in the topic title.',
			array(),
			'Your subject contains the following unsupported characters'
		);
	}

	public function test_supported_unicode_characters()
	{
		$this->login();

		$post = $this->create_topic(2, 'Test Topic 1', 'This is a test topic posted by the testing framework.');
		$this->create_post(2, $post['topic_id'], 'Re: Test Topic 1', "This is a test with these weird characters: \xF0\x9F\x84\x90 \xF0\x9F\x84\x91");
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertContains("\xF0\x9F\x84\x90 \xF0\x9F\x84\x91", $crawler->text());
	}

	public function test_html_entities()
	{
		$this->login();

		$post = $this->create_topic(2, 'Test Topic 1', 'This is a test topic posted by the testing framework.');
		$this->create_post(2, $post['topic_id'], 'Re: Test Topic 1', '&#128512;');
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertContains('&#128512;', $crawler->text());
	}

	public function test_quote()
	{
		$text     = 'Test post </textarea>"\' &&amp;amp;';
		$expected = "(\\[quote=admin[^\\]]*\\]\n" . preg_quote($text) . "\n\\[/quote\\])";

		$this->login();
		$topic = $this->create_topic(2, 'Test Topic 1', 'Test topic');
		$post  = $this->create_post(2, $topic['topic_id'], 'Re: Test Topic 1', $text);

		$crawler = self::request('GET', "posting.php?mode=quote&f=2&t={$post['topic_id']}&p={$post['post_id']}&sid={$this->sid}");

		$this->assertRegexp($expected, $crawler->filter('textarea#message')->text());
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
		$quote_url = "posting.php?mode=quote&f=2&t={$post['topic_id']}&p={$post['post_id']}&sid={$this->sid}";

		$this->admin_login();
		foreach ($expected as $quote_depth => $expected_text)
		{
			$this->set_quote_depth($quote_depth);
			$crawler = self::request('GET', $quote_url);
			$this->assertRegexp(
				"(\\[quote=admin[^\\]]*\\]\n?" . preg_quote($expected_text) . "\n?\\[/quote\\])",
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

			$post = $this->create_post(2, $topic['topic_id'], 'Re: Test Topic 1', $text);
			$url  = "viewtopic.php?p={$post['post_id']}&sid={$this->sid}";

			$crawler = self::request('GET', $url);
			$text_content = $crawler->filter('#p' . $post['post_id'])->text();
			foreach ($contains[$quote_depth] as $contains_text)
			{
				$this->assertContains($contains_text, $text_content);
			}
			foreach ($not_contains[$quote_depth] as $not_contains_text)
			{
				$this->assertNotContains($not_contains_text, $text_content);
			}
		}
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
		$this->assertContains(
			'<span style="font-weight: bold">My signature</span>',
			$crawler->filter('#preview .signature')->html()
		);
	}
}
