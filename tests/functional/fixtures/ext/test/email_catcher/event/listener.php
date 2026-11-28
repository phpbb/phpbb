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

namespace test\email_catcher\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'core.phpbb_mail_before'			=> 'capture_email',
			'core.notification_message_process'	=> 'capture_queued_email',
		];
	}

	/**
	 * Capture an email sent without using the messenger queue.
	 *
	 * @param \phpbb\event\data $event Event data
	 * @return void
	 */
	public function capture_email($event): void
	{
		$this->write_email($event['email']);
	}

	/**
	 * Capture an email sent from the messenger queue.
	 *
	 * The message is not passed to the mailer afterwards because the queued
	 * message already contains all headers and the queue is only used to defer
	 * the actual transport call.
	 *
	 * @param \phpbb\event\data $event Event data
	 * @return void
	 */
	public function capture_queued_email($event): void
	{
		$this->write_email($event['email']);
		$event['break'] = true;
	}

	/**
	 * Write the email to the capture file.
	 *
	 * @param mixed $email Symfony Email object
	 * @return void
	 */
	protected function write_email($email): void
	{
		if (!($email instanceof Email))
		{
			return;
		}

		$data = [
			'to'			=> $this->get_addresses($email->getTo()),
			'cc'			=> $this->get_addresses($email->getCc()),
			'bcc'			=> $this->get_addresses($email->getBcc()),
			'from'			=> $this->get_addresses($email->getFrom()),
			'reply_to'		=> $this->get_addresses($email->getReplyTo()),
			'subject'		=> $email->getSubject(),
			'text'			=> $this->get_body($email->getTextBody()),
			'html'			=> $this->get_body($email->getHtmlBody()),
			'headers'		=> $email->getHeaders()->toString(),
			'raw'			=> $email->toString(),
		];

		// The extension is located in <phpbb_root>/ext/test/email_catcher/event.
		$file = dirname(__DIR__, 4) . '/store/email_catcher.jsonl';
		$directory = dirname($file);

		if (!is_dir($directory))
		{
			@mkdir($directory, 0777, true);
		}

		$encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

		if ($encoded !== false)
		{
			file_put_contents($file, $encoded . "\n", FILE_APPEND | LOCK_EX);
		}
	}

	/**
	 * Convert a list of Symfony addresses to an array.
	 *
	 * @param Address[] $addresses Addresses
	 * @return array
	 */
	protected function get_addresses(array $addresses): array
	{
		return array_map(static function (Address $address): array
		{
			return [
				'address'	=> $address->getAddress(),
				'name'		=> $address->getName(),
			];
		}, $addresses);
	}

	/**
	 * Get a body as a string.
	 *
	 * @param string|resource|null $body Email body
	 * @return string|null
	 */
	protected function get_body($body): ?string
	{
		if (is_resource($body))
		{
			$body = stream_get_contents($body);

			if ($body === false)
			{
				return null;
			}
		}

		return $body;
	}
}
