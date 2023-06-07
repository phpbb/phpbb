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
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\Headers;

/**
 * Messenger class
 */
class email extends base
{
	/** @var array */
	private const PRIORITY_MAP = [
		Email::PRIORITY_HIGHEST => 'Highest',
		Email::PRIORITY_HIGH => 'High',
		Email::PRIORITY_NORMAL => 'Normal',
		Email::PRIORITY_LOW => 'Low',
		Email::PRIORITY_LOWEST => 'Lowest',
	];

	/** @var array */
	private const HEADER_CLASS_MAP = [
		'date' => DateHeader::class,
		'from' => MailboxListHeader::class,
		'sender' => MailboxHeader::class,
		'reply-to' => MailboxListHeader::class,
		'to' => MailboxListHeader::class,
		'cc' => MailboxListHeader::class,
		'bcc' => MailboxListHeader::class,
		'message-id' => IdentificationHeader::class,
		'in-reply-to' => UnstructuredHeader::class,
		'references' => UnstructuredHeader::class,
		'return-path' => PathHeader::class,
	];

	/**
	 * @var string
	 *
	 * Symfony Mailer transport DSN
	 */
	protected $dsn = '';

	/** @var string */
	protected $from;

	/** @var Symfony\Component\Mime\Header\Headers */
	protected $headers;

	/**
	 * @var int
	 *
	 * Possible values are:
	 * Email::PRIORITY_HIGHEST
	 * Email::PRIORITY_HIGH
	 * Email::PRIORITY_NORMAL
	 * Email::PRIORITY_LOW
	 * Email::PRIORITY_LOWEST
	 */
	protected $mail_priority = Email::PRIORITY_NORMAL;

	/** @var queue */
	protected $queue;

	/** @var string */
	protected $replyto = '';

	/** @var Symfony\Component\Mailer\Transport */
	protected $transport;

	/**
	 * Get messenger method id
	 *
	 * @return int
	 */
	public function get_id()
	{
		return NOTIFY_EMAIL;
	}

	/**
	 * get messenger method fie queue object name
	 * @return string
	 */
	abstract public function get_queue_object_name($user)
	{
		return 'email';
	}

	/**
	 * Check if the messenger method is enabled
	 * @return void
	 */
	public function is_enabled()
	{
		return (bool) $this->config['email_enable'];
	}

	/**
	 * Inits/resets the data to default
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->email = new Email();
		$this->headers = $this->email->getHeaders();
		$this->msg = $this->replyto = $this->from = '';
		$this->mail_priority = Email::PRIORITY_NORMAL;

		parent::reset();
	}

	/**
	 * Sets the use of messenger queue flag
	 *
	 * @return void
	 */
	public function set_use_queue($use_queue = true)
	{
		$this->use_queue = !$this->config['email_package_size'] ? false : $use_queue;
	}

	/**
	 * Set address as available
	 *
	 * @param array $user User row
	 * @return void
	 */
	public function set_addresses($user)
	{
		if (isset($user['user_email']) && $user['user_email'])
		{
			$this->to($user['user_email'], $user['username'] ?: '');
		}
	}

	/**
	 * Sets email address to send to
	 *
	 * @param string	$address	Email "To" recipient address
	 * @param string	$realname	Email "To" recipient name
	 * @return void
	 */
	public function to($address, $realname = '')
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
	public function cc($address, $realname = '')
	{
		if (!$address = trim($address))
		{
			return;
		}

		$cc = new Address($address, trim($realname));
		$this->email->getCc() ? $this->email->addCc($to) : $this->email->cc($to);
	}

	/**
	 * Sets bcc address to send to
	 *
	 * @param string	$address	Email black carbon copy recipient address
	 * @param string	$realname	Email black carbon copy recipient name
	 * @return void
	 */
	public function bcc($address, $realname = '')
	{
		if (!$address = trim($address))
		{
			return;
		}

		$bcc = new Address($address, trim($realname));
		$this->email->getBcc() ? $this->email->addBcc($to) : $this->email->bcc($to);
	}

	/**
	 * Set the reply to address
	 *
	 * @param string	$address	Email "Reply to" address
	 * @return void
	 */
	public function replyto($address)
	{
		$this->replyto = new Address(trim($address));
		$this->email->getReplyTo() ? $this->email->addReplyTo($this->replyto) : $this->email->replyTo($this->replyto);
	}

	/**
	 * Set the from address
	 *
	 * @param string	$address	Email "from" address
	 * @return void
	 */
	public function from($address)
	{
		$this->from = new Address(trim($address));
		$this->email->getFrom() ? $this->email->addFrom($this->from) : $this->email->from($this->from);
	}

	/**
	 * Set up subject for mail
	 *
	 * @param string	$subject	Email subject
	 * @return void
	 */
	public function subject($subject = '')
	{
		parent::subject(trim($subject));
		$this->email->subject($this->subject);
	}

	/**
	 * Set up extra mail headers
	 *
	 * @param string	$header_name	Email header name
	 * @param string	$header_value	Email header body
	 * @return void
	 */
	public function header($header_name, $header_value)
	{
		$header_name = trim($header_name);
		$header_value = trim($header_value);

		// addMailboxListHeader() requires value to be array
		if ($this->get_header_method($header_name) == 'addMailboxListHeader')
		{
			$header_value = [$header_value];
		}
		$this->headers->addHeader($header_name, $header_value);
	}

	/**
	 * Adds X-AntiAbuse headers
	 *
	 * @param \phpbb\config\config	$config		Config object
	 * @param \phpbb\user			$user		User object
	 * @return void
	 */
	public function anti_abuse_headers($config, $user)
	{
		$this->header('X-AntiAbuse', 'Board servername - ' . $config['server_name']);
		$this->header('X-AntiAbuse', 'User_id - ' . $user->data['user_id']);
		$this->header('X-AntiAbuse', 'Username - ' . $user->data['username']);
		$this->header('X-AntiAbuse', 'User IP - ' . $user->ip);
	}

	/**
	 * Set the email priority
	 *
	 * Possible values are:
	 * Email::PRIORITY_HIGHEST = 1
	 * Email::PRIORITY_HIGH = 2
	 * Email::PRIORITY_NORMAL = 3
	 * Email::PRIORITY_LOW = 4
	 * Email::PRIORITY_LOWEST = 5
	 *
	 * @param int	$priority	Email priority level
	 * @return void
	 */
	public function set_mail_priority($priority = Email::PRIORITY_NORMAL)
	{
		$this->email->priority($priority);
	}

	/**
	 * Add error message to log
	 *
	 * @param string	$msg	Error message text
	 * @return void
	 */
	public function error($msg)
	{
		$type = 'EMAIL/' . ($this->config['smtp_delivery']) ? 'SMTP' : 'PHP/mail()';
		parent::error($type, $msg);
	}

	/**
	 * Detect proper Header class method to add header
	 *
	 * @param string	$name	Email header name
	 * @return string
	 */
	protected function get_header_method(string $name)
	{
		$parts = explode('\\', self::HEADER_CLASS_MAP[strtolower($name)] ?? UnstructuredHeader::class);
		$method = 'add'.ucfirst(array_pop($parts));
		if ('addUnstructuredHeader' === $method)
		{
			$method = 'addTextHeader';
		}
		else if ('addIdentificationHeader' === $method)
		{
			$method = 'addIdHeader';
		}

		return $method;
	}

	/**
	 * Set email headers
	 *
	 * @return bool
	 */
	protected function build_header()
	{
		$headers = [];

		$board_contact = $this->config['board_contact'];
		if (empty($this->email->getReplyTo()))
		{
			$this->replyto($board_contact);
			$headers['Reply-To'] =  $this->replyto;
		}

		if (empty($this->email->getFrom()))
		{
			$this->from($board_contact);
			$headers['From'] = $this->from;
		}

		$headers['Return-Path'] = new Address($this->config['board_email']);
		$headers['Sender'] = new Address($this->config['board_email']);
		$headers['X-Priority'] = sprintf('%d (%s)', $this->mail_priority, self::PRIORITY_MAP[$this->mail_priority]);
		$headers['X-MSMail-Priority'] = self::PRIORITY_MAP[$this->mail_priority];
		$headers['X-Mailer'] = 'phpBB3';
		$headers['X-MimeOLE'] = 'phpBB3';
		$headers['X-phpBB-Origin'] = 'phpbb://' . str_replace(['http://', 'https://'], ['', ''], generate_board_url());

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
			$this->header($header, $value);
		}

		return true;
	}

	/**
	 * Generates valid DSN for Symfony Mailer transport
	 *
	 * @param string $dsn Symfony Mailer transport DSN
	 * @return void
	 */
	public function set_dsn($dsn = '')
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
	 * @return void
	 */
	public function get_dsn()
	{
		return $this->dsn;
	}

	/**
	 * Generates a valid transport to send email
	 *
	 * @return void
	 */
	public function set_transport()
	{
		if (empty($this->dsn))
		{
			$this->set_dsn();
		}

		$this->transport = Transport::fromDsn($this->dsn);

		if ($this->config['smtp_delivery'] && !in_array($this->dsn, ['null://null', 'sendmail://default']))
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
	 * Send messages from the queue
	 *
	 * @param array $queue_data Queue data array
	 * @return void
	 */
	public function process_queue(&$queue_data)
	{
		$queue_object_name = $this->get_queue_object_name();
		$messages_count = count($queue_data[$queue_object_name]['data'];

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
			 * @var	bool							break	Flag indicating if the function return after hook
			 * @var	Symfony\Component\Mime\Email	email	The Symfony Email object
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
				catch (TransportExceptionInterface $e)
				{
					$this->error('EMAIL', $e->getDebug());
					continue;
				}
			}
		}

		// No more data for this object? Unset it
		if (!count($this->queue_data[$queue_object_name]['data']))
		{
			unset($this->queue_data[$queue_object_name]);
		}
	}

	/**
	 * Get mailer transport object
	 *
	 * @return Symfony\Component\Mailer\Transport Symfony Mailer transport object
	 */
	public function get_transport()
	{
		return $this->transport;
	}

	/**
	 * Send out emails
	 *
	 * @return bool
	 */
	protected function send()
	{
		$this->prepare_message();

		$contact_name = html_entity_decode($this->config['board_contact_name'], ENT_COMPAT);
		$board_contact = trim($this->config['board_contact']);

		$this->email->subject($this->subject);
		$this->email->text($this->msg);

		$subject = $this->subject;
		$msg = $this->msg;
		$email = $this->email;
		/**
		 * Event to send message via external transport
		 *
		 * @event core.notification_message_email
		 * @var	string							subject		The message subject
		 * @var	string							msg			The message text
		 * @var	Symfony\Component\Mime\Email	email		The Symfony Email object
		 * @since 3.2.4-RC1
		 * @changed 4.0.0-a1 Added vars: email. Removed vars: addresses, break
		 */
		$vars = [
			'subject',
			'msg',
			'email',
		];
		extract($this->dispatcher->trigger_event('core.notification_message_email', compact($vars)));

		if (empty($this->email->getReplyto()))
		{
			$this->replyto($board_contact);
		}

		if (empty($this->email->getFrom()))
		{
			$this->from($board_contact);
		}

		// Build headers
		foreach ($this->additional_headers as $header_name => $header_value)
		{
			$this->header($header_name, $header_value);
		}
		$this->build_header();

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
			 * @var	Symfony\Component\Mime\Email	email	The Symfony Email object
			 * @var	string	subject							The message subject
			 * @var	string	msg								The message text
			 * @var string	headers							The email headers
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
			catch (TransportExceptionInterface $e)
			{
				$this->error($e->getDebug());
				return false;
			}

			/**
			 * Execute code after sending out emails with PHP's mail function
			 *
			 * @event core.phpbb_mail_after
			 * @var	Symfony\Component\Mime\Email	email	The Symfony Email object
			 * @var	string	subject							The message subject
			 * @var	string	msg								The message text
			 * @var string	headers							The email headers
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
		$this->reset();

		return true;
	}
}
