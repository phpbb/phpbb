<?php
/***************************************************************************
                                emailer.php
                             -------------------
    begin                : Sunday Aug. 12, 2001
    copyright            : (C) 2001 The phpBB Group
    email                : support@phpbb.com

    $Id$

***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

class emailer
{
	var $msg, $subject, $extra_headers;
	var $to_addres, $cc_address, $bcc_address;
	var $reply_to, $from;
	var $use_queue, $queue;

	var $tpl_msg = array();

	function emailer($use_queue = false)
	{
		global $config;

		$this->use_queue = $use_queue;
		if ($use_queue)
		{
			$this->queue = new Queue();
			$this->queue->init('emailer', $config['email_package_size']);
		}
		$this->reset();
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
		$this->addresses['to'][$pos]['name'] = trim($realname);
	}

	function cc($address, $realname = '')
	{
		$pos = sizeof($this->addresses['cc']);
		$this->addresses['cc'][$pos]['email'] = trim($address);
		$this->addresses['cc'][$pos]['name'] = trim($realname);
	}

	function bcc($address, $realname = '')
	{
		$pos = sizeof($this->addresses['bcc']);
		$this->addresses['bcc'][$pos]['email'] = trim($address);
		$this->addresses['bcc'][$pos]['name'] = trim($realname);
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
		$this->extra_headers .= trim($headers) . "\r\n";
	}

	function template($template_file, $template_lang = '')
	{
		global $config, $phpbb_root_path;

		if (trim($template_file) == '')
		{
			trigger_error('No template file set', E_USER_ERROR);
		}

		if (trim($template_lang) == '')
		{
			$template_lang = $config['default_lang'];
		}

		if (empty($this->tpl_msg[$template_lang . $template_file]))
		{
			$tpl_file = $phpbb_root_path . 'language/' . $template_lang . '/email/' . $template_file . '.txt';

			if (!file_exists($tpl_file))
			{
				$tpl_file = $phpbb_root_path . 'language/' . $config['default_lang'] . '/email/' . $template_file . '.txt';

				if (!file_exists($tpl_file))
				{
					trigger_error('Could not find email template file :: ' . $template_file, E_USER_ERROR);
				}
			}

			if (!($fd = @fopen($tpl_file, 'r')))
			{
				trigger_error('Failed opening template file', E_USER_ERROR);
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
	function send()
	{
		global $config, $user, $phpEx, $phpbb_root_path;

		if (empty($config['email_enable']))
		{
			return false;
		}

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

		if ($drop_header != '')
		{
			$this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
		}

		$to = $cc = $bcc = '';
		// Build to, cc and bcc strings
		foreach ($this->addresses as $type => $address_ary)
		{
			foreach ($address_ary as $which_ary)
			{
				$$type .= (($$type != '') ? ',' : '') . (($which_ary['name'] != '') ? '"' . $this->encode($which_ary['name']) . '" <' . $which_ary['email'] . '>' : '<' . $which_ary['email'] . '>');
			}
		}

		// Build header
		$this->extra_headers = (($this->replyto !='') ? "Reply-to: <$this->replyto>\r\n" : '') . (($this->from != '') ? "From: <$this->from>\r\n" : "From: <" . $config['board_email'] . ">\r\n") . "Return-Path: <" . $config['board_email'] . ">\r\nMessage-ID: <" . md5(uniqid(time())) . "@" . $config['server_name'] . ">\r\nMIME-Version: 1.0\r\nContent-type: text/plain; charset=" . $this->encoding . "\r\nContent-transfer-encoding: 8bit\r\nDate: " . gmdate('D, d M Y H:i:s Z', time()) . "\r\nX-Priority: 3\r\nX-MSMail-Priority: Normal\r\nX-Mailer: PHP\r\nX-MimeOLE: Produced By phpBB2\r\n" . $this->extra_headers . (($cc != '') ? "Cc:$cc\r\n" : '')  . (($bcc != '') ? "Bcc:$bcc\r\n" : ''); 

		// Send message ... removed $this->encode() from subject for time being
		if (!$this->use_queue)
		{
			$result = ($config['smtp_delivery']) ? smtpmail($to, $this->subject, $this->msg, $this->extra_headers) : mail($to, $this->subject, preg_replace("#(?<!\r)\n#s", "\r\n", $this->msg), $this->extra_headers);
		}
		else
		{
			$this->queue->put('emailer', array(
				'smtp_delivery' => $config['smtp_delivery'],
				'to'			=> $to,
				'subject'		=> $this->subject,
				'msg'			=> $this->msg,
				'extra_headers' => $this->extra_headers)
			);

			$result = true;
		}

		// Did it work?
		if (!$result)
		{
			$message = '<u>EMAIL ERROR</u> [ ' . (($config['smtp_delivery']) ? 'SMTP' : 'PHP') . ' ]<br /><br />' . $result . '<br /><br /><u>CALLING PAGE</u><br /><br />'  . ((!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF']) . '<br />';
			trigger_error($message, E_USER_ERROR);
		}

		return true;
	}

	// Encodes the given string for proper display for this encoding ... nabbed 
	// from php.net and modified. There is an alternative encoding method which 
	// may produce less output but it's questionable as to its worth in this 
	// scenario IMO
	function encode($str)
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

} // class emailer

// This function has been modified as provided by SirSir to allow multiline responses when
// using SMTP Extensions
function server_parse($socket, $response)
{
   while (substr($server_response, 3, 1) != ' ')
   {
      if (!($server_response = fgets($socket, 256)))
      {
         trigger_error('Could not get mail server response codes', E_USER_ERROR);
      }
   }

   if (!(substr($server_response, 0, 3) == $response))
   {
      trigger_error("Ran into problems sending Mail. Response: $server_response", E_USER_ERROR);
   }
}

// Replacement or substitute for PHP's mail command
function smtpmail($mail_to, $subject, $message, $headers = '')
{
	global $config;

	// Fix any bare linefeeds in the message to make it RFC821 Compliant.
	$message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);

	if ($headers != '')
	{
		if (is_array($headers))
		{
			if (sizeof($headers) > 1)
			{
				$headers = join("\r\n", $headers);
			}
			else
			{
				$headers = $headers[0];
			}
		}
		$headers = chop($headers);

		// Make sure there are no bare linefeeds in the headers
		$headers = preg_replace('#(?<!\r)\n#si', "\r\n", $headers);

		// Ok this is rather confusing all things considered,
		// but we have to grab bcc and cc headers and treat them differently
		// Something we really didn't take into consideration originally
		$header_array = explode("\r\n", $headers);
		@reset($header_array);

		$headers = '';
		while(list(, $header) = each($header_array))
		{
			if (preg_match('#^cc:#si', $header))
			{
				$cc = preg_replace('#^cc:(.*)#si', '\1', $header);
			}
			else if (preg_match('#^bcc:#si', $header))
			{
				$bcc = preg_replace('#^bcc:(.*)#si', '\1', $header);
				$header = '';
			}
			$headers .= $header . "\r\n";
		}

		$headers = chop($headers);
		$cc = explode(',', $cc);
		$bcc = explode(',', $bcc);
	}

	if (trim($subject) == '')
	{
		trigger_error('No email Subject specified', E_USER_ERROR);
	}

	if (trim($message) == '')
	{
		trigger_error('Email message was blank', E_USER_ERROR);
	}

	$mail_to_array = explode(',', $mail_to);

	// Ok we have error checked as much as we can to this point let's get on
	// it already.
	if (!$socket = fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 20))
	{
		trigger_error("Could not connect to smtp host : $errno : $errstr", E_USER_ERROR);
	}

	// Wait for reply
	server_parse($socket, "220");

	// Do we want to use AUTH?, send RFC2554 EHLO, else send RFC821 HELO
	// This improved as provided by SirSir to accomodate
	if (!empty($config['smtp_username']) && !empty($config['smtp_password']))
	{
		fputs($socket, "EHLO " . $config['smtp_host'] . "\r\n");
		server_parse($socket, "250");

		fputs($socket, "AUTH LOGIN\r\n");
		server_parse($socket, "334");

		fputs($socket, base64_encode($config['smtp_username']) . "\r\n");
		server_parse($socket, "334");

		fputs($socket, base64_encode($config['smtp_password']) . "\r\n");
		server_parse($socket, "235");
	}
	else
	{
		fputs($socket, "HELO " . $config['smtp_host'] . "\r\n");
		server_parse($socket, "250");
	}

	// From this point onward most server response codes should be 250
	// Specify who the mail is from....
	fputs($socket, "MAIL FROM: <" . $config['board_email'] . ">\r\n");
	server_parse($socket, "250");

	// Specify each user to send to and build to header.
	@reset($mail_to_array);
	while(list(, $mail_to_address) = each($mail_to_array))
	{
		// Add an additional bit of error checking to the To field.
		$mail_to_address = trim($mail_to_address);
		if (preg_match('#[^ ]+\@[^ ]+#', $mail_to_address))
		{
			fputs($socket, "RCPT TO: $mail_to_address\r\n");
			server_parse($socket, "250");
		}
		$to_header .= (($to_header !='') ? ', ' : '') . "$mail_to_address";
	}
	// Ok now do the CC and BCC fields...
	@reset($bcc);
	while(list(, $bcc_address) = each($bcc))
	{
		// Add an additional bit of error checking to bcc header...
		$bcc_address = trim($bcc_address);
		if (preg_match('#[^ ]+\@[^ ]+#', $bcc_address))
		{
			fputs($socket, "RCPT TO: $bcc_address\r\n");
			server_parse($socket, "250");
		}
	}

	@reset($cc);
	while(list(, $cc_address) = each($cc))
	{
		// Add an additional bit of error checking to cc header
		$cc_address = trim($cc_address);
		if (preg_match('#[^ ]+\@[^ ]+#', $cc_address))
		{
			fputs($socket, "RCPT TO: $cc_address\r\n");
			server_parse($socket, "250");
		}
	}

	// Ok now we tell the server we are ready to start sending data
	fputs($socket, "DATA\r\n");

	// This is the last response code we look for until the end of the message.
	server_parse($socket, "354");

	// Send the Subject Line...
	fputs($socket, "Subject: $subject\r\n");

	// Now the To Header.
	$to_header = ($to_header == '') ? "<Undisclosed-recipients:;>" : $to_header;
	fputs($socket, "To: $to_header\r\n");

	// Now any custom headers....
	fputs($socket, "$headers\r\n\r\n");

	// Ok now we are ready for the message...
	fputs($socket, "$message\r\n");

	// Ok the all the ingredients are mixed in let's cook this puppy...
	fputs($socket, ".\r\n");
	server_parse($socket, "250");

	// Now tell the server we are done and close the socket...
	fputs($socket, "QUIT\r\n");
	fclose($socket);

	return TRUE;
}

// This class is for handling queues - to be placed into another file ?
// At the moment it is only handling the email queue
class Queue
{
	var $data = array();
	var $queue_data = array();
	var $package_size = 0;
	var $cache_file = '';

	function Queue()
	{
		global $phpEx, $phpbb_root_path;

		$this->data = array();
		$this->cache_file = $phpbb_root_path . 'cache/queue.' . $phpEx;
	}

	//--TEMP
	function is_queue_filled()
	{
		if (file_exists($this->cache_file))
		{
			return true;
		}

		return false;
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

	//--TEMP
	function show()
	{
		echo ";<pre>";
		print_r($this->data);
		echo "</pre>;";
	}

	// Thinking about a lock file...
	function process()
	{
		global $_SERVER, $_ENV, $db;

		if (file_exists($this->cache_file))
		{
			include($this->cache_file);
			$fp = @fopen($this->cache_file, 'r');
			@flock($fp, LOCK_EX);
		}
		else
		{
			return;
		}
			
		foreach ($this->queue_data as $object => $data_array)
		{
			$package_size = $data_array['package_size'];

			$num_items = (count($data_array['data']) < $package_size) ? count($data_array['data']) : $package_size;

			if ($object == 'emailer')
			{
				@set_time_limit(60);
			}

			for ($i = 0; $i < $num_items; $i++)
			{
				foreach ($data_array['data'][0] as $var => $value)
				{
					$$var = $value;
				}
				
				if ($object == 'emailer')
				{
					$result = ($smtp_delivery) ? smtpmail($to, $subject, $msg, $extra_headers) : mail($to, $subject, preg_replace("#(?<!\r)\n#s", "\r\n", $msg), $extra_headers);
				
					if (!$result)
					{
						$message = '<u>EMAIL ERROR</u> [ ' . (($smtp_delivery) ? 'SMTP' : 'PHP') . ' ]<br /><br />' . $result . '<br /><br /><u>CALLING PAGE</u><br /><br />'  . ((!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF']) . '<br />';
						trigger_error($message, E_USER_ERROR);
					}
				}
				array_shift($this->queue_data[$object]['data']);
			}

			if (count($this->queue_data[$object]['data']) == 0)
			{
				unset($this->queue_data[$object]);
			}
		}
	
		if (count($this->queue_data) == 0)
		{
			@flock($fp, LOCK_UN);
			fclose($fp);
			unlink($this->cache_file);
		}
		else
		{
			$file = '<?php $this->queue_data=' . $this->format_array($this->queue_data) . '; ?>';
			@flock($fp, LOCK_UN);
			fclose($fp);

			if ($fp = @fopen($this->cache_file, 'wb'))
			{
				@flock($fp, LOCK_EX);
				fwrite($fp, $file);
				@flock($fp, LOCK_UN);
				fclose($fp);
			}
		}

		$sql = "UPDATE " . CONFIG_TABLE . "
			SET config_value = '" . time() . "'
			WHERE config_name = 'last_queue_run'";
		$db->sql_query($sql);
	}

	function save()
	{
		if (file_exists($this->cache_file))
		{
			include($this->cache_file);
			
			foreach ($this->queue_data as $object => $data_array)
			{
				if (count($this->data[$object]))
				{
					$this->data[$object]['data'] = array_merge($data_array['data'], $this->data[$object]['data']);
				}
			}
		}
		
		$file = '<?php $this->queue_data = ' . $this->format_array($this->data) . '; ?>';

		if ($fp = @fopen($this->cache_file, 'wt'))
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

?>