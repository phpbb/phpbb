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

class phpbb_message_test_messenger_method implements \phpbb\messenger\method\messenger_interface
{
	public array $reply_to = [];

	public function is_enabled(): bool
	{
		return true;
	}

	public function set_use_queue(bool $use_queue = true): void
	{
	}

	public function template($template_file, $template_lang = '', $template_path = '', $template_dir_prefix = ''): void
	{
	}

	public function set_addresses(array $user_row): void
	{
	}

	public function reply_to(string $address, string $realname = ''): void
	{
		$this->reply_to[] = $address;
	}

	public function header(string $header_name, mixed $header_value): void
	{
	}

	public function subject(string $subject = ''): void
	{
	}

	public function assign_vars(array $vars): void
	{
	}

	public function send(): bool
	{
		return true;
	}

	public function error(string $msg): void
	{
	}
}

class phpbb_message_test extends \phpbb_test_case
{
	protected function get_messenger_service_collection($messenger_method)
	{
		$service_collection = $this->getMockBuilder('\phpbb\di\service_collection')
			->disableOriginalConstructor()
			->onlyMethods(['getIterator'])
			->getMock();

		$service_collection->method('getIterator')
			->willReturn(new \ArrayIterator([$messenger_method]));

		return $service_collection;
	}

	public function test_replyto_not_set_when_recipient_is_sender()
	{
		$message = new \phpbb\message\message('example.com');
		$message->set_sender('127.0.0.1', 'sender', 'sender@example.com', 'en', 2, 'sender');
		$message->add_recipient('recipient', 'recipient@example.com', 'en');
		$message->cc_sender();

		$messenger_method = new phpbb_message_test_messenger_method();
		$service_collection = $this->get_messenger_service_collection($messenger_method);

		$message->send($service_collection, 'board@example.com');

		// reply_to() must only be called for the recipient, not for the CC to the sender
		$this->assertSame(['sender@example.com'], $messenger_method->reply_to);
	}

	public function test_replyto_not_set_when_recipient_equals_sender()
	{
		$message = new \phpbb\message\message('example.com');
		$message->set_sender('127.0.0.1', 'sender', 'sender@example.com', 'en', 2, 'sender');
		$message->add_recipient('sender', 'sender@example.com', 'en');

		$messenger_method = new phpbb_message_test_messenger_method();
		$service_collection = $this->get_messenger_service_collection($messenger_method);

		$message->send($service_collection, 'board@example.com');

		// reply_to() must not be called when the recipient is the sender
		$this->assertSame([], $messenger_method->reply_to);
	}
}
