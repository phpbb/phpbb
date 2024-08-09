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

namespace phpbb\messenger\method;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as symfony_email;
use Symfony\Component\Mime\Header\Headers;

/**
 * Messenger class
 */
class email extends base
{
	/** @var array */
	private const PRIORITY_MAP = [
		symfony_email::PRIORITY_HIGHEST => 'Highest',
		symfony_email::PRIORITY_HIGH => 'High',
		symfony_email::PRIORITY_NORMAL => 'Normal',
		symfony_email::PRIORITY_LOW => 'Low',
		symfony_email::PRIORITY_LOWEST => 'Lowest',
	];

	/**
	 * @var string
	 *
	 * Symfony Mailer transport DSN
	 */
	protected $dsn = '';

	/** @var symfony_email */
	protected $email;

	/** @var Address */
	protected $from;

	/** @var Headers */
	protected $headers;

	/**
	 * @var int
	 *
	 * Possible values are:
	 * symfony_email::PRIORITY_HIGHEST
	 * symfony_email::PRIORITY_HIGH
	 * symfony_email::PRIORITY_NORMAL
	 * symfony_email::PRIORITY_LOW
	 * symfony_email::PRIORITY_LOWEST
	 */
	protected $mail_priority = symfony_email::PRIORITY_NORMAL;

	/** @var \phpbb\messenger\queue */
	protected $queue;

	/** @var Address */
	protected $reply_to;

	/** @var \Symfony\Component\Mailer\Transport\AbstractTransport */
	protected $transport;

	/**
	 * {@inheritDoc}
	 */
	public function get_id(): int
	{
		return NOTIFY_EMAIL;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_queue_object_name(): string
	{
		return 'email';
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled(): bool
	{
		return (bool) $this->config['email_enable'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function init(): void
	{
		$this->email = new symfony_email();
		$this->headers = $this->email->getHeaders();
		$this->subject =  $this->msg = '';
		$this->mail_priority = symfony_email::PRIORITY_NORMAL;

		$this->additional_headers = [];
		$this->use_queue = true;
		unset($this->template, $this->reply_to, $this->from);
	}

	/**
	 * Sets the use of messenger queue flag
	 *
	 * @return void
	 */
	public function set_use_queue(bool $use_queue = true): void
	{
		$this->use_queue = !$this->config['email_package_size'] ? false : $use_queue;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_addresses(array $user_row): void
	{
		if (isset($user_row['user_email']) && $user_row['user_email'])
		{
			$this->to($user_row['user_email'], $user_row['username'] ?: '');
		}
	}

	/**
	 * Sets email address to send to
	 *
	 * @param string	$address	Email "To" recipient address
	 * @param string	$realname	Email "To" recipient name
	 * @return void
	 */
	public function to(string $address, string $realname = ''): void
	{
		if (!$address = trim($address))
		{
			return;
		}

		// If empty sendmail_path on windows, PHP changes the to line
		$windows_empty_sendmail_path = !$this->config['smtp_delivery'] && DIRECTORY_SEPARATOR == '\\';

		$to = new Address($address, $windows_empty_sendmail_path ? '' : trim($realname));
		$this->email->getTo() ? $this->email->addTo($to) : $this->email->to($to);
	}

	/**
	 * Sets cc address to send to
	 *
	 * @param string	$address	Email carbon copy recipient address
	 * @param string	$realname	Email carbon copy recipient name
	 * @return void
	 */
	public function cc(string $address, string $realname = ''): void
	{
		if (!$address = trim($address))
		{
			return;
		}

		$cc = new Address($address, trim($realname));
		$this->email->getCc() ? $this->email->addCc($cc) : $this->email->cc($cc);
	}

	/**
	 * Sets bcc address to send to
	 *
	 * @param string	$address	Email black carbon copy recipient address
	 * @param string	$realname	Email black carbon copy recipient name
	 * @return void
	 */
	public function bcc(string $address, string $realname = ''): void
	{
		if (!$address = trim($address))
		{
			return;
		}

		$bcc = new Address($address, trim($realname));
		$this->email->getBcc() ? $this->email->addBcc($bcc) : $this->email->bcc($bcc);
	}

	/**
	 * Set the reply to address
	 *
	 * @param string	$address	Email "Reply to" address
	 * @param string	$realname	Email "Reply to" recipient name
	 * @return void
	 */
	public function reply_to(string $address, string $realname = ''): void
	{
		if (!$address = trim($address))
		{
			return;
		}

		$this->reply_to = new Address($address, trim($realname));
		$this->email->getReplyTo() ? $this->email->addReplyTo($this->reply_to) : $this->email->replyTo($this->reply_to);
	}

	/**
	 * Set the from address
	 *
	 * @param string	$address	Email "from" address
	 * @param string	$realname	Email "from" recipient name
	 * @return void
	 */
	public function from(string $address, string $realname = ''): void
	{
		if (!$address = trim($address))
		{
			return;
		}

		$this->from = new Address($address, trim($realname));
		$this->email->getFrom() ? $this->email->addFrom($this->from) : $this->email->from($this->from);
	}

	/**
	 * Set up subject for mail
	 *
	 * @param string	$subject	Email subject
	 * @return void
	 */
	public function subject(string $subject = ''): void
	{
		parent::subject(trim($subject));
		$this->email->subject($this->subject);
	}

	/**
	 * Adds X-AntiAbuse headers
	 *
	 * @param \phpbb\config\config	$config		Config object
	 * @param \phpbb\user			$user		User object
	 * @return void
	 */
	public function anti_abuse_headers(\phpbb\config\config $config, \phpbb\user $user): void
	{
		$this->headers->addHeader('X-AntiAbuse', 'Board servername - ' . $config['server_name']);
		$this->headers->addHeader('X-AntiAbuse', 'User_id - ' . $user->data['user_id']);
		$this->headers->addHeader('X-AntiAbuse', 'Username - ' . $user->data['username']);
		$this->headers->addHeader('X-AntiAbuse', 'User IP - ' . $user->ip);
	}

	/**
	 * Set the email priority
	 *
	 * Possible values are:
	 * symfony_email::PRIORITY_HIGHEST = 1
	 * symfony_email::PRIORITY_HIGH = 2
	 * symfony_email::PRIORITY_NORMAL = 3
	 * symfony_email::PRIORITY_LOW = 4
	 * symfony_email::PRIORITY_LOWEST = 5
	 *
	 * @param int	$priority	Email priority level
	 * @return void
	 */
	public function set_mail_priority(int $priority = symfony_email::PRIORITY_NORMAL): void
	{
		$this->email->priority($priority);
	}

	/**
	 * Set email headers
	 *
	 * @return void
	 */
	protected function build_headers(): void
	{

		$board_contact = trim($this->config['board_contact']);
		$contact_name = html_entity_decode($this->config['board_contact_name'], ENT_COMPAT);

		if (empty($this->email->getReplyTo()))
		{
			$this->reply_to($board_contact, $contact_name);
		}

		if (empty($this->email->getFrom()))
		{
			$this->from($board_contact, $contact_name);
		}

		$this->email->priority($this->mail_priority);

		$headers = [
			'Return-Path'		=> new Address($this->config['board_email']),
			'Sender'			=> new Address($this->config['board_email']),
			'X-MSMail-Priority'	=> self::PRIORITY_MAP[$this->mail_priority],
			'X-Mailer'			=> 'phpBB3',
			'X-MimeOLE'			=> 'phpBB3',
			'X-phpBB-Origin'	=> 'phpbb://' . str_replace(['http://', 'https://'], ['', ''], generate_board_url()),
		];

		// Add additional headers
		$headers = array_merge($headers, $this->additional_headers);

		/**
		 * Event to modify email header entries
		 *
		 * @event core.modify_email_headers
		 * @var	array	headers	Array containing email header entries
		 * @since 3.1.11-RC1
		 */
		$vars = ['headers'];
		extract($this->dispatcher->trigger_event('core.modify_email_headers', compact($vars)));

		foreach ($headers as $header => $value)
		{
			$this->headers->addHeader($header, $value);
		}

	}

	/**
	 * Generates valid DSN for Symfony Mailer transport
	 *
	 * @param string $dsn Symfony Mailer transport DSN
	 * @return void
	 */
	public function set_dsn(string $dsn = ''): void
	{
		if (!empty($dsn))
		{
			$this->dsn = $dsn;
		}
		else if ($this->config['smtp_delivery'])
		{
			if (empty($this->config['smtp_host']))
			{
				$this->dsn = 'null://null';
			}
			else
			{
				$user = urlencode($this->config['smtp_username']);
				$password = urlencode($this->config['smtp_password']);
				$smtp_host = urlencode($this->config['smtp_host']);
				$smtp_port = $this->config['smtp_port'];

				$this->dsn = "smtp://$user:$password@$smtp_host:$smtp_port";
			}
		}
		else
		{
			$this->dsn = 'sendmail://default';
		}
	}

	/**
	 * Get Symfony Mailer transport DSN
	 *
	 * @return string
	 */
	public function get_dsn(): string
	{
		return $this->dsn;
	}

	/**
	 * Generates a valid transport to send email
	 *
	 * @return void
	 */
	public function set_transport(): void
	{
		if (empty($this->dsn))
		{
			$this->set_dsn();
		}

		$this->transport = Transport::fromDsn($this->dsn);

		if ($this->config['smtp_delivery'] && method_exists($this->transport, 'getStream'))
		{
			// Set ssl context options, see http://php.net/manual/en/context.ssl.php
			$options['ssl'] = [
				'verify_peer' => (bool) $this->config['smtp_verify_peer'],
				'verify_peer_name' => (bool) $this->config['smtp_verify_peer_name'],
				'allow_self_signed' => (bool) $this->config['smtp_allow_self_signed'],
			];
			$this->transport->getStream()->setStreamOptions($options);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function process_queue(array &$queue_data): void
	{
		$queue_object_name = $this->get_queue_object_name();
		$messages_count = count($queue_data[$queue_object_name]['data']);

		if (!$this->is_enabled() || !$messages_count)
		{
			unset($queue_data[$queue_object_name]);
			return;
		}

		@set_time_limit(0);

		$package_size = $queue_data[$queue_object_name]['package_size'] ?? 0;
		$num_items = (!$package_size || $messages_count < $package_size) ? $messages_count : $package_size;
		$mailer = new Mailer($this->transport);

		for ($i = 0; $i < $num_items; $i++)
		{
			// Make variables available...
			extract(array_shift($queue_data[$queue_object_name]['data']));

			$break = false;
			/**
			 * Event to send message via external transport
			 *
			 * @event core.notification_message_process
			 * @var	string		break	Flag indicating if the function return after hook
			 * @var	string		email	The Symfony Email object
			 * @since 3.2.4-RC1
			 * @changed 4.0.0-a1 Added vars: email. Removed vars: addresses, subject, msg.
			 */
			$vars = [
				'break',
				'email',
			];
			extract($this->dispatcher->trigger_event('core.notification_message_process', compact($vars)));

			if (!$break)
			{
				try
				{
					$mailer->send($email);
				}
				catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e)
				{
					$this->error($e->getDebug());
					continue;
				}
			}
		}

		// No more data for this object? Unset it
		if (!count($queue_data[$queue_object_name]['data']))
		{
			unset($queue_data[$queue_object_name]);
		}
	}

	/**
	 * Get mailer transport object
	 *
	 * @return \Symfony\Component\Mailer\Transport\TransportInterface Symfony Mailer transport object
	 */
	public function get_transport(): \Symfony\Component\Mailer\Transport\TransportInterface
	{
		return $this->transport;
	}

	/**
	 * {@inheritDoc}
	 */
	public function send(): bool
	{
		$this->prepare_message();

		$this->email->subject($this->subject);
		$this->email->text($this->msg);

		$subject = $this->subject;
		$msg = $this->msg;
		$email = $this->email;
		/**
		 * Event to send message via external transport
		 *
		 * @event core.notification_message_email
		 * @var	string	subject	The message subject
		 * @var	string	msg		The message text
		 * @var	string	email	The Symfony Email object
		 * @since 3.2.4-RC1
		 * @changed 4.0.0-a1 Added vars: email. Removed vars: addresses, break
		 */
		$vars = [
			'subject',
			'msg',
			'email',
		];
		extract($this->dispatcher->trigger_event('core.notification_message_email', compact($vars)));
		$this->email = $email;

		$this->build_headers();

		// Send message ...
		if (!$this->use_queue)
		{
			$mailer = new Mailer($this->transport);

			$subject = $this->subject;
			$msg = $this->msg;
			$headers = $this->headers;
			$email = $this->email;
			/**
			 * Modify data before sending out emails with PHP's mail function
			 *
			 * @event core.phpbb_mail_before
			 * @var	string	email		The Symfony Email object
			 * @var	string	subject		The message subject
			 * @var	string	msg			The message text
			 * @var string	headers		The email headers
			 * @since 3.3.6-RC1
			 * @changed 4.0.0-a1 Added vars: email. Removed vars: to, eol, additional_parameters.
			 */
			$vars = [
				'email',
				'subject',
				'msg',
				'headers',
			];
			extract($this->dispatcher->trigger_event('core.phpbb_mail_before', compact($vars)));

			$this->subject = $subject;
			$this->msg = $msg;
			$this->headers = $headers;
			$this->email = $email;

			try
			{
				$mailer->send($this->email);
			}
			catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e)
			{
				$this->error($e->getDebug());
				return false;
			}

			/**
			 * Execute code after sending out emails with PHP's mail function
			 *
			 * @event core.phpbb_mail_after
			 * @var	string	email		The Symfony Email object
			 * @var	string	subject		The message subject
			 * @var	string	msg			The message text
			 * @var string	headers		The email headers
			 * @since 3.3.6-RC1
			 * @changed 4.0.0-a1 Added vars: email. Removed vars: to, eol, additional_parameters, $result.
			 */
			$vars = [
				'email',
				'subject',
				'msg',
				'headers',
			];
			extract($this->dispatcher->trigger_event('core.phpbb_mail_after', compact($vars)));
		}
		else
		{
			$this->queue->init('email', $this->config['email_package_size']);
			$this->queue->put('email', [
				'email'	=> $this->email,
			]);
		}

		// Reset the object
		$this->init();

		return true;
	}
}
