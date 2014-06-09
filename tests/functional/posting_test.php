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

		$crawler = self::request('GET', "posting.php?mode=reply&f=2&t=1&sid={$this->sid}");

		$form = $crawler->selectButton('Submit')->form();

		$hidden_fields = array(
			$crawler->filter('[type="hidden"]')->each(function ($node, $i) {
				return array('name' => $node->attr('name'), 'value' => $node->attr('value'));
			}),
		);

		foreach ($hidden_fields as $fields)
		{
			foreach($fields as $field)
			{
				$form_data[$field['name']] = $field['value'];
			}
		}

		// Bypass time restriction that said that if the lastclick time (i.e. time when the form was opened)
		// is not at least 2 seconds before submission, cancel the form
		$form_data['lastclick'] = 0;

		$form_data += array(
			'subject'		=> 'Unsupported characters',
			'message'		=> 'This is a test with these weird characters: 👅👅👅',
			'post'			=> true,
		);
		$crawler = self::request('POST', "posting.php?mode=reply&f=2&t=1&sid={$this->sid}", $form_data);

		$this->assertContains('Your message contains the following unsupported characters', $crawler->text());
	}
}
