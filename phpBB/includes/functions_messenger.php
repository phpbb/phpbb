<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : functions_messenger.php 
// STARTED   : Sat Feb 13, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

class messenger
{
	var $msg, $subject, $extra_headers;
	var $to_addres, $cc_address, $bcc_address, $reply_to, $from;
	var $queue, $jabber;

	var $tpl_msg = array();

	function messenger()
	{
		global $config;

		if (preg_match('#^[c-z]:\\\#i', getenv('PATH')) && !$config['smtp_delivery'] && phpversion() < '4.3')
		{
			// We are running on windows, force delivery to use our smtp functions since php's are broken by default
			$config['smtp_delivery'] = 1;
			$config['smtp_host'] = @ini_get('SMTP');
		}
	}

	// Resets all the data (address, template file, etc etc to default
	function reset()
	{
		$this->addresses = array();
		$this->vars = $this->msg = $this->extra_headers = $this->replyto = $this->from = '';
	}

	// Sets an email address to send to
	function to($address, $realname = '')
	{
		$pos = sizeof($this->addresses['to']);
		$this->addresses['to'][$pos]['email'] = trim($address);
	}

	function cc($address, $realname = '')
	{
		$pos = sizeof($this->addresses['cc']);
		$this->addresses['cc'][$pos]['email'] = trim($address);
//		$this->addresses['cc'][$pos]['name'] = trim($realname);
	}

	function bcc($address, $realname = '')
	{
		$pos = sizeof($this->addresses['bcc']);
		$this->addresses['bcc'][$pos]['email'] = trim($address);
//		$this->addresses['bcc'][$pos]['name'] = trim($realname);
	}

	function im($address, $realname = '')
	{
		$pos = sizeof($this->addresses['im']);
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

		if (empty($this->tpl_msg["$template_lang$template_file"]))
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

			$this->tpl_msg["$template_lang$template_file"] = fread($fd, filesize($tpl_file));
			fclose($fd);
		}

		$this->msg = $this->tpl_msg["$template_lang$template_file"];

		return true;
	}

	// assign variables
	function assign_vars($vars)
	{
		$this->vars = (empty($this->vars)) ? $vars : $this->vars . $vars;
	}

	// Send the mail out to the recipients set previously in var $this->address
	function send($method = NOTIFY_EMAIL)
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

		switch ($method)
		{
			case NOTIFY_EMAIL:
				$this->msg_email();
				break;
			case NOTIFY_IM:
				$this->msg_jabber();
				break;
			case NOTIFY_BOTH:
				$this->msg_email();
				$this->msg_jabber();
				break;
		}

		$this->reset();
	}

	function error($type, $msg)
	{
		global $phpEx, $phpbb_root_path;

		include_once($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		add_log('critical', $type . '_ERROR', $msg);
	}

	//
	// Messenger methods
	//

	function msg_email()
	{
		global $config, $user;

		if (empty($config['email_enable']))
		{
			return false;
		}

		$use_queue = false;
		if ($config['email_package_size'])
		{
			if (empty($this->queue))
			{
				$this->queue = new queue();
			}
			$this->queue->init('email', $config['email_package_size']);
			$use_queue = true;
		}

		$to = $cc = $bcc = '';
		// Build to, cc and bcc strings
		foreach ($this->addresses as $type => $address_ary)
		{
			foreach ($address_ary as $which_ary)
			{
				$$type .= (($$type != '') ? ', ' : '') . (($which_ary['name'] != '') ?  '"' . mail_encode($which_ary['name']) . '" <' . $which_ary['email'] . '>' : $which_ary['email']);
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
		$headers .= "X-Priority: 3\n";
		$headers .= "X-MSMail-Priority: Normal\n";
		$headers .= "X-Mailer: PHP\n";
		$headers .= "X-MimeOLE: Produced By phpBB2\n";
		$headers .= ($this->extra_headers != '') ? $this->extra_headers : '';
		$headers .= "Content-type: text/plain; charset=" . $this->encoding . "\n";
		$headers .= "Content-transfer-encoding: 8bit\n";

		// Send message ... removed $this->encode() from subject for time being
		if (!$use_queue)
		{
			$mail_to = ($to == '') ? 'Undisclosed-Recipients:;' : $to;
			$err_msg = '';
			$result = ($config['smtp_delivery']) ? smtpmail($this->addresses, $this->subject, $this->msg, $err_msg, $headers) : @mail($mail_to, $this->subject, preg_replace("#(?<!\r)\n#s", "\n", $this->msg), $headers);

			if (!$result)
			{
				$message = '<u>EMAIL ERROR</u> [ ' . (($config['smtp_delivery']) ? 'SMTP' : 'PHP') . ' ]<br /><br />' . $err_msg . '<br /><br /><u>CALLING PAGE</u><br /><br />'  . ((!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF']) . '<br />';
				
				$this->error('EMAIL', $message);
				trigger_error($message, E_USER_ERROR);
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

	function msg_jabber()
	{
		global $config, $db, $user, $phpbb_root_path, $phpEx;

		if (empty($config['jab_enable']) || empty($config['jab_host']) || empty($config['jab_username']) || empty($config['jab_password']))
		{
			return false;
		}

		$use_queue = false;
		if ($config['jab_package_size'])
		{
			if (empty($this->queue))
			{
				$this->queue = new queue();
			}
			$this->queue->init('jabber', $config['jab_package_size']);
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
			$this->jabber = new Jabber;

			$this->jabber->server	= $config['jab_host'];
			$this->jabber->username = $config['jab_username'];
			$this->jabber->password = $config['jab_password'];
			$this->jabber->resource = (!empty($config['jab_resource'])) ? htmlentities($config['jab_resource']) : '';

			if (!$this->jabber->Connect())
			{
				$this->error('JABBER', 'Could not connect to Jabber server');
				trigger_error('Could not connect to Jabber server', E_USER_ERROR);
			}

			if (!$this->jabber->SendAuth())
			{
				$this->error('JABBER', 'Could not authorise on Jabber server');
				trigger_error('Could not authorise on Jabber server', E_USER_ERROR);
			}
			$this->jabber->SendPresence(NULL, NULL, 'online');

			foreach ($addresses as $address)
			{
				$this->jabber->SendMessage($address, 'normal', NULL, array('body' => $msg));
			}

			$this->jabber->CruiseControl(2);
			$this->jabber->Disconnect();
		}
		else
		{
			$this->queue->put('jabber', array(
				'addresses'		=> $addresses,
				'subject'		=> htmlentities($subject),
				'msg'			=> htmlentities($this->msg))
			);
		}
		unset($addresses);
		return true;
	}
}

// At the moment it is only handling the email queue
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

	// Thinking about a lock file...
	function process()
	{
		global $db, $config, $phpEx, $phpbb_root_path;

		set_config('last_queue_run', time());

		if (!file_exists($this->cache_file) || file_exists($this->cache_file . '.lock'))
		{
			return;
		}

		$fp = @fopen($this->cache_file . '.lock', 'wb');
		fclose($fp);

		include($this->cache_file);

		foreach ($this->queue_data as $object => $data_ary)
		{
			$package_size = $data_ary['package_size'];

			$num_items = (count($data_ary['data']) < $package_size) ? count($data_ary['data']) : $package_size;

			switch ($object)
			{
				case 'email':
					// Delete the email queued objects if mailing is disabled
					if (!$config['email_enable'])
					{
						unset($this->queue_data['email']);
						break 2;
					}
					@set_time_limit(60);
					break;

				case 'jabber':
					if (!$config['jab_enable'])
					{
						unset($this->queue_data['jabber']);
						break 2;
					}
					@set_time_limit(60);

					include_once($phpbb_root_path . 'includes/functions_jabber.'.$phpEx);
					$this->jabber = new Jabber;

					$this->jabber->server	= $config['jab_host'];
					$this->jabber->username = $config['jab_username'];
					$this->jabber->password = $config['jab_password'];
					$this->jabber->resource = (!empty($config['jab_resource'])) ? htmlentities($config['jab_resource']) : '';

					if (!$this->jabber->Connect())
					{
						messenger::error('JABBER', 'Could not connect to Jabber server');
						trigger_error('Could not connect to Jabber server', E_USER_ERROR);
					}

					if (!$this->jabber->SendAuth())
					{
						messenger::error('JABBER', 'Could not authorise on Jabber server');
						trigger_error('Could not authorise on Jabber server', E_USER_ERROR);
					}
					$this->jabber->SendPresence(NULL, NULL, 'online');
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
						$to = (!$to) ? 'Undisclosed-Recipients:;' : $to;

						$result = ($config['smtp_delivery']) ? smtpmail($addresses, $subject, $msg, $err_msg, $headers) : mail($to, $subject, preg_replace("#(?<!\r)\n#s", "\r\n", $msg), $headers);
				
						if (!$result)
						{
							@unlink($this->cache_file . '.lock');

							// Logging instead of displaying!?
							$message = 'Method: [ ' . (($config['smtp_delivery']) ? 'SMTP' : 'PHP') . ' ]<br /><br />' . $err_msg . '<br /><br /><u>CALLING PAGE</u><br /><br />'  . ((!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF']);
							messenger::error('MAIL', $message);
//							trigger_error($message, E_USER_ERROR);
						}
						break;

					case 'jabber':
						foreach ($addresses as $address)
						{
							$this->jabber->SendMessage($address, 'normal', NULL, array('body' => $msg));
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
					$this->jabber->CruiseControl(2);
					$this->jabber->Disconnect();
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
		if (file_exists($this->cache_file))
		{
			include($this->cache_file);
			
			foreach ($this->queue_data as $object => $data_ary)
			{
				if (count($this->data[$object]))
				{
					$this->data[$object]['data'] = array_merge($data_ary['data'], $this->data[$object]['data']);
				}
			}
		}
		
		$file = '<?php $this->queue_data = ' . $this->format_array($this->data) . '; ?>';

		if ($fp = @fopen($this->cache_file, 'w'))
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

// Replacement or substitute for PHP's mail command
function smtpmail($addresses, $subject, $message, &$err_msg, $headers = '')
{
	global $config;

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
		return FALSE;
	}

	if (trim($message) == '')
	{
		$err_msg = 'Email message was blank';
		return FALSE;
	}

	$mail_rcpt = $mail_to = $mail_cc = array();

	// Build correct addresses for RCPT TO command and the client side display (TO, CC)
	foreach ($addresses['to'] as $which_ary)
	{
		$mail_to[] = ($which_ary['name'] != '') ? mail_encode(trim($which_ary['name'])) . ' <' . trim($which_ary['email']) . '>' : '<' . trim($which_ary['email']) . '>';
		$mail_rcpt['to'][] = '<' . trim($which_ary['email']) . '>';
	}

	foreach ($addresses['bcc'] as $which_ary)
	{
		$mail_rcpt['bcc'][] = '<' . trim($which_ary['email']) . '>';
	}

	foreach ($addresses['cc'] as $which_ary)
	{
		$mail_cc[] = ($which_ary['name'] != '') ? mail_encode(trim($which_ary['name'])) . ' <' . trim($which_ary['email']) . '>' : '<' . trim($which_ary['email']) . '>';
		$mail_rcpt['cc'][] = '<' . trim($which_ary['email']) . '>';
	}

	// Ok we have error checked as much as we can to this point let's get on
	// it already.
	if (!$socket = fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 20))
	{
		$err_msg = "Could not connect to smtp host : $errno : $errstr";
		return FALSE;
	}

	// Wait for reply
	if ($err_msg = server_parse($socket, '220'))
	{
		return FALSE;
	}

	// I see the potential to use pipelining after the EHLO call... 

	// Do we want to use AUTH?, send RFC2554 EHLO, else send RFC821 HELO
	// This improved as provided by SirSir to accomodate
	if (!empty($config['smtp_username']) && !empty($config['smtp_password']))
	{
		// See RFC 821 3.5
		// best would be to do a reverse resolution on the IP and use the result (if any) as
		// domain, or the IP as fallback. Since reverse dns is broken in many php versions (afaik)
		// it seems better to just use the ip.
		fputs($socket, 'EHLO [' . $config['smtp_host'] . "]\r\n");
		if ($err_msg = server_parse($socket, '250'))
		{
			return FALSE;
		}

		// EHLO returns the supported AUTH types
		// NOTE: best way (IMO) is to first choose *MD5 (if it is available), then PLAIN, then LOGIN and if
		// implemented (as a last resort) ANONYMOUS
		switch ($config['smtp_auth_method'])
		{
			case 'LOGIN':
				fputs($socket, "AUTH LOGIN\r\n");
				if ($err_msg = server_parse($socket, '334'))
				{
					return FALSE;
				}

				fputs($socket, base64_encode($config['smtp_username']) . "\r\n");
				if ($err_msg = server_parse($socket, '334'))
				{
					return FALSE;
				}

				fputs($socket, base64_encode($config['smtp_password']) . "\r\n");
				if ($err_msg = server_parse($socket, '235'))
				{
					return FALSE;
				}
				break;
			
			case 'CRAM-MD5':
				break;

			case 'DIGEST-MD5':
				break;

			default:
				// Note: PLAIN should be default (if *MD5 is not available), since LOGIN is not fully compatible with
				// Cyrus-SASL (used by many MTAs for SMTP-AUTH)
				$base64_method_plain = base64_encode($config['smtp_username'] . "\0" . $config['smtp_username'] . "\0" . $config['smtp_password']);
				fputs($socket, "AUTH PLAIN $base64_method_plain\r\n");

				if ($err_msg = server_parse($socket, '235'))
				{
					return FALSE;
				}
				break;
		}
	}
	else
	{
		fputs($socket, 'HELO [' . $config['smtp_host'] . "]\r\n");
		if ($err_msg = server_parse($socket, '250'))
		{
			return FALSE;
		}
	}

	// From this point onward most server response codes should be 250
	// Specify who the mail is from....
	fputs($socket, 'MAIL FROM: <' . $board_config['board_email'] . ">\r\n");
	if ($err_msg = server_parse($socket, '250'))
	{
		return FALSE;
	}

	// Specify each user to send to and build to header.
	$to_header = implode(', ', $mail_to);
	$cc_header = implode(', ', $mail_cc);

	// Now tell the MTA to send the Message to the following people... [TO, BCC, CC]
	foreach ($mail_rcpt as $type => $mail_to_addresses)
	{
		foreach ($mail_to_addresses as $mail_to_address)
		{
			// Add an additional bit of error checking to the To field.
			if (preg_match('#[^ ]+\@[^ ]+#', $mail_to_address))
			{
				fputs($socket, "RCPT TO: $mail_to_address\r\n");
				if ($err_msg = server_parse($socket, '250'))
				{
					return FALSE;
				}
			}
		}
	}

	// Ok now we tell the server we are ready to start sending data
	fputs($socket, "DATA\r\n");

	// This is the last response code we look for until the end of the message.
	if ($err_msg = server_parse($socket, '354'))
	{
		return FALSE;
	}

	// Send the Subject Line...
	fputs($socket, "Subject: $subject\r\n");

	// Now the To Header.
	$to_header = ($to_header == '') ? 'Undisclosed-Recipients:;' : $to_header;
	fputs($socket, "To: $to_header\r\n");

	// Now the CC Header.
	if ($cc_header != '')
	{
		fputs($socket, "CC: $cc_header\r\n");
	}

	// Now any custom headers....
	fputs($socket, "$headers\r\n\r\n");

	// Ok now we are ready for the message...
	fputs($socket, "$message\r\n");

	// Ok the all the ingredients are mixed in let's cook this puppy...
	fputs($socket, ".\r\n");
	if ($err_msg = server_parse($socket, '250'))
	{
		return FALSE;
	}

	// Now tell the server we are done and close the socket...
	fputs($socket, "QUIT\r\n");
	fclose($socket);

	return TRUE;
}

function server_parse($socket, $response)
{
	while (substr($server_response, 3, 1) != ' ')
	{
		if (!($server_response = fgets($socket, 256)))
		{
			return 'Could not get mail server response codes';
		}
	}

	if (!(substr($server_response, 0, 3) == $response))
	{
		return "Ran into problems sending Mail. Response: $server_response";
	}

	return 0;
}

// Encodes the given string for proper display for this encoding ... nabbed 
// from php.net and modified. There is an alternative encoding method which 
// may produce less output but it's questionable as to its worth in this 
// scenario IMO
function mail_encode($str)
{
	if ($this->encoding == '')
	{
		return $str;
	}

	// define start delimimter, end delimiter and spacer
	$end = "?=";
	$start = "=?$this->encoding?B?";
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

function md5_digest()
{
}

?>