<?php
/***************************************************************************
 *                              smtp.php
 *                       -------------------
 *   begin                : Wed May 09 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

define('SMTP_INCLUDED', 1);
//
// This function has been modified as provided
// by SirSir to allow multiline responses when 
// using SMTP Extensions
//
function server_parse($socket, $response) 
{ 
   while ( substr($server_response,3,1) != ' ' ) 
   { 
      if( !( $server_response = fgets($socket, 256) ) ) 
      { 
         message_die(GENERAL_ERROR, "Couldn't get mail server response codes", "", __LINE__, __FILE__); 
      } 
   } 

   if( !( substr($server_response, 0, 3) == $response ) ) 
   { 
      message_die(GENERAL_ERROR, "Ran into problems sending Mail. Response: $server_response", "", __LINE__, __FILE__); 
   } 
} 

/****************************************************************************
*	Function: 		smtpmail
*	Description: 	This is a functional replacement for php's builtin mail
*						function, that uses smtp.
*	Usage:			The usage for this function is identical to that of php's
*						built in mail function.
****************************************************************************/
function smtpmail($mail_to, $subject, $message, $headers = "")
{
	// For now I'm using an array based $smtp_vars to hold the smtp server
	// info, but it should probably change to $board_config...
	// then the relevant info would be $board_config['smtp_host'] and
	// $board_config['smtp_port'].
	global $board_config;

	//
	// Fix any bare linefeeds in the message to make it RFC821 Compliant.
	//
	$message = preg_replace("/(?<!\r)\n/si", "\r\n", $message);

	if ($headers != "")
	{
		if(is_array($headers))
		{
			if(sizeof($headers) > 1)
			{
				$headers = join("\r\n", $headers);
			}
			else
			{
				$headers = $headers[0];
			}
		}
		$headers = chop($headers);

		//
		// Make sure there are no bare linefeeds in the headers
		//
		$headers = preg_replace("/(?<!\r)\n/si", "\r\n", $headers);
		//
		// Ok this is rather confusing all things considered,
		// but we have to grab bcc and cc headers and treat them differently
		// Something we really didn't take into consideration originally
		//
		$header_array = explode("\r\n", $headers);
		@reset($header_array);
		$headers = "";
		while( list(, $header) = each($header_array) )
		{
			if( preg_match("/^cc:/si", $header) )
			{
				$cc = preg_replace("/^cc:(.*)/si", "\\1", $header);
			}
			else if( preg_match("/^bcc:/si", $header ))
			{
				$bcc = preg_replace("/^bcc:(.*)/si", "\\1", $header);
				$header = "";
			}
			$headers .= $header . "\r\n";
		}
		$headers = chop($headers);
		$cc = explode(",", $cc);
		$bcc = explode(",", $bcc);
	}
	if(trim($mail_to) == "")
	{
		message_die(GENERAL_ERROR, "No email address specified", "", __LINE__, __FILE__);
	}
	if(trim($subject) == "")
	{
		message_die(GENERAL_ERROR, "No email Subject specified", "", __LINE__, __FILE__);
	}
	if(trim($message) == "")
	{
		message_die(GENERAL_ERROR, "Email message was blank", "", __LINE__, __FILE__);
	}
	$mail_to_array = explode(",", $mail_to);

	//
	// Ok we have error checked as much as we can to this point let's get on
	// it already.
	//
	if( !$socket = fsockopen($board_config['smtp_host'], 25, $errno, $errstr, 20) )
	{
		message_die(GENERAL_ERROR, "Could not connect to smtp host : $errno : $errstr", "", __LINE__, __FILE__);
	}
	server_parse($socket, "220");

	if( !empty($board_config['smtp_username']) && !empty($board_config['smtp_password']) )
	{ 
		// Send the RFC2554 specified EHLO. 
		// This improved as provided by SirSir to accomodate
		// both SMTP AND ESMTP capable servers
		fputs($socket, "EHLO " . $board_config['smtp_host'] . "\r\n"); 
		server_parse($socket, "250"); 

		fputs($socket, "AUTH LOGIN\r\n"); 
		server_parse($socket, "334"); 
		fputs($socket, base64_encode($board_config['smtp_username']) . "\r\n"); 
		server_parse($socket, "334"); 
		fputs($socket, base64_encode($board_config['smtp_password']) . "\r\n"); 
		server_parse($socket, "235"); 
	} 
	else 
	{ 
		// Send the RFC821 specified HELO. 
		fputs($socket, "HELO " . $board_config['smtp_host'] . "\r\n"); 
		server_parse($socket, "250"); 
	}

	// From this point onward most server response codes should be 250
	// Specify who the mail is from....
	fputs($socket, "MAIL FROM: <" . $board_config['board_email'] . ">\r\n");
	server_parse($socket, "250");

	// Specify each user to send to and build to header.
	$to_header = "To: ";
	@reset( $mail_to_array );
	while( list( , $mail_to_address ) = each( $mail_to_array ))
	{
		//
		// Add an additional bit of error checking to the To field.
		//
		$mail_to_address = trim($mail_to_address);
		if ( preg_match('/[^ ]+\@[^ ]+/', $mail_to_address) )
		{
			fputs( $socket, "RCPT TO: <$mail_to_address>\r\n" );
			server_parse( $socket, "250" );
		}
		$to_header .= ( ( $mail_to_address != '' ) ? ', ' : '' ) . "<$mail_to_address>";
	}
	// Ok now do the CC and BCC fields...
	@reset( $bcc );
	while( list( , $bcc_address ) = each( $bcc ))
	{
		//
		// Add an additional bit of error checking to bcc header...
		//
		$bcc_address = trim( $bcc_address );
		if ( preg_match('/[^ ]+\@[^ ]+/', $bcc_address) )
		{
			fputs( $socket, "RCPT TO: <$bcc_address>\r\n" );
			server_parse( $socket, "250" );
		}
	}
	@reset( $cc );
	while( list( , $cc_address ) = each( $cc ))
	{
		//
		// Add an additional bit of error checking to cc header
		//
		$cc_address = trim( $cc_address );
		if ( preg_match('/[^ ]+\@[^ ]+/', $cc_address) )
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
