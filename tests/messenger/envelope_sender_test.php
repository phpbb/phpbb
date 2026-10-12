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

class phpbb_messenger_envelope_sender_test extends phpbb_test_case
{
	/** @var \phpbb\config\config */
	protected $config;

	protected function setUp(): void
	{
		global $config, $phpbb_dispatcher, $phpbb_root_path, $phpEx, $request, $user;

		$this->config = $config = new \phpbb\config\config(array(
			'board_contact'			=> 'contact@example.com',
			'board_contact_name'	=> '',
			'board_email'			=> 'board@example.com',
			'cookie_secure'			=> false,
			'email_enable'			=> true,
			'email_package_size'	=> 10,
			'force_server_vars'		=> true,
			'script_path'			=> '/phpbb',
			'server_name'			=> 'example.com',
			'server_port'			=> 80,
			'server_protocol'		=> 'http://',
			'smtp_delivery'			=> true,
		));
		$request = new phpbb_mock_request();
		$user = (object) array('host' => '');
		$phpbb_dispatcher = new phpbb_messenger_envelope_sender_dispatcher();

		if (!class_exists('messenger'))
		{
			include $phpbb_root_path . 'includes/functions_messenger.' . $phpEx;
		}
	}

	public function test_event_sender_and_headers_are_stored_with_queued_message()
	{
		global $phpbb_dispatcher;

		$phpbb_dispatcher->envelope_sender = 'bounce@example.com';
		$queue = new phpbb_messenger_envelope_sender_queue();
		$messenger = new messenger();
		$messenger->queue = $queue;
		$messenger->to('recipient@example.com');
		$messenger->subject('Subject');
		$messenger->msg = 'Message';

		$this->assertTrue($messenger->msg_email());
		$this->assertCount(1, $queue->messages);
		$this->assertSame('email', $queue->messages[0]['object']);
		$this->assertSame('bounce@example.com', $queue->messages[0]['data']['envelope_sender']);
		$this->assertContains('X-Envelope-Sender-Test: present', $queue->messages[0]['data']['headers']);
		$this->assertContains('core.modify_email_envelope_sender', $phpbb_dispatcher->events);
	}

	/**
	* @dataProvider invalid_envelope_sender_data
	*/
	public function test_invalid_event_sender_falls_back_to_board_email($invalid_sender)
	{
		global $phpbb_dispatcher;

		$phpbb_dispatcher->envelope_sender = $invalid_sender;
		$queue = new phpbb_messenger_envelope_sender_queue();
		$messenger = new messenger();
		$messenger->queue = $queue;
		$messenger->to('recipient@example.com');
		$messenger->msg = 'Message';

		$this->assertTrue($messenger->msg_email());
		$this->assertSame('board@example.com', $queue->messages[0]['data']['envelope_sender']);
	}

	public function invalid_envelope_sender_data()
	{
		return array(
			'empty'			=> array(''),
			'invalid email'	=> array('not-an-email'),
			'header newline'	=> array("bounce@example.com\r\nBcc: victim@example.com"),
			'non-string'	=> array(array('bounce@example.com')),
			'null'			=> array(null),
		);
	}

	public function test_transport_parameters_remain_optional()
	{
		$smtp = new ReflectionFunction('smtpmail');
		$local_mail = new ReflectionFunction('phpbb_mail');

		$this->assertSame(4, $smtp->getNumberOfRequiredParameters());
		$this->assertSame(6, $smtp->getNumberOfParameters());
		$this->assertSame(6, $local_mail->getNumberOfRequiredParameters());
		$this->assertSame(7, $local_mail->getNumberOfParameters());
	}

	public function test_sender_uses_phpbb_email_validation()
	{
		$sender = 'bounce&amp;tag@example.com';

		$this->assertSame($sender, messenger::get_valid_envelope_sender($sender));
	}
}

class phpbb_messenger_envelope_sender_dispatcher
{
	/** @var mixed */
	public $envelope_sender = 'board@example.com';

	/** @var string[] */
	public $events = array();

	public function trigger_event($event_name, $data)
	{
		$this->events[] = $event_name;
		if ($event_name === 'core.modify_email_envelope_sender')
		{
			$data['envelope_sender'] = $this->envelope_sender;
			$data['headers'][] = 'X-Envelope-Sender-Test: present';
		}

		return $data;
	}
}

class phpbb_messenger_envelope_sender_queue
{
	/** @var array */
	public $messages = array();

	public function put($object, $data)
	{
		$this->messages[] = array(
			'object'	=> $object,
			'data'		=> $data,
		);
	}
}
