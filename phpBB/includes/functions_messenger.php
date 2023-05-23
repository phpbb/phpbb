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

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\Headers;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Messenger class
 */
class messenger
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

	/** @var array */
	protected $additional_headers = [];

	/** @var array */
	protected $addresses = [];

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

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

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log_interface */
	protected $log;

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

	/** @var string */
	protected $msg;

	/** @var string */
	protected $php_ext;

	/** @var queue */
	protected $queue;

	/** @var string */
	protected $replyto;

	/** @var  \phpbb\request\request */
	protected $request;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $subject;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var Symfony\Component\Mailer\Transport */
	protected $transport;

	/** @var bool */
	protected $use_queue = true;

	/** @var phpbb\user */
	protected $user;

	/**
	 * Messenger class constructor
	 *
	 * @param bool $use_queue Flag to switch the use of the messenger file queue
	 */
	function __construct($use_queue = true)
	{
		global $phpbb_container;

		$this->phpbb_container = $phpbb_container;
		$this->config = $this->phpbb_container->get('config');
		$this->dispatcher = $this->phpbb_container->get('dispatcher');
		$this->language = $this->phpbb_container->get('language');
		$this->log = $this->phpbb_container->get('log');
		$this->request = $this->phpbb_container->get('request');
		$this->user = $this->phpbb_container->get('user');
		$this->email = new Email();
		$this->headers = $this->email->getHeaders();
		$this->use_queue = (!$this->config['email_package_size']) ? false : $use_queue;
		$this->subject = '';
		$this->php_ext = $this->phpbb_container->getParameter('core.php_ext');
		$this->root_path = $this->phpbb_container->getParameter('core.root_path');
		$this->set_transport();
	}

	/**
	 * Resets all the data (address, template file, etc etc) to default
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->addresses = [];
		$this->msg = $this->replyto = $this->from = '';
		$this->mail_priority = Email::PRIORITY_NORMAL;
	}

	/**
	 * Set addresses for to/im as available
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

		if (isset($user['user_jabber']) && $user['user_jabber'])
		{
			$this->im($user['user_jabber'], $user['username'] ?: '');
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
		if (!trim($address))
		{
			return;
		}

		// If empty sendmail_path on windows, PHP changes the to line
		$windows_empty_sendmail_path = !$this->config['smtp_delivery'] && DIRECTORY_SEPARATOR == '\\';

		$to = new Address(trim($address), $windows_empty_sendmail_path ? '' : trim($realname));
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
		if (!trim($address))
		{
			return;
		}

		$cc = new Address(trim($address), trim($realname));
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
		if (!trim($address))
		{
			return;
		}

		$bcc = new Address(trim($address), trim($realname));
		$this->email->getBcc() ? $this->email->addBcc($to) : $this->email->bcc($to);
	}

	/**
	 * Sets a im contact to send to
	 *
	 * @param string	$address	Jabber recipient address
	 * @param string	$realname	Jabber recipient name
	 * @return void
	 */
	public function im($address, $realname = '')
	{
		// IM-Addresses could be empty
		if (!trim($address))
		{
			return;
		}

		$pos = isset($this->addresses['im']) ? count($this->addresses['im']) : 0;
		$this->addresses['im'][$pos]['uid'] = trim($address);
		$this->addresses['im'][$pos]['name'] = trim($realname);
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
		$this->subject = $subject;
		$this->email->subject(trim($this->subject));
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
	 * Set email template to use
	 *
	 * @param string	$template_file			Email template file name
	 * @param string	$template_lang			Email template language
	 * @param string	$template_path			Email template path
	 * @param string	$template_dir_prefix	Email template directory prefix
	 *
	 * @return bool
	 */
	public function template($template_file, $template_lang = '', $template_path = '', $template_dir_prefix = '')
	{
		$template_dir_prefix = (!$template_dir_prefix || $template_dir_prefix[0] === '/') ? $template_dir_prefix : '/' . $template_dir_prefix;

		$this->setup_template();

		if (!trim($template_file))
		{
			trigger_error('No template file for emailing set.', E_USER_ERROR);
		}

		if (!trim($template_lang))
		{
			// fall back to board default language if the user's language is
			// missing $template_file.  If this does not exist either,
			// $this->template->set_filenames will do a trigger_error
			$template_lang = basename($this->config['default_lang']);
		}

		$ext_template_paths = [
			[
				'name' 		=> $template_lang . '_email',
				'ext_path' 	=> 'language/' . $template_lang . '/email' . $template_dir_prefix,
			],
		];

		if ($template_path)
		{
			$template_paths = [
				$template_path . $template_dir_prefix,
			];
		}
		else
		{
			$template_path = (!empty($this->user->lang_path)) ? $this->user->lang_path : $this->root_path . 'language/';
			$template_path .= $template_lang . '/email';

			$template_paths = [
				$template_path . $template_dir_prefix,
			];

			$board_language = basename($this->config['default_lang']);

			// we can only specify default language fallback when the path is not a custom one for which we
			// do not know the default language alternative
			if ($template_lang !== $board_language)
			{
				$fallback_template_path = (!empty($this->user->lang_path)) ? $this->user->lang_path : $this->root_path . 'language/';
				$fallback_template_path .= $board_language . '/email';

				$template_paths[] = $fallback_template_path . $template_dir_prefix;

				$ext_template_paths[] = [
					'name'		=> $board_language . '_email',
					'ext_path'	=> 'language/' . $board_language . '/email' . $template_dir_prefix,
				];
			}
			// If everything fails just fall back to en template
			if ($template_lang !== 'en' && $board_language !== 'en')
			{
				$fallback_template_path = (!empty($this->user->lang_path)) ? $this->user->lang_path : $this->root_path . 'language/';
				$fallback_template_path .= 'en/email';

				$template_paths[] = $fallback_template_path . $template_dir_prefix;

				$ext_template_paths[] = [
					'name'		=> 'en_email',
					'ext_path'	=> 'language/en/email' . $template_dir_prefix,
				];
			}
		}

		$this->set_template_paths($ext_template_paths, $template_paths);

		$this->template->set_filenames([
			'body'		=> $template_file . '.txt',
		]);

		return true;
	}

	/**
	 * Assign variables to email template
	 *
	 * @param array	$vars	Array of VAR => VALUE to assign to email template
	 * @return void
	 */
	public function assign_vars($vars)
	{
		$this->setup_template();

		$this->template->assign_vars($vars);
	}

	/**
	 * Assign block of variables to email template
	 *
	 * @param string	$blockname	Template block name
	 * @param array		$vars		Array of VAR => VALUE to assign to email template block
	 * @return void
	 */
	public function assign_block_vars($blockname, $vars)
	{
		$this->setup_template();

		$this->template->assign_block_vars($blockname, $vars);
	}

	/**
	 * Prepare message before sending out to the recipients
	 *
	 * @return void
	 */
	public function prepare_message()
	{
		// We add some standard variables we always use, no need to specify them always
		$this->assign_vars([
			'U_BOARD'	=> generate_board_url(),
			'EMAIL_SIG'	=> str_replace('<br />', "\n", "-- \n" . html_entity_decode($this->config['board_email_sig'], ENT_COMPAT)),
			'SITENAME'	=> html_entity_decode($this->config['sitename'], ENT_COMPAT),
		]);

		$email = $this->email;
		$subject = $this->email->getSubject();
		$template = $this->template;
		/**
		 * Event to modify the template before parsing
		 *
		 * @event core.modify_notification_template
		 * @var	Symfony\Component\Mime\Email	email		The Symfony Email object
		 * @var	string							subject		The message subject
		 * @var \phpbb\template\template 		template	The (readonly) template object
		 * @since 3.2.4-RC1
		 * @changed 4.0.0-a1 Added vars: email. Removed vars: method, break.
		 */
		$vars = ['email', 'subject', 'template'];
		extract($this->dispatcher->trigger_event('core.modify_notification_template', compact($vars)));

		// Parse message through template
		$message = trim($this->template->assign_display('body'));

		/**
		 * Event to modify notification message text after parsing
		 *
		 * @event core.modify_notification_message
		 * @var	Symfony\Component\Mime\Email	email	The Symfony Email object
		 * @var	string							message	The message text
		 * @var	string							subject	The message subject
		 * @since 3.1.11-RC1
		 * @changed 4.0.0-a1 Added vars: email.  Removed vars: method, break.
		 */
		$vars = ['email', 'message', 'subject'];
		extract($this->dispatcher->trigger_event('core.modify_notification_message', compact($vars)));

		$this->email = $email;
		$this->subject = $subject;
		$this->msg = $message;
		unset($email, $subject, $message, $template);

		// Because we use \n for newlines in the body message we need to fix line encoding errors for those admins who uploaded email template files in the wrong encoding
		$this->msg = str_replace("\r\n", "\n", $this->msg);

		// We now try and pull a subject from the email body ... if it exists,
		// do this here because the subject may contain a variable
		$drop_header = '';
		$match = [];
		if (preg_match('#^(Subject):(.*?)$#m', $this->msg, $match))
		{
			$this->subject = (trim($match[2]) != '') ? trim($match[2]) : (($this->subject != '') ? $this->subject : $this->user->lang['NO_EMAIL_SUBJECT']);
			$drop_header .= '[\r\n]*?' . preg_quote($match[0], '#');
		}
		else
		{
			$this->subject = (($this->subject != '') ? $this->subject : $this->user->lang['NO_EMAIL_SUBJECT']);
		}

		if (preg_match('#^(List-Unsubscribe):(.*?)$#m', $this->msg, $match))
		{
			$drop_header .= '[\r\n]*?' . preg_quote($match[0], '#');
			$this->additional_headers[$match[1]] = trim($match[2]);
		}

		if ($drop_header)
		{
			$this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
		}
	}

	/**
	 * Send the mail out to the recipients
	 *
	 * @param int	$method	User notification method NOTIFY_EMAIL|NOTIFY_IM|NOTIFY_BOTH
	 * @param bool	$break	Flag indicating if the function only formats the subject
	 *						and the message without sending it
	 * @return bool
	 */
	public function send($method = NOTIFY_EMAIL, $break = false)
	{
		$this->prepare_message();
		if ($break)
		{
			return true;
		}

		switch ($method)
		{
			case NOTIFY_EMAIL:
				$result = $this->msg_email();
			break;

			case NOTIFY_IM:
				$result = $this->msg_jabber();
			break;

			case NOTIFY_BOTH:
				$result = $this->msg_email();
				$this->msg_jabber();
			break;
		}

		$this->reset();
		return $result;
	}

	/**
	 * Add error message to log
	 *
	 * @param string	$type	Error type: EMAIL / etc
	 * @param string	$msg	Error message text
	 * @return void
	 */
	public function error($type, $msg)
	{
		// Session doesn't exist, create it
		if (!isset($this->user->session_id) || $this->user->session_id === '')
		{
			$this->user->session_begin();
		}

		$calling_page = html_entity_decode($this->request->server('REQUEST_URI'), ENT_COMPAT);

		switch ($type)
		{
			case 'EMAIL':
				$message = '<strong>EMAIL/' . (($this->config['smtp_delivery']) ? 'SMTP' : 'PHP/mail()') . '</strong>';
			break;

			default:
				$message = "<strong>$type</strong>";
			break;
		}

		$message .= '<br /><em>' . htmlspecialchars($calling_page, ENT_COMPAT) . '</em><br /><br />' . $msg . '<br />';
		$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_ERROR_' . $type, false, [$message]);
	}

	/**
	 * Save message data to the messemger file queue
	 * @return void
	 */
	public function save_queue()
	{
		if ($this->config['email_package_size'] && $this->use_queue && !empty($this->queue))
		{
			$this->queue->save();
			return;
		}
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
	protected function msg_email()
	{
		if (empty($this->config['email_enable']))
		{
			return false;
		}

		// Addresses to send to?
		if (empty($this->email->getTo()))
		{
			// Send was successful. ;)
			return true;
		}

		$use_queue = false;
		if ($this->config['email_package_size'] && $this->use_queue)
		{
			if (empty($this->queue))
			{
				$this->queue = new queue();
				$this->queue->init('email', $this->config['email_package_size']);
			}
			$use_queue = true;
		}

		$contact_name = html_entity_decode($this->config['board_contact_name'], ENT_COMPAT);
		$board_contact = $this->config['board_contact'];

		$this->email->subject($this->subject);
		$this->email->text($this->msg);

		$break = false;
		$subject = $this->subject;
		$msg = $this->msg;
		$email = $this->email;
		/**
		 * Event to send message via external transport
		 *
		 * @event core.notification_message_email
		 * @var	bool							break		Flag indicating if the function return after hook
		 * @var	string							subject		The message subject
		 * @var	string							msg			The message text
		 * @var	Symfony\Component\Mime\Email	email		The Symfony Email object
		 * @since 3.2.4-RC1
		 * @changed 4.0.0-a1 Added vars: email. Removed vars: addresses
		 */
		$vars = [
			'break',
			'subject',
			'msg',
			'email',
		];
		extract($this->dispatcher->trigger_event('core.notification_message_email', compact($vars)));

		$this->addresses = $addresses;
		$this->subject = $subject;
		$this->msg = $msg;
		unset($addresses, $subject, $msg);

		if ($break)
		{
			return true;
		}

		if (empty($this->email->getReplyto()))
		{
			$this->replyto($board_contact);
		}

		if (empty($this->email->getFrom()))
		{
			$this->from($board_contact);
		}

		// Build header
		foreach ($this->additional_headers as $header_name => $header_value)
		{
			$this->header($header_name, $header_value);
		}
		$this->build_header();

		// Send message ...
		if (!$use_queue)
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
			 * @var	string	subject					The message subject
			 * @var	string	msg						The message text
			 * @var string	headers					The email headers
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
				$this->error('EMAIL', $e->getDebug());
				return false;
			}

			/**
			 * Execute code after sending out emails with PHP's mail function
			 *
			 * @event core.phpbb_mail_after
			 * @var	Symfony\Component\Mime\Email	email	The Symfony Email object
			 * @var	string	subject					The message subject
			 * @var	string	msg						The message text
			 * @var string	headers					The email headers
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
			$this->queue->put('email', [
				'email'	=> $this->email,
			]);
		}

		return true;
	}

	/**
	 * Send jabber message out
	 *
	 * @return bool
	 */
	protected function msg_jabber()
	{
		if (empty($this->config['jab_enable']) || empty($this->config['jab_host']) || empty($this->config['jab_username']) || empty($this->config['jab_password']))
		{
			return false;
		}

		if (empty($this->addresses['im']))
		{
			// Send was successful. ;)
			return true;
		}

		$use_queue = false;
		if ($this->config['jab_package_size'] && $this->use_queue)
		{
			if (empty($this->queue))
			{
				$this->queue = new queue();
				$this->queue->init('jabber', $this->config['jab_package_size']);
			}
			$use_queue = true;
		}

		$addresses = [];
		foreach ($this->addresses['im'] as $type => $uid_ary)
		{
			$addresses[] = $uid_ary['uid'];
		}
		$addresses = array_unique($addresses);

		if (!$use_queue)
		{
			include_once($this->root_path . 'includes/functions_jabber.' . $this->php_ext);
			$this->jabber = new jabber($this->config['jab_host'], $this->config['jab_port'], $this->config['jab_username'], html_entity_decode($this->config['jab_password'], ENT_COMPAT), $this->config['jab_use_ssl'], $this->config['jab_verify_peer'], $this->config['jab_verify_peer_name'], $this->config['jab_allow_self_signed']);

			if (!$this->jabber->connect())
			{
				$this->error('JABBER', $this->user->lang['ERR_JAB_CONNECT'] . '<br />' . $this->jabber->get_log());
				return false;
			}

			if (!$this->jabber->login())
			{
				$this->error('JABBER', $this->user->lang['ERR_JAB_AUTH'] . '<br />' . $this->jabber->get_log());
				return false;
			}

			foreach ($addresses as $address)
			{
				$this->jabber->send_message($address, $this->msg, $this->subject);
			}

			$this->jabber->disconnect();
		}
		else
		{
			$this->queue->put('jabber', [
				'addresses'		=> $addresses,
				'subject'		=> $this->subject,
				'msg'			=> $this->msg
			]);
		}
		unset($addresses);
		return true;
	}

	/**
	 * Setup template engine
	 *
	 * @return void
	 */
	protected function setup_template()
	{
		if ($this->template instanceof \phpbb\template\template)
		{
			return;
		}

		$template_environment = new \phpbb\template\twig\environment(
			$phpbb_container->get('assets.bag'),
			$phpbb_container->get('config'),
			$phpbb_container->get('filesystem'),
			$phpbb_container->get('path_helper'),
			$phpbb_container->getParameter('core.template.cache_path'),
			$phpbb_container->get('ext.manager'),
			new \phpbb\template\twig\loader(),
			$this->dispatcher,
			[]
		);
		$template_environment->setLexer($this->phpbb_container->get('template.twig.lexer'));

		$this->template = new \phpbb\template\twig\twig(
			$this->phpbb_container->get('path_helper'),
			$this->config,
			new \phpbb\template\context(),
			$template_environment,
			$this->phpbb_container->getParameter('core.template.cache_path'),
			$this->user,
			$this->phpbb_container->get('template.twig.extensions.collection'),
			$this->phpbb_container->get('ext.manager')
		);
	}

	/**
	 * Set template paths to load
	 *
	 * @param string $path_name Email template path name
	 * @param string $paths 	Email template paths
	 * @return void
	 */
	protected function set_template_paths($path_name, $paths)
	{
		$this->setup_template();

		$this->template->set_custom_style($path_name, $paths);
	}
}

/**
 * Handling messenger file queue
 */
class queue
{
	/** @var array */
	protected $data = [];

	/** @var array */
	protected $queue_data = [];

	/** @var string */
	protected $cache_file = '';

	/**
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	 * Messenger file queue class constructor
	 */
	function __construct()
	{
		global $phpbb_container;

		$this->phpbb_container = $phpbb_container;
		$this->php_ext = $this->phpbb_container->getParameter('core.php_ext');
		$this->root_path = $this->phpbb_container->getParameter('core.root_path');
		$this->cache_file = $this->phpbb_container->getParameter('core.cache_dir') . "queue.{$this->php_ext}";
		$this->filesystem = $this->phpbb_container->get('filesystem');
		$this->config = $this->phpbb_container->get('config');
		$this->dispatcher = $this->phpbb_container->get('dispatcher');
	}

	/**
	 * Init a queue object
	 *
	 * @param string $object 	Queue object type: email/jabber/etc
	 * @param int $package_size Size of the messenger package to send
	 * @return void
	 */
	public function init($object, $package_size)
	{
		$this->data[$object] = [];
		$this->data[$object]['package_size'] = $package_size;
		$this->data[$object]['data'] = [];
	}

	/**
	 * Put message into the messenger file queue
	 *
	 * @param string $object 		Queue object type: email/jabber/etc
	 * @param mixed $message_data	Message data to send
	 * @return void
	 */
	public function put($object, $message_data)
	{
		$this->data[$object]['data'][] = $message_data;
	}

	/**
	 * Process the messemger file queue (using lock file)
	 *
	 * @return void
	 */
	public function process()
	{
		$lock = new \phpbb\lock\flock($this->cache_file);
		$lock->acquire();

		// avoid races, check file existence once
		$have_cache_file = file_exists($this->cache_file);
		if (!$have_cache_file || $this->config['last_queue_run'] > time() - $this->config['queue_interval'])
		{
			if (!$have_cache_file)
			{
				$this->config->set('last_queue_run', time(), false);
			}

			$lock->release();
			return;
		}

		$this->config->set('last_queue_run', time(), false);

		include($this->cache_file);

		foreach ($this->queue_data as $object => $data_ary)
		{
			@set_time_limit(0);

			if (!isset($data_ary['package_size']))
			{
				$data_ary['package_size'] = 0;
			}

			$package_size = $data_ary['package_size'];
			$num_items = (!$package_size || count($data_ary['data']) < $package_size) ? count($data_ary['data']) : $package_size;

			switch ($object)
			{
				case 'email':
					// Delete the email queued objects if mailing is disabled
					if (!$this->config['email_enable'])
					{
						unset($this->queue_data['email']);
						continue 2;
					}
				break;

				case 'jabber':
					if (!$this->config['jab_enable'])
					{
						unset($this->queue_data['jabber']);
						continue 2;
					}

					include_once($this->root_path . 'includes/functions_jabber.' . $this->php_ext);
					$this->jabber = new jabber($this->config['jab_host'], $this->config['jab_port'], $this->config['jab_username'], html_entity_decode($this->config['jab_password'], ENT_COMPAT), $this->config['jab_use_ssl'], $this->config['jab_verify_peer'], $this->config['jab_verify_peer_name'], $this->config['jab_allow_self_signed']);

					if (!$this->jabber->connect())
					{
						$messenger = new messenger();
						$messenger->error('JABBER', $this->user->lang['ERR_JAB_CONNECT']);
						continue 2;
					}

					if (!$this->jabber->login())
					{
						$messenger = new messenger();
						$messenger->error('JABBER', $this->user->lang['ERR_JAB_AUTH']);
						continue 2;
					}

				break;

				default:
					$lock->release();
					return;
			}

			for ($i = 0; $i < $num_items; $i++)
			{
				// Make variables available...
				extract(array_shift($this->queue_data[$object]['data']));

				switch ($object)
				{
					case 'email':
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
							$messenger = new messenger();
							$mailer = new Mailer($messenger->get_transport());

							try
							{
								$mailer->send($email);
							}
							catch (TransportExceptionInterface $e)
							{
								$messenger->error('EMAIL', $e->getDebug());
								continue 2;
							}
						}
					break;

					case 'jabber':
						foreach ($addresses as $address)
						{
							if ($this->jabber->send_message($address, $msg, $subject) === false)
							{
								$messenger = new messenger();
								$messenger->error('JABBER', $this->jabber->get_log());
								continue 3;
							}
						}
					break;
				}
			}

			// No more data for this object? Unset it
			if (!count($this->queue_data[$object]['data']))
			{
				unset($this->queue_data[$object]);
			}

			// Post-object processing
			switch ($object)
			{
				case 'jabber':
					// Hang about a couple of secs to ensure the messages are
					// handled, then disconnect
					$this->jabber->disconnect();
				break;
			}
		}

		if (!count($this->queue_data))
		{
			@unlink($this->cache_file);
		}
		else
		{
			if ($fp = @fopen($this->cache_file, 'wb'))
			{
				fwrite($fp, "<?php\nif (!defined('IN_PHPBB')) exit;\n\$this->queue_data = unserialize(" . var_export(serialize($this->queue_data), true) . ");\n\n?>");
				fclose($fp);

				if (function_exists('opcache_invalidate'))
				{
					@opcache_invalidate($this->cache_file);
				}

				try
				{
					$this->filesystem->phpbb_chmod($this->cache_file, \phpbb\filesystem\filesystem_interface::CHMOD_READ | \phpbb\filesystem\filesystem_interface::CHMOD_WRITE);
				}
				catch (\phpbb\filesystem\exception\filesystem_exception $e)
				{
					// Do nothing
				}
			}
		}

		$lock->release();
	}

	/**
	 * Save message data to the messenger file queue
	 *
	 * @return void
	 */
	public function save()
	{
		if (!count($this->data))
		{
			return;
		}

		$lock = new \phpbb\lock\flock($this->cache_file);
		$lock->acquire();

		if (file_exists($this->cache_file))
		{
			include($this->cache_file);

			foreach ($this->queue_data as $object => $data_ary)
			{
				if (isset($this->data[$object]) && count($this->data[$object]))
				{
					$this->data[$object]['data'] = array_merge($data_ary['data'], $this->data[$object]['data']);
				}
				else
				{
					$this->data[$object]['data'] = $data_ary['data'];
				}
			}
		}

		if ($fp = @fopen($this->cache_file, 'w'))
		{
			fwrite($fp, "<?php\nif (!defined('IN_PHPBB')) exit;\n\$this->queue_data = unserialize(" . var_export(serialize($this->data), true) . ");\n\n?>");
			fclose($fp);

			if (function_exists('opcache_invalidate'))
			{
				@opcache_invalidate($this->cache_file);
			}

			try
			{
				$this->filesystem->phpbb_chmod($this->cache_file, \phpbb\filesystem\filesystem_interface::CHMOD_READ | \phpbb\filesystem\filesystem_interface::CHMOD_WRITE);
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				// Do nothing
			}

			$this->data = [];
		}

		$lock->release();
	}
}
