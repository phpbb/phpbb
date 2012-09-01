<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
		$this->add_lang('posting');

		$crawler = $this->request('GET', 'posting.php?mode=post&f=2&sid=' . $this->sid);
		$this->assertContains($this->lang('POST_TOPIC'), $crawler->filter('html')->text());

		$hidden_fields = array();
		$hidden_fields[] = $crawler->filter('[type="hidden"]')->each(function ($node, $i) {
			return array('name' => $node->getAttribute('name'), 'value' => $node->getAttribute('value'));
		});

		$test_message = 'This is a test topic posted by the testing framework.';
		$form_data = array(
			'subject'		=> 'Test Topic 1',
			'message'		=> $test_message,
			'post'			=> true,
			'f'				=> 2,
			'mode'			=> 'post',
			'sid'			=> $this->sid,
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

		// I use a request because the form submission method does not allow you to send data that is not
		// contained in one of the actual form fields that the browser sees (i.e. it ignores "hidden" inputs)
		// Instead, I send it as a request with the submit button "post" set to true.
		$crawler = $this->client->request('POST', 'posting.php', $form_data);
		$this->assertContains($this->lang('POST_STORED'), $crawler->filter('html')->text());

		$crawler = $this->request('GET', 'viewtopic.php?t=2&sid=' . $this->sid);
		$this->assertContains($test_message, $crawler->filter('html')->text());
	}

	public function test_post_reply()
	{
		$this->login();
		$this->add_lang('posting');

		$crawler = $this->request('GET', 'posting.php?mode=reply&t=2&f=2&sid=' . $this->sid);
		$this->assertContains($this->lang('POST_REPLY'), $crawler->filter('html')->text());

		$hidden_fields = array();
		$hidden_fields[] = $crawler->filter('[type="hidden"]')->each(function ($node, $i) {
			return array('name' => $node->getAttribute('name'), 'value' => $node->getAttribute('value'));
		});

		$test_message = 'This is a test post posted by the testing framework.';
		$form_data = array(
			'subject'		=> 'Re: Test Topic 1',
			'message'		=> $test_message,
			'post'			=> true,
			't'				=> 2,
			'f'				=> 2,
			'mode'			=> 'reply',
			'sid'			=> $this->sid,
		);

		foreach ($hidden_fields as $fields)
		{
			foreach($fields as $field)
			{
				$form_data[$field['name']] = $field['value'];
			}
		}

		// For reasoning behind the following command, see the test_post_new_topic() test
		$form_data['lastclick'] = 0;

		// Submit the post
		$crawler = $this->client->request('POST', 'posting.php', $form_data);
		$this->assertContains($this->lang('POST_STORED'), $crawler->filter('html')->text());

		$crawler = $this->request('GET', 'viewtopic.php?t=2&sid=' . $this->sid);
		$this->assertContains($test_message, $crawler->filter('html')->text());
	}
}
