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

//
// NOTE NOTE NOTE  NOTE NOTE NOTE  NOTE NOTE NOTE 
//
// Bug fixes in 2.0.x should be ported to this, particularly the header changes
//
// NOTE NOTE NOTE  NOTE NOTE NOTE  NOTE NOTE NOTE 
//

//
// The emailer class has support for attaching files, that isn't implemented
// in the 2.0 release but we can probable find some way of using it in a future
// release
//
class emailer
{
	var $tpl_file;
	var $use_smtp;
	var $msg;
	var $mimeOut;
	var $arrPlaceHolders = array();	// an associative array that has the key = placeHolderName and val = placeHolderValue.
	var $subject, $extra_headers, $address;

	function emailer($use_smtp)
	{
		$this->use_smtp = $use_smtp;
		$this->tpl_file = NULL;
		$this->address = NULL;
 		$this->msg = '';
		$this->mimeOut = '';
	}

	// Resets all the data (address, template file, etc etc to default
	function reset()
	{
		$this->tpl_file = '';
		$this->address = '';
		$this->msg = '';
		$this->memOut = '';
		$this->vars = '';
	}

	// Sets an email address to send to
	function email_address($address)
	{
		$this->address = '';
		$this->address .= $address;
	}

	// set up subject for mail
	function set_subject($subject = '')
	{
		$this->subject = $subject;
	}

	// set up extra mail headers
	function extra_headers($headers)
	{
		$this->extra_headers = $headers;
	}

	function use_template($template_file, $template_lang = '')
	{
		global $config, $phpbb_root_path;

		if ($template_lang == '')
		{
			$template_lang = $config['default_lang'];
		}

		$this->tpl_file = $phpbb_root_path . 'language/' . $template_lang . '/email/' . $template_file . '.txt';
		if (!file_exists($this->tpl_file))
		{
			trigger_error('Could not find email template file ' . $template_file);
		}

		if (!$this->load_msg())
		{
			trigger_error('Could not load email template file ' . $template_file);
		}

		return true;
	}

	// Open the template file and read in the message
	function load_msg()
	{
		if ($this->tpl_file == NULL)
		{
			trigger_error('No template file set');
		}

		if (!($fd = fopen($this->tpl_file, 'r')))
		{
			trigger_error('Failed opening template file');
		}

		$this->msg .= fread($fd, filesize($this->tpl_file));
		fclose($fd);

		return true;
	}

	function assign_vars($vars)
	{
		$this->vars = (empty($this->vars)) ? $vars : $this->vars . $vars;
	}

	function parse_email()
	{
		foreach ($this->vars as $key => $val)
		{
			$$key = $val;
		}

    	// Escape all quotes, else the eval will fail.
		$this->msg = str_replace ("'", "\'", $this->msg);
		$this->msg = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", $this->msg);

		eval("\$this->msg = '$this->msg';");

		// We now try and pull a subject from the email body ... if it exists,
		// do this here because the subject may contain a variable
		$drop_header = '';
		$match = array();
		if (preg_match('#^(Subject:(.*?))$#m', $this->msg, $match))
		{
			$this->subject = (trim($match[2]) != '') ? trim($match[2]) : (($this->subject != '') ? $this->subject : 'No Subject');
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->subject = (($this->subject != '') ? $this->subject : 'No Subject');
		}

		if (preg_match('#^(Charset:(.*?))$#m', $this->msg, $match))
		{
			$this->encoding = (trim($match[2]) != '') ? trim($match[2]) : trim($lang['ENCODING']);
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->encoding = trim($lang['ENCODING']);
		}

		if ($drop_header != '')
		{
			$this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
		}

		// Split up message into 76 chars as per RFC2045
		// $this->msg = chunk_split($this->msg);

		return true;
	}

	// Send the mail out to the recipients set previously in var $this->address
	function send()
	{
		global $phpEx, $phpbb_root_path;

		if ($this->address == NULL)
		{
			trigger_error('No email address set');
		}

		if (!$this->parse_email())
		{
			return false;
		}

		$universal_extra = "MIME-Version: 1.0\nContent-type: text/plain; charset=" . $this->encoding . "\nContent-transfer-encoding: 8bit\nDate: " . gmdate('D, d M Y H:i:s', time()) . " UT\nX-Priority: 3\nX-MSMail-Priority: Normal\nX-Mailer: PHP\n";
		$this->extra_headers = $universal_extra . trim($this->extra_headers); 

		$result = ($this->use_smtp) ? smtpmail($this->address, $this->subject, $this->msg, $this->extra_headers) : @mail($this->address, $this->subject, $this->msg, $this->extra_headers);

		if (!$result)
		{
			trigger_error('Failed sending email :: ' . $result);
		}
		
		return true;
	}
} // class emailer

// This function has been modified as provided by SirSir to allow multiline responses when
// using SMTP Extensions
function server_parse($socket, $response)
{
   while (substr($server_response,3,1) != ' ')
   {
      if (!($server_response = fgets($socket, 256)))
      {
         trigger_error('Could not get mail server response codes');
      }
   }

   if (!(substr($server_response, 0, 3) == $response))
   {
      trigger_error("Ran into problems sending Mail. Response: $server_response");
   }
}

// Replacement or substitute for PHP's mail command
function smtpmail($mail_to, $subject, $message, $headers = '')
{
	global $config;

	// Fix any bare linefeeds in the message to make it RFC821 Compliant.
	$message = preg_replace("/(?<!\r)\n/si", "\r\n", $message);

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
		$headers = preg_replace("#(?<!\r)\n#si", "\r\n", $headers);

		// Ok this is rather confusing all things considered,
		// but we have to grab bcc and cc headers and treat them differently
		// Something we really didn't take into consideration originally
		$header_array = explode("\r\n", $headers);
		@reset($header_array);

		$headers = "";
		while(list(, $header) = each($header_array))
		{
			if (preg_match("#^cc:#si", $header))
			{
				$cc = preg_replace("#^cc:(.*)#si", "\\1", $header);
			}
			else if (preg_match("#^bcc:/si", $header))
			{
				$bcc = preg_replace("#^bcc:(.*)#si", "\\1", $header);
				$header = "";
			}
			$headers .= $header . "\r\n";
		}

		$headers = chop($headers);
		$cc = explode(',', $cc);
		$bcc = explode(',', $bcc);
	}

	if (trim($mail_to) == '')
	{
		trigger_error('No email address specified');
	}

	if (trim($subject) == '')
	{
		trigger_error('No email Subject specified');
	}

	if (trim($message) == '')
	{
		trigger_error('Email message was blank');
	}

	$mail_to_array = explode(',', $mail_to);

	// Ok we have error checked as much as we can to this point let's get on
	// it already.
	if (!$socket = fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 20))
	{
		trigger_error("Could not connect to smtp host : $errno : $errstr");
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
	$to_header = "To: ";
	@reset($mail_to_array);
	while(list(, $mail_to_address) = each($mail_to_array))
	{
		// Add an additional bit of error checking to the To field.
		$mail_to_address = trim($mail_to_address);
		if (preg_match('#[^ ]+\@[^ ]+#', $mail_to_address))
		{
			fputs($socket, "RCPT TO: <$mail_to_address>\r\n");
			server_parse($socket, "250");
		}
		$to_header .= "<$mail_to_address>, ";
	}

	// Ok now do the CC and BCC fields...
	@reset($bcc);
	while(list(, $bcc_address) = each($bcc))
	{
		// Add an additional bit of error checking to bcc header...
		$bcc_address = trim($bcc_address);
		if (preg_match('#[^ ]+\@[^ ]+#', $bcc_address))
		{
			fputs($socket, "RCPT TO: <$bcc_address>\r\n");
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
			fputs($socket, "RCPT TO: <$cc_address>\r\n");
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
	fputs($socket, "$to_header\r\n");

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

?>