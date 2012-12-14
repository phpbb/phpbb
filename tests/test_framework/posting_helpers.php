<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_test_framework_posting_helpers
{
	protected $test_case;

	public function __construct($test_case)
	{
		$this->test_case = $test_case;
	}

	/**
	* Creates a topic
	* 
	* Be sure to login before creating
	* 
	* @param int $forum_id
	* @param string $subject
	* @param string $message
	* @param array $additional_form_data Any additional form data to be sent in the request
	* @return array post_id, topic_id
	*/
	public function create_topic($forum_id, $subject, $message, $additional_form_data = array())
	{
		$posting_url = "posting.php?mode=post&f={$forum_id}&sid={$this->test_case->get_sid()}";

		$form_data = array_merge(array(
			'subject'		=> $subject,
			'message'		=> $message,
			'post'			=> true,
		), $additional_form_data);

		return $this->submit_post($posting_url, 'POST_TOPIC', $form_data);
	}

	/**
	* Creates a post
	* 
	* Be sure to login before creating
	* 
	* @param int $forum_id
	* @param string $subject
	* @param string $message
	* @param array $additional_form_data Any additional form data to be sent in the request
	* @return array post_id, topic_id
	*/
	public function create_post($forum_id, $topic_id, $subject, $message, $additional_form_data = array())
	{
		$posting_url = "posting.php?mode=reply&f={$forum_id}&t={$topic_id}&sid={$this->test_case->get_sid()}";

		$form_data = array_merge(array(
			'subject'		=> $subject,
			'message'		=> $message,
			'post'			=> true,
		), $additional_form_data);

		return $this->submit_post($posting_url, 'POST_REPLY', $form_data);
	}
	
	/**
	* Helper for submitting posts
	* 
	* @param string $posting_url
	* @param string $posting_contains
	* @param array $form_data
	* @return array post_id, topic_id
	*/
	protected function submit_post($posting_url, $posting_contains, $form_data)
	{
		$this->test_case->add_lang('posting');

		$crawler = $this->test_case->request('GET', $posting_url);
		$this->test_case->assertContains($this->test_case->lang($posting_contains), $crawler->filter('html')->text());

		$hidden_fields = array(
			$crawler->filter('[type="hidden"]')->each(function ($node, $i) {
				return array('name' => $node->getAttribute('name'), 'value' => $node->getAttribute('value'));
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

		// I use a request because the form submission method does not allow you to send data that is not
		// contained in one of the actual form fields that the browser sees (i.e. it ignores "hidden" inputs)
		// Instead, I send it as a request with the submit button "post" set to true.
		$crawler = $this->test_case->get_client()->request('POST', $posting_url, $form_data);
		$this->test_case->assertContains($this->test_case->lang('POST_STORED'), $crawler->filter('html')->text());

		$url = $crawler->selectLink($this->test_case->lang('VIEW_MESSAGE', '', ''))->link()->getUri();
		
		$matches = $topic_id = $post_id = false;
		preg_match_all('#&t=([0-9]+)(&p=([0-9]+))?#', $url, $matches);
		
		$topic_id = (int) (isset($matches[1][0])) ? $matches[1][0] : 0;
		$post_id = (int) (isset($matches[3][0])) ? $matches[3][0] : 0;

		return array(
			'topic_id'	=> $topic_id,
			'post_id'	=> $post_id,
		);
	}
}
