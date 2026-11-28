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
use Symfony\Component\Mailer\Event\FailedMessageEvent;
use Symfony\Component\Mailer\Event\SentMessageEvent;
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
			SentMessageEvent::class				=> 'capture_sent_email',
			FailedMessageEvent::class			=> 'capture_failed_email',
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
	 * Capture a message after it has been accepted by the transport.
	 *
	 * @param SentMessageEvent $event Event
	 * @return void
	 */
	public function capture_sent_email(SentMessageEvent $event): void
	{
		$sent_message = $event->getMessage();
		$original_message = $sent_message->getOriginalMessage();
		$email = $original_message instanceof Email ? $original_message : null;

		$data = [
			'envelope_from'	=> $this->get_address($sent_message->getEnvelope()->getSender()),
			'envelope_to'	=> $this->get_addresses($sent_message->getEnvelope()->getRecipients()),
			'message_id'	=> $sent_message->getMessageId(),
			'subject'		=> $email ? $email->getSubject() : null,
			'text'			=> $email ? $this->get_body($email->getTextBody()) : null,
			'html'			=> $email ? $this->get_body($email->getHtmlBody()) : null,
			'headers'		=> $email ? $email->getHeaders()->toString() : '',
			'raw'			=> $sent_message->toString(),
			'debug'			=> $sent_message->getDebug(),
		];

		$this->write_data($data, 'email_catcher_sent.jsonl');
	}

	/**
	 * Capture a message that the transport rejected.
	 *
	 * @param FailedMessageEvent $event Event
	 * @return void
	 */
	public function capture_failed_email(FailedMessageEvent $event): void
	{
		$error = $event->getError();
		$message = $event->getMessage();

		$data = [
			'error_class'	=> get_class($error),
			'error_message'	=> $error->getMessage(),
			'raw'			=> method_exists($message, 'toString') ? $message->toString() : '',
		];

		$this->write_data($data, 'email_catcher_failed.jsonl');
	}

	/**
	 * Write the generated email to the capture file.
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

		$this->write_data($data, 'email_catcher.jsonl');
	}

	/**
	 * Append a JSON record to a capture file.
	 *
	 * @param array $data Data to write
	 * @param string $filename Capture file name
	 * @return void
	 */
	protected function write_data(array $data, string $filename): void
	{
		// The extension is located in <phpbb_root>/ext/test/email_catcher/event.
		$file = dirname(__DIR__, 4) . '/store/' . $filename;
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
	 * Convert a single Symfony address to an array.
	 *
	 * @param Address $address Address
	 * @return array
	 */
	protected function get_address(Address $address): array
	{
		return [
			'address'	=> $address->getAddress(),
			'name'		=> $address->getName(),
		];
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
