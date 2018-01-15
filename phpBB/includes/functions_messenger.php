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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Messenger
*/
class messenger
{
	var $msg, $extra_headers, $replyto, $from, $subject;
	var $addresses = array();

	var $mail_priority = MAIL_NORMAL_PRIORITY;
	var $use_queue = true;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	* Constructor
	*/
	function messenger($use_queue = true)
	{
		global $config;

		$this->use_queue = (!$config['email_package_size']) ? false : $use_queue;
		$this->subject = '';
	}

	/**
	* Resets all the data (address, template file, etc etc) to default
	*/
	function reset()
	{
		$this->addresses = $this->extra_headers = array();
		$this->msg = $this->replyto = $this->from = '';
		$this->mail_priority = MAIL_NORMAL_PRIORITY;
	}

	/**
	* Set addresses for to/im as available
	*
	* @param array $user User row
	*/
	function set_addresses($user)
	{
		if (isset($user['user_email']) && $user['user_email'])
		{
			$this->to($user['user_email'], (isset($user['username']) ? $user['username'] : ''));
		}

		if (isset($user['user_jabber']) && $user['user_jabber'])
		{
			$this->im($user['user_jabber'], (isset($user['username']) ? $user['username'] : ''));
		}
	}

	/**
	* Sets an email address to send to
	*/
	function to($address, $realname = '')
	{
		global $config;

		if (!trim($address))
		{
			return;
		}

		$pos = isset($this->addresses['to']) ? count($this->addresses['to']) : 0;

		$this->addresses['to'][$pos]['email'] = trim($address);

		// If empty sendmail_path on windows, PHP changes the to line
		if (!$config['smtp_delivery'] && DIRECTORY_SEPARATOR == '\\')
		{
			$this->addresses['to'][$pos]['name'] = '';
		}
		else
		{
			$this->addresses['to'][$pos]['name'] = trim($realname);
		}
	}

	/**
	* Sets an cc address to send to
	*/
	function cc($address, $realname = '')
	{
		if (!trim($address))
		{
			return;
		}

		$pos = isset($this->addresses['cc']) ? count($this->addresses['cc']) : 0;
		$this->addresses['cc'][$pos]['email'] = trim($address);
		$this->addresses['cc'][$pos]['name'] = trim($realname);
	}

	/**
	* Sets an bcc address to send to
	*/
	function bcc($address, $realname = '')
	{
		if (!trim($address))
		{
			return;
		}

		$pos = isset($this->addresses['bcc']) ? count($this->addresses['bcc']) : 0;
		$this->addresses['bcc'][$pos]['email'] = trim($address);
		$this->addresses['bcc'][$pos]['name'] = trim($realname);
	}

	/**
	* Sets a im contact to send to
	*/
	function im($address, $realname = '')
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
	*/
	function replyto($address)
	{
		$this->replyto = trim($address);
	}

	/**
	* Set the from address
	*/
	function from($address)
	{
		$this->from = trim($address);
	}

	/**
	* set up subject for mail
	*/
	function subject($subject = '')
	{
		$this->subject = trim($subject);
	}

	/**
	* set up extra mail headers
	*/
	function headers($headers)
	{
		$this->extra_headers[] = trim($headers);
	}

	/**
	* Adds X-AntiAbuse headers
	*
	* @param array $config		Configuration array
	* @param user $user			A user object
	*
	* @return null
	*/
	function anti_abuse_headers($config, $user)
	{
		$this->headers('X-AntiAbuse: Board servername - ' . mail_encode($config['server_name']));
		$this->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
		$this->headers('X-AntiAbuse: Username - ' . mail_encode($user->data['username']));
		$this->headers('X-AntiAbuse: User IP - ' . $user->ip);
	}

	/**
	* Set the email priority
	*/
	function set_mail_priority($priority = MAIL_NORMAL_PRIORITY)
	{
		$this->mail_priority = $priority;
	}

	/**
	* Set email template to use
	*/
	function template($template_file, $template_lang = '', $template_path = '', $template_dir_prefix = '')
	{
		global $config, $phpbb_root_path, $user;

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
			$template_lang = basename($config['default_lang']);
		}

		$ext_template_paths = array(
			array(
				'name' 		=> $template_lang . '_email',
				'ext_path' 	=> 'language/' . $template_lang . '/email' . $template_dir_prefix,
			),
		);

		if ($template_path)
		{
			$template_paths = array(
				$template_path . $template_dir_prefix,
			);
		}
		else
		{
			$template_path = (!empty($user->lang_path)) ? $user->lang_path : $phpbb_root_path . 'language/';
			$template_path .= $template_lang . '/email';

			$template_paths = array(
				$template_path . $template_dir_prefix,
			);

			$board_language = basename($config['default_lang']);

			// we can only specify default language fallback when the path is not a custom one for which we
			// do not know the default language alternative
			if ($template_lang !== $board_language)
			{
				$fallback_template_path = (!empty($user->lang_path)) ? $user->lang_path : $phpbb_root_path . 'language/';
				$fallback_template_path .= $board_language . '/email';

				$template_paths[] = $fallback_template_path . $template_dir_prefix;

				$ext_template_paths[] = array(
					'name'		=> $board_language . '_email',
					'ext_path'	=> 'language/' . $board_language . '/email' . $template_dir_prefix,
				);
			}
			// If everything fails just fall back to en template
			if ($template_lang !== 'en' && $board_language !== 'en')
			{
				$fallback_template_path = (!empty($user->lang_path)) ? $user->lang_path : $phpbb_root_path . 'language/';
				$fallback_template_path .= 'en/email';

				$template_paths[] = $fallback_template_path . $template_dir_prefix;

				$ext_template_paths[] = array(
					'name'		=> 'en_email',
					'ext_path'	=> 'language/en/email' . $template_dir_prefix,
				);
			}
		}

		$this->set_template_paths($ext_template_paths, $template_paths);

		$this->template->set_filenames(array(
			'body'		=> $template_file . '.txt',
		));

		return true;
	}

	/**
	* assign variables to email template
	*/
	function assign_vars($vars)
	{
		$this->setup_template();

		$this->template->assign_vars($vars);
	}

	function assign_block_vars($blockname, $vars)
	{
		$this->setup_template();

		$this->template->assign_block_vars($blockname, $vars);
	}

	/**
	* Send the mail out to the recipients set previously in var $this->addresses
	*
	* @param int	$method	User notification method NOTIFY_EMAIL|NOTIFY_IM|NOTIFY_BOTH
	* @param bool	$break	Flag indicating if the function only formats the subject
	*						and the message without sending it
	*
	* @return bool
	*/
	function send($method = NOTIFY_EMAIL, $break = false)
	{
		global $config, $user, $phpbb_dispatcher;

		// We add some standard variables we always use, no need to specify them always
		$this->assign_vars(array(
			'U_BOARD'	=> generate_board_url(),
			'EMAIL_SIG'	=> str_replace('<br />', "\n", "-- \n" . htmlspecialchars_decode($config['board_email_sig'])),
			'SITENAME'	=> htmlspecialchars_decode($config['sitename']),
		));

		$subject = $this->subject;
		$message = $this->msg;
		/**
		* Event to modify notification message text before parsing
		*
		* @event core.modify_notification_message
		* @var	int		method	User notification method NOTIFY_EMAIL|NOTIFY_IM|NOTIFY_BOTH
		* @var	bool	break	Flag indicating if the function only formats the subject
		*						and the message without sending it
		* @var	string	subject	The message subject
		* @var	string	message	The message text
		* @since 3.1.11-RC1
		*/
		$vars = array(
			'method',
			'break',
			'subject',
			'message',
		);
		extract($phpbb_dispatcher->trigger_event('core.modify_notification_message', compact($vars)));
		$this->subject = $subject;
		$this->msg = $message;
		unset($subject, $message);

		// Parse message through template
		$this->msg = trim($this->template->assign_display('body'));

		// Because we use \n for newlines in the body message we need to fix line encoding errors for those admins who uploaded email template files in the wrong encoding
		$this->msg = str_replace("\r\n", "\n", $this->msg);

		// We now try and pull a subject from the email body ... if it exists,
		// do this here because the subject may contain a variable
		$drop_header = '';
		$match = array();
		if (preg_match('#^(Subject:(.*?))$#m', $this->msg, $match))
		{
			$this->subject = (trim($match[2]) != '') ? trim($match[2]) : (($this->subject != '') ? $this->subject : $user->lang['NO_EMAIL_SUBJECT']);
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->subject = (($this->subject != '') ? $this->subject : $user->lang['NO_EMAIL_SUBJECT']);
		}

		if ($drop_header)
		{
			$this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
		}

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
	*/
	function error($type, $msg)
	{
		global $user, $config, $request, $phpbb_log;

		// Session doesn't exist, create it
		if (!isset($user->session_id) || $user->session_id === '')
		{
			$user->session_begin();
		}

		$calling_page = htmlspecialchars_decode($request->server('PHP_SELF'));

		switch ($type)
		{
			case 'EMAIL':
				$message = '<strong>EMAIL/' . (($config['smtp_delivery']) ? 'SMTP' : 'PHP/mail()') . '</strong>';
			break;

			default:
				$message = "<strong>$type</strong>";
			break;
		}

		$message .= '<br /><em>' . htmlspecialchars($calling_page) . '</em><br /><br />' . $msg . '<br />';
		$phpbb_log->add('critical', $user->data['user_id'], $user->ip, 'LOG_ERROR_' . $type, false, array($message));
	}

	/**
	* Save to queue
	*/
	function save_queue()
	{
		global $config;

		if ($config['email_package_size'] && $this->use_queue && !empty($this->queue))
		{
			$this->queue->save();
			return;
		}
	}

	/**
	* Generates a valid message id to be used in emails
	*
	* @return string message id
	*/
	function generate_message_id()
	{
		global $config, $request;

		$domain = ($config['server_name']) ?: $request->server('SERVER_NAME', 'phpbb.generated');

		return md5(unique_id(time())) . '@' . $domain;
	}

	/**
	* Return email header
	*/
	function build_header($to, $cc, $bcc)
	{
		global $config, $phpbb_dispatcher;

		// We could use keys here, but we won't do this for 3.0.x to retain backwards compatibility
		$headers = array();

		$headers[] = 'From: ' . $this->from;

		if ($cc)
		{
			$headers[] = 'Cc: ' . $cc;
		}

		if ($bcc)
		{
			$headers[] = 'Bcc: ' . $bcc;
		}

		$headers[] = 'Reply-To: ' . $this->replyto;
		$headers[] = 'Return-Path: <' . $config['board_email'] . '>';
		$headers[] = 'Sender: <' . $config['board_email'] . '>';
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Message-ID: <' . $this->generate_message_id() . '>';
		$headers[] = 'Date: ' . date('r', time());
		$headers[] = 'Content-Type: text/plain; charset=UTF-8'; // format=flowed
		$headers[] = 'Content-Transfer-Encoding: 8bit'; // 7bit

		$headers[] = 'X-Priority: ' . $this->mail_priority;
		$headers[] = 'X-MSMail-Priority: ' . (($this->mail_priority == MAIL_LOW_PRIORITY) ? 'Low' : (($this->mail_priority == MAIL_NORMAL_PRIORITY) ? 'Normal' : 'High'));
		$headers[] = 'X-Mailer: phpBB3';
		$headers[] = 'X-MimeOLE: phpBB3';
		$headers[] = 'X-phpBB-Origin: phpbb://' . str_replace(array('http://', 'https://'), array('', ''), generate_board_url());

		/**
		* Event to modify email header entries
		*
		* @event core.modify_email_headers
		* @var	array	headers	Array containing email header entries
		* @since 3.1.11-RC1
		*/
		$vars = array('headers');
		extract($phpbb_dispatcher->trigger_event('core.modify_email_headers', compact($vars)));

		if (count($this->extra_headers))
		{
			$headers = array_merge($headers, $this->extra_headers);
		}

		return $headers;
	}

	/**
	* Send out emails
	*/
	function msg_email()
	{
		global $config;

		if (empty($config['email_enable']))
		{
			return false;
		}

		// Addresses to send to?
		if (empty($this->addresses) || (empty($this->addresses['to']) && empty($this->addresses['cc']) && empty($this->addresses['bcc'])))
		{
			// Send was successful. ;)
			return true;
		}

		$use_queue = false;
		if ($config['email_package_size'] && $this->use_queue)
		{
			if (empty($this->queue))
			{
				$this->queue = new queue();
				$this->queue->init('email', $config['email_package_size']);
			}
			$use_queue = true;
		}

		$contact_name = htmlspecialchars_decode($config['board_contact_name']);
		$board_contact = (($contact_name !== '') ? '"' . mail_encode($contact_name) . '" ' : '') . '<' . $config['board_contact'] . '>';

		if (empty($this->replyto))
		{
			$this->replyto = $board_contact;
		}

		if (empty($this->from))
		{
			$this->from = $board_contact;
		}

		$encode_eol = ($config['smtp_delivery']) ? "\r\n" : PHP_EOL;

		// Build to, cc and bcc strings
		$to = $cc = $bcc = '';
		foreach ($this->addresses as $type => $address_ary)
		{
			if ($type == 'im')
			{
				continue;
			}

			foreach ($address_ary as $which_ary)
			{
				${$type} .= ((${$type} != '') ? ', ' : '') . (($which_ary['name'] != '') ? mail_encode($which_ary['name'], $encode_eol) . ' <' . $which_ary['email'] . '>' : $which_ary['email']);
			}
		}

		// Build header
		$headers = $this->build_header($to, $cc, $bcc);

		// Send message ...
		if (!$use_queue)
		{
			$mail_to = ($to == '') ? 'undisclosed-recipients:;' : $to;
			$err_msg = '';

			if ($config['smtp_delivery'])
			{
				$result = smtpmail($this->addresses, mail_encode($this->subject), wordwrap(utf8_wordwrap($this->msg), 997, "\n", true), $err_msg, $headers);
			}
			else
			{
				$result = phpbb_mail($mail_to, $this->subject, $this->msg, $headers, PHP_EOL, $err_msg);
			}

			if (!$result)
			{
				$this->error('EMAIL', $err_msg);
				return false;
			}
		}
		else
		{
			$this->queue->put('email', array(
				'to'			=> $to,
				'addresses'		=> $this->addresses,
				'subject'		=> $this->subject,
				'msg'			=> $this->msg,
				'headers'		=> $headers)
			);
		}

		return true;
	}

	/**
	* Send jabber message out
	*/
	function msg_jabber()
	{
		global $config, $user, $phpbb_root_path, $phpEx;

		if (empty($config['jab_enable']) || empty($config['jab_host']) || empty($config['jab_username']) || empty($config['jab_password']))
		{
			return false;
		}

		if (empty($this->addresses['im']))
		{
			// Send was successful. ;)
			return true;
		}

		$use_queue = false;
		if ($config['jab_package_size'] && $this->use_queue)
		{
			if (empty($this->queue))
			{
				$this->queue = new queue();
				$this->queue->init('jabber', $config['jab_package_size']);
			}
			$use_queue = true;
		}

		$addresses = array();
		foreach ($this->addresses['im'] as $type => $uid_ary)
		{
			$addresses[] = $uid_ary['uid'];
		}
		$addresses = array_unique($addresses);

		if (!$use_queue)
		{
			include_once($phpbb_root_path . 'includes/functions_jabber.' . $phpEx);
			$this->jabber = new jabber($config['jab_host'], $config['jab_port'], $config['jab_username'], htmlspecialchars_decode($config['jab_password']), $config['jab_use_ssl'], $config['jab_verify_peer'], $config['jab_verify_peer_name'], $config['jab_allow_self_signed']);

			if (!$this->jabber->connect())
			{
				$this->error('JABBER', $user->lang['ERR_JAB_CONNECT'] . '<br />' . $this->jabber->get_log());
				return false;
			}

			if (!$this->jabber->login())
			{
				$this->error('JABBER', $user->lang['ERR_JAB_AUTH'] . '<br />' . $this->jabber->get_log());
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
			$this->queue->put('jabber', array(
				'addresses'		=> $addresses,
				'subject'		=> $this->subject,
				'msg'			=> $this->msg)
			);
		}
		unset($addresses);
		return true;
	}

	/**
	* Setup template engine
	*/
	protected function setup_template()
	{
		global $phpbb_container, $phpbb_dispatcher;

		if ($this->template instanceof \phpbb\template\template)
		{
			return;
		}

		$template_environment = new \phpbb\template\twig\environment(
			$phpbb_container->get('config'),
			$phpbb_container->get('filesystem'),
			$phpbb_container->get('path_helper'),
			$phpbb_container->getParameter('core.template.cache_path'),
			$phpbb_container->get('ext.manager'),
			new \phpbb\template\twig\loader(
				$phpbb_container->get('filesystem')
			),
			$phpbb_dispatcher,
			array()
		);
		$template_environment->setLexer($phpbb_container->get('template.twig.lexer'));

		$this->template = new \phpbb\template\twig\twig(
			$phpbb_container->get('path_helper'),
			$phpbb_container->get('config'),
			new \phpbb\template\context(),
			$template_environment,
			$phpbb_container->getParameter('core.template.cache_path'),
			$phpbb_container->get('user'),
			$phpbb_container->get('template.twig.extensions.collection'),
			$phpbb_container->get('ext.manager')
		);
	}

	/**
	* Set template paths to load
	*/
	protected function set_template_paths($path_name, $paths)
	{
		$this->setup_template();

		$this->template->set_custom_style($path_name, $paths);
	}
}

/**
* handling email and jabber queue
*/
class queue
{
	var $data = array();
	var $queue_data = array();
	var $package_size = 0;
	var $cache_file = '';
	var $eol = "\n";

	/**
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	* constructor
	*/
	function queue()
	{
		global $phpEx, $phpbb_root_path, $phpbb_filesystem, $phpbb_container;

		$this->data = array();
		$this->cache_file = $phpbb_container->getParameter('core.cache_dir') . "queue.$phpEx";
		$this->filesystem = $phpbb_filesystem;
	}

	/**
	* Init a queue object
	*/
	function init($object, $package_size)
	{
		$this->data[$object] = array();
		$this->data[$object]['package_size'] = $package_size;
		$this->data[$object]['data'] = array();
	}

	/**
	* Put object in queue
	*/
	function put($object, $scope)
	{
		$this->data[$object]['data'][] = $scope;
	}

	/**
	* Process queue
	* Using lock file
	*/
	function process()
	{
		global $config, $phpEx, $phpbb_root_path, $user;

		$lock = new \phpbb\lock\flock($this->cache_file);
		$lock->acquire();

		// avoid races, check file existence once
		$have_cache_file = file_exists($this->cache_file);
		if (!$have_cache_file || $config['last_queue_run'] > time() - $config['queue_interval'])
		{
			if (!$have_cache_file)
			{
				$config->set('last_queue_run', time(), false);
			}

			$lock->release();
			return;
		}

		$config->set('last_queue_run', time(), false);

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

			/*
			* This code is commented out because it causes problems on some web hosts.
			* The core problem is rather restrictive email sending limits.
			* This code is nly useful if you have no such restrictions from the
			* web host and the package size setting is wrong.

			// If the amount of emails to be sent is way more than package_size than we need to increase it to prevent backlogs...
			if (count($data_ary['data']) > $package_size * 2.5)
			{
				$num_items = count($data_ary['data']);
			}
			*/

			switch ($object)
			{
				case 'email':
					// Delete the email queued objects if mailing is disabled
					if (!$config['email_enable'])
					{
						unset($this->queue_data['email']);
						continue 2;
					}
				break;

				case 'jabber':
					if (!$config['jab_enable'])
					{
						unset($this->queue_data['jabber']);
						continue 2;
					}

					include_once($phpbb_root_path . 'includes/functions_jabber.' . $phpEx);
					$this->jabber = new jabber($config['jab_host'], $config['jab_port'], $config['jab_username'], htmlspecialchars_decode($config['jab_password']), $config['jab_use_ssl'], $config['jab_verify_peer'], $config['jab_verify_peer_name'], $config['jab_allow_self_signed']);

					if (!$this->jabber->connect())
					{
						$messenger = new messenger();
						$messenger->error('JABBER', $user->lang['ERR_JAB_CONNECT']);
						continue 2;
					}

					if (!$this->jabber->login())
					{
						$messenger = new messenger();
						$messenger->error('JABBER', $user->lang['ERR_JAB_AUTH']);
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
						$err_msg = '';
						$to = (!$to) ? 'undisclosed-recipients:;' : $to;

						if ($config['smtp_delivery'])
						{
							$result = smtpmail($addresses, mail_encode($subject), wordwrap(utf8_wordwrap($msg), 997, "\n", true), $err_msg, $headers);
						}
						else
						{
							$result = phpbb_mail($to, $subject, $msg, $headers, PHP_EOL, $err_msg);
						}

						if (!$result)
						{
							$messenger = new messenger();
							$messenger->error('EMAIL', $err_msg);
							continue 2;
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
					$this->filesystem->phpbb_chmod($this->cache_file, CHMOD_READ | CHMOD_WRITE);
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
	* Save queue
	*/
	function save()
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
				$this->filesystem->phpbb_chmod($this->cache_file, CHMOD_READ | CHMOD_WRITE);
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				// Do nothing
			}

			$this->data = array();
		}

		$lock->release();
	}
}

/**
* Replacement or substitute for PHP's mail command
*/
function smtpmail($addresses, $subject, $message, &$err_msg, $headers = false)
{
	global $config, $user;

	// Fix any bare linefeeds in the message to make it RFC821 Compliant.
	$message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);

	if ($headers !== false)
	{
		if (!is_array($headers))
		{
			// Make sure there are no bare linefeeds in the headers
			$headers = preg_replace('#(?<!\r)\n#si', "\n", $headers);
			$headers = explode("\n", $headers);
		}

		// Ok this is rather confusing all things considered,
		// but we have to grab bcc and cc headers and treat them differently
		// Something we really didn't take into consideration originally
		$headers_used = array();

		foreach ($headers as $header)
		{
			if (strpos(strtolower($header), 'cc:') === 0 || strpos(strtolower($header), 'bcc:') === 0)
			{
				continue;
			}
			$headers_used[] = trim($header);
		}

		$headers = chop(implode("\r\n", $headers_used));
	}

	if (trim($subject) == '')
	{
		$err_msg = (isset($user->lang['NO_EMAIL_SUBJECT'])) ? $user->lang['NO_EMAIL_SUBJECT'] : 'No email subject specified';
		return false;
	}

	if (trim($message) == '')
	{
		$err_msg = (isset($user->lang['NO_EMAIL_MESSAGE'])) ? $user->lang['NO_EMAIL_MESSAGE'] : 'Email message was blank';
		return false;
	}

	$mail_rcpt = $mail_to = $mail_cc = array();

	// Build correct addresses for RCPT TO command and the client side display (TO, CC)
	if (isset($addresses['to']) && count($addresses['to']))
	{
		foreach ($addresses['to'] as $which_ary)
		{
			$mail_to[] = ($which_ary['name'] != '') ? mail_encode(trim($which_ary['name'])) . ' <' . trim($which_ary['email']) . '>' : '<' . trim($which_ary['email']) . '>';
			$mail_rcpt['to'][] = '<' . trim($which_ary['email']) . '>';
		}
	}

	if (isset($addresses['bcc']) && count($addresses['bcc']))
	{
		foreach ($addresses['bcc'] as $which_ary)
		{
			$mail_rcpt['bcc'][] = '<' . trim($which_ary['email']) . '>';
		}
	}

	if (isset($addresses['cc']) && count($addresses['cc']))
	{
		foreach ($addresses['cc'] as $which_ary)
		{
			$mail_cc[] = ($which_ary['name'] != '') ? mail_encode(trim($which_ary['name'])) . ' <' . trim($which_ary['email']) . '>' : '<' . trim($which_ary['email']) . '>';
			$mail_rcpt['cc'][] = '<' . trim($which_ary['email']) . '>';
		}
	}

	$smtp = new smtp_class();

	$errno = 0;
	$errstr = '';

	$smtp->add_backtrace('Connecting to ' . $config['smtp_host'] . ':' . $config['smtp_port']);

	// Ok we have error checked as much as we can to this point let's get on it already.
	if (!class_exists('\phpbb\error_collector'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/error_collector.' . $phpEx);
	}
	$collector = new \phpbb\error_collector;
	$collector->install();

	$options = array();
	$verify_peer = (bool) $config['smtp_verify_peer'];
	$verify_peer_name = (bool) $config['smtp_verify_peer_name'];
	$allow_self_signed = (bool) $config['smtp_allow_self_signed'];
	$remote_socket = $config['smtp_host'] . ':' . $config['smtp_port'];

	// Set ssl context options, see http://php.net/manual/en/context.ssl.php
	$options['ssl'] = array('verify_peer' => $verify_peer, 'verify_peer_name' => $verify_peer_name, 'allow_self_signed' => $allow_self_signed);
	$socket_context = stream_context_create($options);

	$smtp->socket = @stream_socket_client($remote_socket, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $socket_context);
	$collector->uninstall();
	$error_contents = $collector->format_errors();

	if (!$smtp->socket)
	{
		if ($errstr)
		{
			$errstr = utf8_convert_message($errstr);
		}

		$err_msg = (isset($user->lang['NO_CONNECT_TO_SMTP_HOST'])) ? sprintf($user->lang['NO_CONNECT_TO_SMTP_HOST'], $errno, $errstr) : "Could not connect to smtp host : $errno : $errstr";
		$err_msg .= ($error_contents) ? '<br /><br />' . htmlspecialchars($error_contents) : '';
		return false;
	}

	// Wait for reply
	if ($err_msg = $smtp->server_parse('220', __LINE__))
	{
		$smtp->close_session($err_msg);
		return false;
	}

	// Let me in. This function handles the complete authentication process
	if ($err_msg = $smtp->log_into_server($config['smtp_host'], $config['smtp_username'], htmlspecialchars_decode($config['smtp_password']), $config['smtp_auth_method']))
	{
		$smtp->close_session($err_msg);
		return false;
	}

	// From this point onward most server response codes should be 250
	// Specify who the mail is from....
	$smtp->server_send('MAIL FROM:<' . $config['board_email'] . '>');
	if ($err_msg = $smtp->server_parse('250', __LINE__))
	{
		$smtp->close_session($err_msg);
		return false;
	}

	// Specify each user to send to and build to header.
	$to_header = implode(', ', $mail_to);
	$cc_header = implode(', ', $mail_cc);

	// Now tell the MTA to send the Message to the following people... [TO, BCC, CC]
	$rcpt = false;
	foreach ($mail_rcpt as $type => $mail_to_addresses)
	{
		foreach ($mail_to_addresses as $mail_to_address)
		{
			// Add an additional bit of error checking to the To field.
			if (preg_match('#[^ ]+\@[^ ]+#', $mail_to_address))
			{
				$smtp->server_send("RCPT TO:$mail_to_address");
				if ($err_msg = $smtp->server_parse('250', __LINE__))
				{
					// We continue... if users are not resolved we do not care
					if ($smtp->numeric_response_code != 550)
					{
						$smtp->close_session($err_msg);
						return false;
					}
				}
				else
				{
					$rcpt = true;
				}
			}
		}
	}

	// We try to send messages even if a few people do not seem to have valid email addresses, but if no one has, we have to exit here.
	if (!$rcpt)
	{
		$user->session_begin();
		$err_msg .= '<br /><br />';
		$err_msg .= (isset($user->lang['INVALID_EMAIL_LOG'])) ? sprintf($user->lang['INVALID_EMAIL_LOG'], htmlspecialchars($mail_to_address)) : '<strong>' . htmlspecialchars($mail_to_address) . '</strong> possibly an invalid email address?';
		$smtp->close_session($err_msg);
		return false;
	}

	// Ok now we tell the server we are ready to start sending data
	$smtp->server_send('DATA');

	// This is the last response code we look for until the end of the message.
	if ($err_msg = $smtp->server_parse('354', __LINE__))
	{
		$smtp->close_session($err_msg);
		return false;
	}

	// Send the Subject Line...
	$smtp->server_send("Subject: $subject");

	// Now the To Header.
	$to_header = ($to_header == '') ? 'undisclosed-recipients:;' : $to_header;
	$smtp->server_send("To: $to_header");

	// Now the CC Header.
	if ($cc_header != '')
	{
		$smtp->server_send("CC: $cc_header");
	}

	// Now any custom headers....
	if ($headers !== false)
	{
		$smtp->server_send("$headers\r\n");
	}

	// Ok now we are ready for the message...
	$smtp->server_send($message);

	// Ok the all the ingredients are mixed in let's cook this puppy...
	$smtp->server_send('.');
	if ($err_msg = $smtp->server_parse('250', __LINE__))
	{
		$smtp->close_session($err_msg);
		return false;
	}

	// Now tell the server we are done and close the socket...
	$smtp->server_send('QUIT');
	$smtp->close_session($err_msg);

	return true;
}

/**
* SMTP Class
* Auth Mechanisms originally taken from the AUTH Modules found within the PHP Extension and Application Repository (PEAR)
* See docs/AUTHORS for more details
*/
class smtp_class
{
	var $server_response = '';
	var $socket = 0;
	protected $socket_tls = false;
	var $responses = array();
	var $commands = array();
	var $numeric_response_code = 0;

	var $backtrace = false;
	var $backtrace_log = array();

	function smtp_class()
	{
		// Always create a backtrace for admins to identify SMTP problems
		$this->backtrace = true;
		$this->backtrace_log = array();
	}

	/**
	* Add backtrace message for debugging
	*/
	function add_backtrace($message)
	{
		if ($this->backtrace)
		{
			$this->backtrace_log[] = utf8_htmlspecialchars($message);
		}
	}

	/**
	* Send command to smtp server
	*/
	function server_send($command, $private_info = false)
	{
		fputs($this->socket, $command . "\r\n");

		(!$private_info) ? $this->add_backtrace("# $command") : $this->add_backtrace('# Omitting sensitive information');

		// We could put additional code here
	}

	/**
	* We use the line to give the support people an indication at which command the error occurred
	*/
	function server_parse($response, $line)
	{
		global $user;

		$this->server_response = '';
		$this->responses = array();
		$this->numeric_response_code = 0;

		while (substr($this->server_response, 3, 1) != ' ')
		{
			if (!($this->server_response = fgets($this->socket, 256)))
			{
				return (isset($user->lang['NO_EMAIL_RESPONSE_CODE'])) ? $user->lang['NO_EMAIL_RESPONSE_CODE'] : 'Could not get mail server response codes';
			}
			$this->responses[] = substr(rtrim($this->server_response), 4);
			$this->numeric_response_code = (int) substr($this->server_response, 0, 3);

			$this->add_backtrace("LINE: $line <- {$this->server_response}");
		}

		if (!(substr($this->server_response, 0, 3) == $response))
		{
			$this->numeric_response_code = (int) substr($this->server_response, 0, 3);
			return (isset($user->lang['EMAIL_SMTP_ERROR_RESPONSE'])) ? sprintf($user->lang['EMAIL_SMTP_ERROR_RESPONSE'], $line, $this->server_response) : "Ran into problems sending Mail at <strong>Line $line</strong>. Response: $this->server_response";
		}

		return 0;
	}

	/**
	* Close session
	*/
	function close_session(&$err_msg)
	{
		fclose($this->socket);

		if ($this->backtrace)
		{
			$message = '<h1>Backtrace</h1><p>' . implode('<br />', $this->backtrace_log) . '</p>';
			$err_msg .= $message;
		}
	}

	/**
	* Log into server and get possible auth codes if neccessary
	*/
	function log_into_server($hostname, $username, $password, $default_auth_method)
	{
		global $user;

		// Here we try to determine the *real* hostname (reverse DNS entry preferrably)
		$local_host = $user->host;

		if (function_exists('php_uname'))
		{
			$local_host = php_uname('n');

			// Able to resolve name to IP
			if (($addr = @gethostbyname($local_host)) !== $local_host)
			{
				// Able to resolve IP back to name
				if (($name = @gethostbyaddr($addr)) !== $addr)
				{
					$local_host = $name;
				}
			}
		}

		// If we are authenticating through pop-before-smtp, we
		// have to login ones before we get authenticated
		// NOTE: on some configurations the time between an update of the auth database takes so
		// long that the first email send does not work. This is not a biggie on a live board (only
		// the install mail will most likely fail) - but on a dynamic ip connection this might produce
		// severe problems and is not fixable!
		if ($default_auth_method == 'POP-BEFORE-SMTP' && $username && $password)
		{
			global $config;

			$errno = 0;
			$errstr = '';

			$this->server_send("QUIT");
			fclose($this->socket);

			$this->pop_before_smtp($hostname, $username, $password);
			$username = $password = $default_auth_method = '';

			// We need to close the previous session, else the server is not
			// able to get our ip for matching...
			if (!$this->socket = @fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 10))
			{
				if ($errstr)
				{
					$errstr = utf8_convert_message($errstr);
				}

				$err_msg = (isset($user->lang['NO_CONNECT_TO_SMTP_HOST'])) ? sprintf($user->lang['NO_CONNECT_TO_SMTP_HOST'], $errno, $errstr) : "Could not connect to smtp host : $errno : $errstr";
				return $err_msg;
			}

			// Wait for reply
			if ($err_msg = $this->server_parse('220', __LINE__))
			{
				$this->close_session($err_msg);
				return $err_msg;
			}
		}

		$hello_result = $this->hello($local_host);
		if (!is_null($hello_result))
		{
			return $hello_result;
		}

		// SMTP STARTTLS (RFC 3207)
		if (!$this->socket_tls)
		{
			$this->socket_tls = $this->starttls();

			if ($this->socket_tls)
			{
				// Switched to TLS
				// RFC 3207: "The client MUST discard any knowledge obtained from the server, [...]"
				// So say hello again
				$hello_result = $this->hello($local_host);

				if (!is_null($hello_result))
				{
					return $hello_result;
				}
			}
		}

		// If we are not authenticated yet, something might be wrong if no username and passwd passed
		if (!$username || !$password)
		{
			return false;
		}

		if (!isset($this->commands['AUTH']))
		{
			return (isset($user->lang['SMTP_NO_AUTH_SUPPORT'])) ? $user->lang['SMTP_NO_AUTH_SUPPORT'] : 'SMTP server does not support authentication';
		}

		// Get best authentication method
		$available_methods = explode(' ', $this->commands['AUTH']);

		// Define the auth ordering if the default auth method was not found
		$auth_methods = array('PLAIN', 'LOGIN', 'CRAM-MD5', 'DIGEST-MD5');
		$method = '';

		if (in_array($default_auth_method, $available_methods))
		{
			$method = $default_auth_method;
		}
		else
		{
			foreach ($auth_methods as $_method)
			{
				if (in_array($_method, $available_methods))
				{
					$method = $_method;
					break;
				}
			}
		}

		if (!$method)
		{
			return (isset($user->lang['NO_SUPPORTED_AUTH_METHODS'])) ? $user->lang['NO_SUPPORTED_AUTH_METHODS'] : 'No supported authentication methods';
		}

		$method = strtolower(str_replace('-', '_', $method));
		return $this->$method($username, $password);
	}

	/**
	* SMTP EHLO/HELO
	*
	* @return mixed		Null if the authentication process is supposed to continue
	*					False if already authenticated
	*					Error message (string) otherwise
	*/
	protected function hello($hostname)
	{
		// Try EHLO first
		$this->server_send("EHLO $hostname");
		if ($err_msg = $this->server_parse('250', __LINE__))
		{
			// a 503 response code means that we're already authenticated
			if ($this->numeric_response_code == 503)
			{
				return false;
			}

			// If EHLO fails, we try HELO
			$this->server_send("HELO $hostname");
			if ($err_msg = $this->server_parse('250', __LINE__))
			{
				return ($this->numeric_response_code == 503) ? false : $err_msg;
			}
		}

		foreach ($this->responses as $response)
		{
			$response = explode(' ', $response);
			$response_code = $response[0];
			unset($response[0]);
			$this->commands[$response_code] = implode(' ', $response);
		}
	}

	/**
	* SMTP STARTTLS (RFC 3207)
	*
	* @return bool		Returns true if TLS was started
	*					Otherwise false
	*/
	protected function starttls()
	{
		if (!function_exists('stream_socket_enable_crypto'))
		{
			return false;
		}

		if (!isset($this->commands['STARTTLS']))
		{
			return false;
		}

		$this->server_send('STARTTLS');

		if ($err_msg = $this->server_parse('220', __LINE__))
		{
			return false;
		}

		$result = false;
		$stream_meta = stream_get_meta_data($this->socket);

		if (socket_set_blocking($this->socket, 1))
		{
			$result = stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
			socket_set_blocking($this->socket, (int) $stream_meta['blocked']);
		}

		return $result;
	}

	/**
	* Pop before smtp authentication
	*/
	function pop_before_smtp($hostname, $username, $password)
	{
		global $user;

		if (!$this->socket = @fsockopen($hostname, 110, $errno, $errstr, 10))
		{
			if ($errstr)
			{
				$errstr = utf8_convert_message($errstr);
			}

			return (isset($user->lang['NO_CONNECT_TO_SMTP_HOST'])) ? sprintf($user->lang['NO_CONNECT_TO_SMTP_HOST'], $errno, $errstr) : "Could not connect to smtp host : $errno : $errstr";
		}

		$this->server_send("USER $username", true);
		if ($err_msg = $this->server_parse('+OK', __LINE__))
		{
			return $err_msg;
		}

		$this->server_send("PASS $password", true);
		if ($err_msg = $this->server_parse('+OK', __LINE__))
		{
			return $err_msg;
		}

		$this->server_send('QUIT');
		fclose($this->socket);

		return false;
	}

	/**
	* Plain authentication method
	*/
	function plain($username, $password)
	{
		$this->server_send('AUTH PLAIN');
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return ($this->numeric_response_code == 503) ? false : $err_msg;
		}

		$base64_method_plain = base64_encode("\0" . $username . "\0" . $password);
		$this->server_send($base64_method_plain, true);
		if ($err_msg = $this->server_parse('235', __LINE__))
		{
			return $err_msg;
		}

		return false;
	}

	/**
	* Login authentication method
	*/
	function login($username, $password)
	{
		$this->server_send('AUTH LOGIN');
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return ($this->numeric_response_code == 503) ? false : $err_msg;
		}

		$this->server_send(base64_encode($username), true);
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return $err_msg;
		}

		$this->server_send(base64_encode($password), true);
		if ($err_msg = $this->server_parse('235', __LINE__))
		{
			return $err_msg;
		}

		return false;
	}

	/**
	* cram_md5 authentication method
	*/
	function cram_md5($username, $password)
	{
		$this->server_send('AUTH CRAM-MD5');
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return ($this->numeric_response_code == 503) ? false : $err_msg;
		}

		$md5_challenge = base64_decode($this->responses[0]);
		$password = (strlen($password) > 64) ? pack('H32', md5($password)) : ((strlen($password) < 64) ? str_pad($password, 64, chr(0)) : $password);
		$md5_digest = md5((substr($password, 0, 64) ^ str_repeat(chr(0x5C), 64)) . (pack('H32', md5((substr($password, 0, 64) ^ str_repeat(chr(0x36), 64)) . $md5_challenge))));

		$base64_method_cram_md5 = base64_encode($username . ' ' . $md5_digest);

		$this->server_send($base64_method_cram_md5, true);
		if ($err_msg = $this->server_parse('235', __LINE__))
		{
			return $err_msg;
		}

		return false;
	}

	/**
	* digest_md5 authentication method
	* A real pain in the ***
	*/
	function digest_md5($username, $password)
	{
		global $config, $user;

		$this->server_send('AUTH DIGEST-MD5');
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return ($this->numeric_response_code == 503) ? false : $err_msg;
		}

		$md5_challenge = base64_decode($this->responses[0]);

		// Parse the md5 challenge - from AUTH_SASL (PEAR)
		$tokens = array();
		while (preg_match('/^([a-z-]+)=("[^"]+(?<!\\\)"|[^,]+)/i', $md5_challenge, $matches))
		{
			// Ignore these as per rfc2831
			if ($matches[1] == 'opaque' || $matches[1] == 'domain')
			{
				$md5_challenge = substr($md5_challenge, strlen($matches[0]) + 1);
				continue;
			}

			// Allowed multiple "realm" and "auth-param"
			if (!empty($tokens[$matches[1]]) && ($matches[1] == 'realm' || $matches[1] == 'auth-param'))
			{
				if (is_array($tokens[$matches[1]]))
				{
					$tokens[$matches[1]][] = preg_replace('/^"(.*)"$/', '\\1', $matches[2]);
				}
				else
				{
					$tokens[$matches[1]] = array($tokens[$matches[1]], preg_replace('/^"(.*)"$/', '\\1', $matches[2]));
				}
			}
			else if (!empty($tokens[$matches[1]])) // Any other multiple instance = failure
			{
				$tokens = array();
				break;
			}
			else
			{
				$tokens[$matches[1]] = preg_replace('/^"(.*)"$/', '\\1', $matches[2]);
			}

			// Remove the just parsed directive from the challenge
			$md5_challenge = substr($md5_challenge, strlen($matches[0]) + 1);
		}

		// Realm
		if (empty($tokens['realm']))
		{
			$tokens['realm'] = (function_exists('php_uname')) ? php_uname('n') : $user->host;
		}

		// Maxbuf
		if (empty($tokens['maxbuf']))
		{
			$tokens['maxbuf'] = 65536;
		}

		// Required: nonce, algorithm
		if (empty($tokens['nonce']) || empty($tokens['algorithm']))
		{
			$tokens = array();
		}
		$md5_challenge = $tokens;

		if (!empty($md5_challenge))
		{
			$str = '';
			for ($i = 0; $i < 32; $i++)
			{
				$str .= chr(mt_rand(0, 255));
			}
			$cnonce = base64_encode($str);

			$digest_uri = 'smtp/' . $config['smtp_host'];

			$auth_1 = sprintf('%s:%s:%s', pack('H32', md5(sprintf('%s:%s:%s', $username, $md5_challenge['realm'], $password))), $md5_challenge['nonce'], $cnonce);
			$auth_2 = 'AUTHENTICATE:' . $digest_uri;
			$response_value = md5(sprintf('%s:%s:00000001:%s:auth:%s', md5($auth_1), $md5_challenge['nonce'], $cnonce, md5($auth_2)));

			$input_string = sprintf('username="%s",realm="%s",nonce="%s",cnonce="%s",nc="00000001",qop=auth,digest-uri="%s",response=%s,%d', $username, $md5_challenge['realm'], $md5_challenge['nonce'], $cnonce, $digest_uri, $response_value, $md5_challenge['maxbuf']);
		}
		else
		{
			return (isset($user->lang['INVALID_DIGEST_CHALLENGE'])) ? $user->lang['INVALID_DIGEST_CHALLENGE'] : 'Invalid digest challenge';
		}

		$base64_method_digest_md5 = base64_encode($input_string);
		$this->server_send($base64_method_digest_md5, true);
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return $err_msg;
		}

		$this->server_send(' ');
		if ($err_msg = $this->server_parse('235', __LINE__))
		{
			return $err_msg;
		}

		return false;
	}
}

/**
* Encodes the given string for proper display in UTF-8.
*
* This version is using base64 encoded data. The downside of this
* is if the mail client does not understand this encoding the user
* is basically doomed with an unreadable subject.
*
* Please note that this version fully supports RFC 2045 section 6.8.
*
* @param string $eol End of line we are using (optional to be backwards compatible)
*/
function mail_encode($str, $eol = "\r\n")
{
	// define start delimimter, end delimiter and spacer
	$start = "=?UTF-8?B?";
	$end = "?=";
	$delimiter = "$eol ";

	// Maximum length is 75. $split_length *must* be a multiple of 4, but <= 75 - strlen($start . $delimiter . $end)!!!
	$split_length = 60;
	$encoded_str = base64_encode($str);

	// If encoded string meets the limits, we just return with the correct data.
	if (strlen($encoded_str) <= $split_length)
	{
		return $start . $encoded_str . $end;
	}

	// If there is only ASCII data, we just return what we want, correctly splitting the lines.
	if (strlen($str) === utf8_strlen($str))
	{
		return $start . implode($end . $delimiter . $start, str_split($encoded_str, $split_length)) . $end;
	}

	// UTF-8 data, compose encoded lines
	$array = utf8_str_split($str);
	$str = '';

	while (count($array))
	{
		$text = '';

		while (count($array) && intval((strlen($text . $array[0]) + 2) / 3) << 2 <= $split_length)
		{
			$text .= array_shift($array);
		}

		$str .= $start . base64_encode($text) . $end . $delimiter;
	}

	return substr($str, 0, -strlen($delimiter));
}

/**
* Wrapper for sending out emails with the PHP's mail function
*/
function phpbb_mail($to, $subject, $msg, $headers, $eol, &$err_msg)
{
	global $config, $phpbb_root_path, $phpEx;

	// We use the EOL character for the OS here because the PHP mail function does not correctly transform line endings. On Windows SMTP is used (SMTP is \r\n), on UNIX a command is used...
	// Reference: http://bugs.php.net/bug.php?id=15841
	$headers = implode($eol, $headers);

	if (!class_exists('\phpbb\error_collector'))
	{
		include($phpbb_root_path . 'includes/error_collector.' . $phpEx);
	}

	$collector = new \phpbb\error_collector;
	$collector->install();

	// On some PHP Versions mail() *may* fail if there are newlines within the subject.
	// Newlines are used as a delimiter for lines in mail_encode() according to RFC 2045 section 6.8.
	// Because PHP can't decide what is wanted we revert back to the non-RFC-compliant way of separating by one space (Use '' as parameter to mail_encode() results in SPACE used)
	$additional_parameters = $config['email_force_sender'] ? '-f' . $config['board_email'] : '';
	$result = mail($to, mail_encode($subject, ''), wordwrap(utf8_wordwrap($msg), 997, "\n", true), $headers, $additional_parameters);

	$collector->uninstall();
	$err_msg = $collector->format_errors();

	return $result;
}
