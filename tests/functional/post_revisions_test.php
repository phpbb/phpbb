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
class phpbb_functional_post_revisions_test extends phpbb_functional_test_case
{
	public function test_create_revision()
	{
		// Ensure the post revision tracking system is enabled
		$this->get_db()->sql_query("UPDATE phpbb_config SET config_value = '1' WHERE config_name = 'track_post_revisions'");

		$this->login();

		// Test editing a topic
		$post = $this->edit_post(2, 1, 'Edited post title', 'I am a post that has been edited by the test framework.');

		// First make sure the edit actually happened
		$crawler = $this->request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertContains('I am a post that has been edited by the test framework.', $crawler->filter('html')->text());

		// Now make sure a revision was created
		$crawler = $this->request('GET', "app.php?controller=post/1/revisions&sid={$this->sid}");
		$revisions = 0;
		// Count the number of revision rows. Even though the post has been
		// revised only once, the original version of the post is considered
		// to be a revision, so the original plus the new version make two
		$crawler->filter('li.revision_id')->each(function() use (&$revisions) {
			$revisions++;
		});
		$this->assertEquals($revisions, 2);

	}

	/**
	* Edits a post
	*
	* Be sure to login before creating
	*
	* @param int $forum_id
	* @param int $post_id
	* @param string $subject
	* @param string $message
	* @param array $additional_form_data Any additional form data to be sent in the request
	* @return array post_id, post_id
	*/
	public function edit_post($forum_id, $post_id, $subject, $message, $additional_form_data = array())
	{
		$posting_url = "posting.php?mode=edit&f={$forum_id}&p={$post_id}&sid={$this->sid}";

		$form_data = array_merge(array(
			'subject'		=> $subject,
			'message'		=> $message,
			'post'			=> true,
		), $additional_form_data);

		return $this->submit_post($posting_url, 'EDIT_POST', $form_data);
	}
}
