<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../mock/messenger.php';

class phpbb_message_test extends phpbb_test_case
{
	public function test_send_one_recipient()
	{
		$message = new phpbb_message('board_contact', 'server_name');

		$message->set_subject('subject');
		$message->set_body('body');
		$message->set_template('template_file');
		$message->set_template_vars(array('foo' => 'bar'));

		$message->add_recipient_from_user_row(array(
			'username' => 'r_username',
			'user_email' => 'r_email',
			'user_lang' => 'r_lang',
			'user_jabber' => 'r_jabber',
			'user_notify_type' => NOTIFY_EMAIL,
		));

		$message->set_sender('123.4.5.6', 's_name', 's_email', 's_lang');

		$messenger = new phpbb_mock_messenger;

		$message->send($messenger);

		$this->assertEquals(array(
			'BOARD_CONTACT' => 'board_contact',
			'TO_USERNAME' => 'r_username',
			'FROM_USERNAME' => 's_name',
			'MESSAGE'	=> 'body',
			'foo'	=> 'bar',
		), $messenger->get_vars());

		$this->assertEquals(array(
			'X-AntiAbuse: Board servername - server_name',
			'X-AntiAbuse: User IP - 123.4.5.6',
		), $messenger->get_extra_headers());
	}
}
