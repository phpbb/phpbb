<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package phpBB3
* Messenger
*/
class messenger
{
	var $vars, $msg, $extra_headers, $replyto, $from, $subject, $necoding;
	var $addresses = array();

	var $mail_priority = MAIL_NORMAL_PRIORITY;
	var $use_queue = true;
	var $tpl_msg = array();

	function messenger($use_queue = true)
	{
		global $config;

		if (preg_match('#^[c-z]:\\\#i', getenv('PATH')) && !$config['smtp_delivery'] && phpversion() < '4.3')
		{
			// We are running on windows, force delivery to use our smtp functions since php's are broken by default
			$config['smtp_delivery'] = 1;
			$config['smtp_host'] = @ini_get('SMTP');
		}

		$this->use_queue = $use_queue;
		$this->subject = '';
	}

	// Resets all the data (address, template file, etc etc) to default
	function reset()
	{
		$this->addresses = array();
		$this->vars = $this->msg = $this->extra_headers = $this->replyto = $this->from = $this->encoding = '';
		$this->mail_priority = MAIL_NORMAL_PRIORITY;
	}

	// Sets an email address to send to
	function to($address, $realname = '')
	{
		$pos = isset($this->addresses['to']) ? sizeof($this->addresses['to']) : 0;
		$this->addresses['to'][$pos]['email'] = trim($address);
		$this->addresses['to'][$pos]['name'] = trim($realname);
	}

	function cc($address, $realname = '')
	{
		$pos = isset($this->addresses['cc']) ? sizeof($this->addresses['cc']) : 0;
		$this->addresses['cc'][$pos]['email'] = trim($address);
		$this->addresses['cc'][$pos]['name'] = trim($realname);
	}

	function bcc($address, $realname = '')
	{
		$pos = isset($this->addresses['bcc']) ? sizeof($this->addresses['bcc']) : 0;
		$this->addresses['bcc'][$pos]['email'] = trim($address);
		$this->addresses['bcc'][$pos]['name'] = trim($realname);
	}

	function im($address, $realname = '')
	{
		$pos = isset($this->addresses['im']) ? sizeof($this->addresses['im']) : 0;
		$this->addresses['im'][$pos]['uid'] = trim($address);
		$this->addresses['im'][$pos]['name'] = trim($realname);
	}

	function replyto($address)
	{
		$this->replyto = trim($address);
	}

	function from($address)
	{
		$this->from = trim($address);
	}

	// set up subject for mail
	function subject($subject = '')
	{
		$this->subject = trim($subject);
	}

	// set up extra mail headers
	function headers($headers)
	{
		$this->extra_headers .= trim($headers) . "\n";
	}

	function set_mail_priority($priority = MAIL_NORMAL_PRIORITY)
	{
		$this->mail_priority = $priority;
	}

	function template($template_file, $template_lang = '')
	{
		global $config, $phpbb_root_path;

		if (!trim($template_file))
		{
			trigger_error('No template file set', E_USER_ERROR);
		}

		if (!trim($template_lang))
		{
			$template_lang = $config['default_lang'];
		}

		if (empty($this->tpl_msg[$template_lang . $template_file]))
		{
			$tpl_file = "{$phpbb_root_path}language/$template_lang/email/$template_file.txt";

			if (!file_exists($tpl_file))
			{
				$tpl_file = "{$phpbb_root_path}language/$template_lang/email/$template_file.txt";

				if (!file_exists($tpl_file))
				{
					trigger_error("Could not find email template file [ $template_file ]", E_USER_ERROR);
				}
			}

			if (!($fd = @fopen($tpl_file, 'r')))
			{
				trigger_error("Failed opening template file [ $template_file ]", E_USER_ERROR);
			}

			$this->tpl_msg[$template_lang . $template_file] = fread($fd, filesize($tpl_file));
			fclose($fd);
		}

		$this->msg = $this->tpl_msg[$template_lang . $template_file];

		return true;
	}

	// assign variables
	function assign_vars($vars)
	{
		$this->vars = (empty($this->vars)) ? $vars : $this->vars . $vars;
	}

	// Send the mail out to the recipients set previously in var $this->address
	function send($method = NOTIFY_EMAIL, $break = false)
	{
		global $config, $user;

		// Escape all quotes, else the eval will fail.
		$this->msg = str_replace ("'", "\'", $this->msg);
		$this->msg = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", $this->msg);

		// Set vars
		foreach ($this->vars as $key => $val)
		{
			$$key = $val;
		}

		eval("\$this->msg = '$this->msg';");

		// Clear vars
		foreach ($this->vars as $key => $val)
		{
			unset($$key);
		}

		// We now try and pull a subject from the email body ... if it exists,
		// do this here because the subject may contain a variable
		$drop_header = '';
		$match = array();
		if (preg_match('#^(Subject:(.*?))$#m', $this->msg, $match))
		{
			$this->subject = (trim($match[2]) != '') ? trim($match[2]) : (($this->subject != '') ? $this->subject : $user->lang['NO_SUBJECT']);
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->subject = (($this->subject != '') ? $this->subject : $user->lang['NO_SUBJECT']);
		}

		if (preg_match('#^(Charset:(.*?))$#m', $this->msg, $match))
		{
			$this->encoding = (trim($match[2]) != '') ? trim($match[2]) : trim($user->lang['ENCODING']);
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->encoding = trim($user->lang['ENCODING']);
		}

		if ($drop_header)
		{
			$this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
		}

		if ($break)
		{
			return;
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

	function error($type, $msg)
	{
		global $user, $phpEx, $phpbb_root_path;

		// Session doesn't exist, create it
		$user->session_begin();

		include_once($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		add_log('critical', 'LOG_' . $type . '_ERROR', $msg);
	}

	//
	// Messenger methods
	//
	function save_queue()
	{
		global $config;

		if ($config['email_package_size'] && $this->use_queue && !empty($this->queue))
		{
			$this->queue->save();
		}
	}

	function msg_email()
	{
		global $config, $user;

		if (empty($config['email_enable']))
		{
			return false;
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

		$to = $cc = $bcc = '';
		// Build to, cc and bcc strings
		foreach ($this->addresses as $type => $address_ary)
		{
			if ($type == 'im')
			{
				continue;
			}

			foreach ($address_ary as $which_ary)
			{
				$$type .= (($$type != '') ? ', ' : '') . (($which_ary['name'] != '') ?  '"' . mail_encode($which_ary['name'], $this->encoding) . '" <' . $which_ary['email'] . '>' : $which_ary['email']);
			}
		}

		if (empty($this->replyto))
		{
			$this->replyto = '<' . $config['board_email'] . '>';
		}

		if (empty($this->from))
		{
			$this->from = '<' . $config['board_email'] . '>';
		}

		// Build header
		$headers = 'From: ' . $this->from . "\n";
		$headers .= ($cc != '') ? "Cc: $cc\n" : '';
		$headers .= ($bcc != '') ? "Bcc: $bcc\n" : ''; 
		$headers .= 'Reply-to: ' . $this->replyto . "\n";
		$headers .= 'Return-Path: <' . $config['board_email'] . ">\n";
		$headers .= 'Sender: <' . $config['board_email'] . ">\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= 'Message-ID: <' . md5(uniqid(time())) . "@" . $config['server_name'] . ">\n";
		$headers .= 'Date: ' . gmdate('D, d M Y H:i:s T', time()) . "\n";
		$headers .= "Content-type: text/plain; charset={$this->encoding}\n";
		$headers .= "Content-transfer-encoding: 8bit\n";
		$headers .= "X-Priority: {$this->mail_priority}\n";
		$headers .= 'X-MSMail-Priority: ' . (($this->mail_priority == MAIL_LOW_PRIORITY) ? 'Low' : (($this->mail_priority == MAIL_NORMAL_PRIORITY) ? 'Normal' : 'High')) . "\n";
		$headers .= "X-Mailer: PhpBB\n";
		$headers .= "X-MimeOLE: phpBB\n";
		$headers .= "X-phpBB-Origin: phpbb://" . str_replace(array('http://', 'https://'), array('', ''), generate_board_url()) . "\n";
		$headers .= ($this->extra_headers != '') ? $this->extra_headers : '';

		// Send message ... removed $this->encode() from subject for time being
		if (!$use_queue)
		{
			$mail_to = ($to == '') ? 'Undisclosed-Recipient:;' : $to;
			$err_msg = '';

			$result = ($config['smtp_delivery']) ? smtpmail($this->addresses, $this->subject, wordwrap($this->msg), $err_msg, $this->encoding, $headers) : @$config['email_function_name']($mail_to, $this->subject, implode("\n", preg_split("/\r?\n/", wordwrap($this->msg))), $headers);

			if (!$result)
			{
				$message = '<u>EMAIL ERROR</u> [ ' . (($config['smtp_delivery']) ? 'SMTP' : 'PHP') . ' ]<br /><br />' . $err_msg . '<br /><br /><u>CALLING PAGE</u><br /><br />'  . ((!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF']) . '<br />';
				
				$this->error('EMAIL', $message);
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
				'encoding'		=> $this->encoding,
				'headers'		=> $headers)
			);
		}

		return true;
	}

	function msg_jabber()
	{
		global $config, $db, $user, $phpbb_root_path, $phpEx;

		if (empty($config['jab_enable']) || empty($config['jab_host']) || empty($config['jab_username']) || empty($config['jab_password']))
		{
			return false;
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
			include_once($phpbb_root_path . 'includes/functions_jabber.'.$phpEx);
			$this->jabber = new jabber;

			$this->jabber->server	= $config['jab_host'];
			$this->jabber->port		= ($config['jab_port']) ? $config['jab_port'] : 5222;
			$this->jabber->username = $config['jab_username'];
			$this->jabber->password = $config['jab_password'];
			$this->jabber->resource = ($config['jab_resource']) ? $config['jab_resource'] : '';

			if (!$this->jabber->connect())
			{
				$this->error('JABBER', 'Could not connect to Jabber server');
				return false;
			}

			if (!$this->jabber->send_auth())
			{
				$this->error('JABBER', 'Could not authorise on Jabber server');
				return false;
			}
			$this->jabber->send_presence(NULL, NULL, 'online');

			foreach ($addresses as $address)
			{
				$this->jabber->send_message($address, 'normal', NULL, array('body' => $msg));
			}

			sleep(1);
			$this->jabber->disconnect();
		}
		else
		{
			$this->queue->put('jabber', array(
				'addresses'		=> $addresses,
				'subject'		=> htmlentities($this->subject),
				'msg'			=> htmlentities($this->msg))
			);
		}
		unset($addresses);
		return true;
	}
}

/**
* @package phpBB3
* Queue
* At the moment it is only handling the email queue
*/
class queue
{
	var $data = array();
	var $queue_data = array();
	var $package_size = 0;
	var $cache_file = '';

	function queue()
	{
		global $phpEx, $phpbb_root_path;

		$this->data = array();
		$this->cache_file = "{$phpbb_root_path}cache/queue.$phpEx";
	}
	
	function init($object, $package_size)
	{
		$this->data[$object] = array();
		$this->data[$object]['package_size'] = $package_size;
		$this->data[$object]['data'] = array();
	}

	function put($object, $scope)
	{
		$this->data[$object]['data'][] = $scope;
	}

	// Using lock file...
	function process()
	{
		global $db, $config, $phpEx, $phpbb_root_path;

		set_config('last_queue_run', time(), true);

		// Delete stale lock file
		if (file_exists($this->cache_file . '.lock') && !file_exists($this->cache_file))
		{
			@unlink($this->cache_file . '.lock');
			return;
		}

		if (!file_exists($this->cache_file) || (file_exists($this->cache_file . '.lock') && filemtime($this->cache_file) > time() - $config['queue_interval']))
		{
			return;
		}

		$fp = @fopen($this->cache_file . '.lock', 'wb');
		fclose($fp);

		include($this->cache_file);

		foreach ($this->queue_data as $object => $data_ary)
		{
			@set_time_limit(0);

			if (!isset($data_ary['package_size']))
			{
				$data_ary['package_size'] = 0;
			}

			$package_size = $data_ary['package_size'];
			$num_items = (sizeof($data_ary['data']) < $package_size) ? sizeof($data_ary['data']) : $package_size;

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

					include_once($phpbb_root_path . 'includes/functions_jabber.'.$phpEx);
					$this->jabber = new jabber;

					$this->jabber->server	= $config['jab_host'];
					$this->jabber->port		= ($config['jab_port']) ? $config['jab_port'] : 5222;
					$this->jabber->username = $config['jab_username'];
					$this->jabber->password = $config['jab_password'];
					$this->jabber->resource = ($config['jab_resource']) ? $config['jab_resource'] : '';

					if (!$this->jabber->connect())
					{
						messenger::error('JABBER', 'Could not connect to Jabber server');
						continue 2;
					}

					if (!$this->jabber->send_auth())
					{
						messenger::error('JABBER', 'Could not authorise on Jabber server');
						continue 2;
					}
					$this->jabber->send_presence(NULL, NULL, 'online');
					break;

				default:
					return;
			}

			for ($i = 0; $i < $num_items; $i++)
			{
				extract(array_shift($this->queue_data[$object]['data']));

				switch ($object)
				{
					case 'email':
						$err_msg = '';
						$to = (!$to) ? 'Undisclosed-Recipient:;' : $to;

						$result = ($config['smtp_delivery']) ? smtpmail($addresses, $subject, wordwrap($msg), $err_msg, $encoding, $headers) : @$config['email_function_name']($to, $subject, implode("\n", preg_split("/\r?\n/", wordwrap($msg))), $headers);

						if (!$result)
						{
							@unlink($this->cache_file . '.lock');

							$message = 'Method: [ ' . (($config['smtp_delivery']) ? 'SMTP' : 'PHP') . ' ]<br /><br />' . $err_msg . '<br /><br /><u>CALLING PAGE</u><br /><br />'  . ((!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF']);
							messenger::error('MAIL', $message);
							continue 3;
						}
						break;

					case 'jabber':
						foreach ($addresses as $address)
						{
							$this->jabber->send_message($address, 'normal', NULL, array('body' => $msg));
						}
						break;
				}
			}

			// No more data for this object? Unset it
			if (!sizeof($this->queue_data[$object]['data']))
			{
				unset($this->queue_data[$object]);
			}

			// Post-object processing
			switch ($object)
			{
				case 'jabber':
					// Hang about a couple of secs to ensure the messages are
					// handled, then disconnect
					sleep(1);
					$this->jabber->disconnect();
					break;
			}
		}
	
		if (!sizeof($this->queue_data))
		{
			@unlink($this->cache_file);
		}
		else
		{
			$file = '<?php $this->queue_data=' . $this->format_array($this->queue_data) . '; ?>';

			if ($fp = @fopen($this->cache_file, 'w'))
			{
				@flock($fp, LOCK_EX);
				fwrite($fp, $file);
				@flock($fp, LOCK_UN);
				fclose($fp);
			}
		}

		@unlink($this->cache_file . '.lock');
	}

	function save()
	{
		if (!sizeof($this->data))
		{
			return;
		}
		
		if (file_exists($this->cache_file))
		{
			include($this->cache_file);
			
			foreach ($this->queue_data as $object => $data_ary)
			{
				if (isset($this->data[$object]) && sizeof($this->data[$object]))
				{
					$this->data[$object]['data'] = array_merge($data_ary['data'], $this->data[$object]['data']);
				}
				else
				{
					$this->data[$object]['data'] = $data_ary['data'];
				}
			}
		}
		
		$file = '<?php $this->queue_data = ' . $this->format_array($this->data) . '; ?>';

		if ($fp = fopen($this->cache_file, 'w'))
		{
			@flock($fp, LOCK_EX);
			fwrite($fp, $file);
			@flock($fp, LOCK_UN);
			fclose($fp);
		}
	}

	function format_array($array)
	{
		$lines = array();
		foreach ($array as $k => $v)
		{
			if (is_array($v))
			{
				$lines[] = "'$k'=>" . $this->format_array($v);
			}
			elseif (is_int($v))
			{
				$lines[] = "'$k'=>$v";
			}
			elseif (is_bool($v))
			{
				$lines[] = "'$k'=>" . (($v) ? 'TRUE' : 'FALSE');
			}
			else
			{
				$lines[] = "'$k'=>'" . str_replace("'", "\'", str_replace('\\', '\\\\', $v)) . "'";
			}
		}

		return 'array(' . implode(',', $lines) . ')';
	}

}

/**
* Replacement or substitute for PHP's mail command
*/
function smtpmail($addresses, $subject, $message, &$err_msg, $encoding, $headers = '')
{
	global $config, $user;

	// Fix any bare linefeeds in the message to make it RFC821 Compliant.
	$message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);

	if ($headers != '')
	{
		if (is_array($headers))
		{
			$headers = (sizeof($headers) > 1) ? join("\n", $headers) : $headers[0];
		}
		$headers = chop($headers);

		// Make sure there are no bare linefeeds in the headers
		$headers = preg_replace('#(?<!\r)\n#si', "\r\n", $headers);

		// Ok this is rather confusing all things considered,
		// but we have to grab bcc and cc headers and treat them differently
		// Something we really didn't take into consideration originally
		$header_array = explode("\r\n", $headers);
		$headers = '';
		
		foreach ($header_array as $header)
		{
			if (preg_match('#^cc:#si', $header) || preg_match('#^bcc:#si', $header))
			{
				$header = '';
			}
			$headers .= ($header != '') ? $header . "\r\n" : '';
		}

		$headers = chop($headers);
	}

	if (trim($subject) == '')
	{
		$err_msg = 'No email Subject specified';
		return false;
	}

	if (trim($message) == '')
	{
		$err_msg = 'Email message was blank';
		return false;
	}

	$mail_rcpt = $mail_to = $mail_cc = array();

	// Build correct addresses for RCPT TO command and the client side display (TO, CC)
	foreach ($addresses['to'] as $which_ary)
	{
		$mail_to[] = ($which_ary['name'] != '') ? mail_encode(trim($which_ary['name']), $encoding) . ' <' . trim($which_ary['email']) . '>' : '<' . trim($which_ary['email']) . '>';
		$mail_rcpt['to'][] = '<' . trim($which_ary['email']) . '>';
	}

	if (isset($addresses['bcc']) && sizeof($addresses['bcc']))
	{
		foreach ($addresses['bcc'] as $which_ary)
		{
			$mail_rcpt['bcc'][] = '<' . trim($which_ary['email']) . '>';
		}
	}

	if (isset($addresses['cc']) && sizeof($addresses['cc']))
	{
		foreach ($addresses['cc'] as $which_ary)
		{
			$mail_cc[] = ($which_ary['name'] != '') ? mail_encode(trim($which_ary['name']), $encoding) . ' <' . trim($which_ary['email']) . '>' : '<' . trim($which_ary['email']) . '>';
			$mail_rcpt['cc'][] = '<' . trim($which_ary['email']) . '>';
		}
	}

	$smtp = new smtp_class;

	// Ok we have error checked as much as we can to this point let's get on
	// it already.
	if (!$smtp->socket = fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 20))
	{
		$err_msg = "Could not connect to smtp host : $errno : $errstr";
		return false;
	}

	// Wait for reply
	if ($err_msg = $smtp->server_parse('220', __LINE__))
	{
		$smtp->close_session();
		return false;
	}

	// Let me in. This function handles the complete authentication process
	if ($err_msg = $smtp->log_into_server($config['smtp_host'], $config['smtp_username'], $config['smtp_password'], $config['smtp_auth_method']))
	{
		$smtp->close_session();
		return false;
	}

	// From this point onward most server response codes should be 250
	// Specify who the mail is from....
	$smtp->server_send('MAIL FROM:<' . $config['board_email'] . '>');
	if ($err_msg = $smtp->server_parse('250', __LINE__))
	{
		$smtp->close_session();
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
						$smtp->close_session();
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
		$err_msg .= '<br /><br />' . sprintf($user->lang['INVALID_EMAIL_LOG'], htmlspecialchars($mail_to_address));
		$smtp->close_session();
		return false;
	}

	// Ok now we tell the server we are ready to start sending data
	$smtp->server_send('DATA');

	// This is the last response code we look for until the end of the message.
	if ($err_msg = $smtp->server_parse('354', __LINE__))
	{
		$smtp->close_session();
		return false;
	}

	// Send the Subject Line...
	$smtp->server_send("Subject: $subject");

	// Now the To Header.
	$to_header = ($to_header == '') ? 'Undisclosed-Recipients:;' : $to_header;
	$smtp->server_send("To: $to_header");

	// Now the CC Header.
	if ($cc_header != '')
	{
		$smtp->server_send("CC: $cc_header");
	}

	// Now any custom headers....
	$smtp->server_send("$headers\r\n");

	// Ok now we are ready for the message...
	$smtp->server_send($message);

	// Ok the all the ingredients are mixed in let's cook this puppy...
	$smtp->server_send('.');
	if ($err_msg = $smtp->server_parse('250', __LINE__))
	{
		$smtp->close_session();
		return false;
	}

	// Now tell the server we are done and close the socket...
	$smtp->server_send('QUIT');
	$smtp->close_session();

	return true;
}

/**
* @package phpBB3
* SMTP Class
* Auth Mechanisms originally taken from the AUTH Modules found within the PHP Extension and Application Repository (PEAR)
* See docs/AUTHORS for more details
*/
class smtp_class
{
	var $server_response = '';
	var $socket = 0;
	var $responses = array();
	var $commands = array();
	var $numeric_response_code = 0;

	// Send command to smtp server
	function server_send($command)
	{
		fputs($this->socket, $command . "\r\n");

		// We could put additional code here
	}
	
	// We use the line to give the support people an indication at which command the error occurred
	function server_parse($response, $line)
	{
		$this->server_response = '';
		$this->responses = array();
		$this->numeric_response_code = 0;

		while (substr($this->server_response, 3, 1) != ' ')
		{
			if (!($this->server_response = fgets($this->socket, 256)))
			{
				return 'Could not get mail server response codes';
			}
			$this->responses[] = substr(rtrim($this->server_response), 4);
			$this->numeric_response_code = (int) substr($this->server_response, 0, 3);
		}

		if (!(substr($this->server_response, 0, 3) == $response))
		{
			$this->numeric_response_code = (int) substr($this->server_response, 0, 3);
			return "Ran into problems sending Mail at <b>Line $line</b>. Response: $this->server_response";
		}

		return 0;
	}

	function close_session()
	{
		fclose($this->socket);
	}
	
	// Log into server and get possible auth codes if neccessary
	function log_into_server($hostname, $username, $password, $default_auth_method)
	{
		$err_msg = '';

		// If we are authenticating through pop-before-smtp, we
		// have to login ones before we get authenticated
		if ($default_auth_method == 'POP-BEFORE-SMTP' && $username && $password)
		{
			$result = $this->pop_before_smtp($hostname, $username, $password);
			$username = $password = $default_auth_method = '';
		}

		// Try EHLO first
		$this->server_send("EHLO [$hostname]");
		if ($err_msg = $this->server_parse('250', __LINE__))
		{
			// a 503 response code means that we're already authenticated
			if ($this->numeric_response_code == 503)
			{
				return false;
			}

			// If EHLO fails, we try HELO			
			$this->server_send("HELO [$hostname]");
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

		// If we are not authenticated yet, something might be wrong if no username and passwd passed
		if (!$username || !$password)
		{
			return false;
		}
		
		if (!isset($this->commands['AUTH']))
		{
			return 'SMTP server does not support authentication';
		}

		// Get best authentication method
		$available_methods = explode(' ', $this->commands['AUTH']);

		// Define the auth ordering if the default auth method was not found
		$auth_methods = array('PLAIN', 'LOGIN', 'CRAM-MD5');
		if (function_exists('posix_uname'))
		{
			$auth_methods[] = 'DIGEST-MD5';
		}

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
			return 'No supported authentication methods';
		}

		$method = strtolower(str_replace('-', '_', $method));
		return $this->$method($username, $password);
	}

	function pop_before_smtp($hostname, $username, $password)
	{
		$old_socket = $this->socket;
		
		if (!$this->socket = fsockopen($hostname, 110, $errno, $errstr, 20))
		{
			$this->socket = $old_socket;
			return "Could not connect to smtp host : $errno : $errstr";
		}
		
		$this->server_parse('0', __LINE__);
		if (substr($this->server_response, 0, 3) == '+OK')
		{
			fputs($this->socket, "USER $username\r\n");
			fputs($this->socket, "PASS $password\r\n");
		}
		else
		{
			$this->socket = $old_socket;
			return $this->responses[0];
		}

		$this->server_send('QUIT');
		$this->server_parse('0', __LINE__);
		fclose($this->socket);

		$this->socket = $old_socket;

		return false;
	}
	
	function plain($username, $password)
	{
		$this->server_send('AUTH PLAIN');
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return ($this->numeric_response_code == 503) ? false : $err_msg;
		}

		$base64_method_plain = base64_encode("\0" . $username . "\0" . $password);
		$this->server_send($base64_method_plain);
		if ($err_msg = $this->server_parse('235', __LINE__))
		{
			return $err_msg;
		}

		return false;
	}

	function login($username, $password)
	{
		$this->server_send('AUTH LOGIN');
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return ($this->numeric_response_code == 503) ? false : $err_msg;
		}

		$this->server_send(base64_encode($username));
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return $err_msg;
		}

		$this->server_send(base64_encode($password));
		if ($err_msg = $this->server_parse('235', __LINE__))
		{
			return $err_msg;
		}

		return false;
	}

	// The last two authentication mechanisms are a little bit tricky...
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

		$this->server_send($base64_method_cram_md5);
		if ($err_msg = $this->server_parse('235', __LINE__))
		{
			return $err_msg;
		}

		return false;
	}

	// A real pain in the ***
	function digest_md5($username, $password)
	{
		global $config;

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
			$uname = posix_uname();
			$tokens['realm'] = $uname['nodename'];
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
			mt_srand( (double) microtime() * 10000000);
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
			return 'Invalid digest challenge';
		}
		
		$base64_method_digest_md5 = base64_encode($input_string);
		$this->server_send($base64_method_digest_md5);
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
* Encodes the given string for proper display for this encoding ... nabbed 
* from php.net and modified. There is an alternative encoding method which 
* may produce less output but it's questionable as to its worth in this 
* scenario IMO
*/
function mail_encode($str, $encoding)
{
	if ($encoding == '')
	{
		return $str;
	}

	// define start delimimter, end delimiter and spacer
	$end = "?=";
	$start = "=?$encoding?B?";
	$spacer = "$end\r\n $start";

	// determine length of encoded text within chunks and ensure length is even
	$length = 75 - strlen($start) - strlen($end);
	$length = floor($length / 2) * 2;

	// encode the string and split it into chunks with spacers after each chunk
	$str = chunk_split(base64_encode($str), $length, $spacer);

	// remove trailing spacer and add start and end delimiters
	$str = preg_replace('#' . preg_quote($spacer) . '$#', '', $str);

	return $start . $str . $end;
}

?>