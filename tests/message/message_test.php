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

global $phpbb_root_path, $phpEx;
require_once $phpbb_root_path . 'includes/functions_messenger.' . $phpEx;

class phpbb_message_test_messenger extends \messenger
{
	public $replyto = [];

	public function __construct()
	{
	}

	public function template($template_file, $template_lang = '', $template_path = '', $template_dir_prefix = '')
	{
	}

	public function to($address, $realname = '')
	{
	}

	public function im($address, $realname = '')
	{
	}

	public function replyto($address)
	{
		$this->replyto[] = $address;
	}

	public function headers($headers)
	{
	}

	public function subject($subject = '')
	{
	}

	public function assign_vars($vars)
	{
	}

	public function send($method = NOTIFY_EMAIL, $break = false)
	{
	}
}

class phpbb_message_test extends phpbb_test_case
{
	public function test_replyto_not_set_when_recipient_is_sender()
	{
		$message = new \phpbb\message\message('example.com');
		$message->set_sender('127.0.0.1', 'sender', 'sender@example.com', 'en', 2, 'sender');
		$message->add_recipient('recipient', 'recipient@example.com', 'en');
		$message->cc_sender();

		$messenger = new phpbb_message_test_messenger();
		$message->send($messenger, 'board@example.com');

		// replyto() must only be called for the recipient, not for the CC to the sender
		$this->assertSame(['sender@example.com'], $messenger->replyto);
	}

	public function test_replyto_not_set_when_recipient_equals_sender()
	{
		$message = new \phpbb\message\message('example.com');
		$message->set_sender('127.0.0.1', 'sender', 'sender@example.com', 'en', 2, 'sender');
		$message->add_recipient('sender', 'sender@example.com', 'en');

		$messenger = new phpbb_message_test_messenger();
		$message->send($messenger, 'board@example.com');

		// replyto() must not be called when the recipient is the sender
		$this->assertSame([], $messenger->replyto);
	}
}
