<?php
/***************************************************************************
                          smtp.php  -  description
                             -------------------
    begin                : Wed May 09 2001
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

/****************************************************************************
*	This script should be included if the admin has configured the board for
*	smtp mail instead of standard sendmail.  It includes a function smtpmail
* 	which is identical to the standard built in mail function in usage.
****************************************************************************/
include("../common.php");

/****************************************************************************
*	Function: 		server_parse
*	Description:	This funtion processes the smtp server's response codes
*	Usage: 			This function is only used interanally by the smtpmail
*						function.  It takes two arguments the first a socket pointer
*						to the opened socket to the server and the second the 
*						response code you are looking for.
****************************************************************************/
function server_parse($socket, $response)
{
	if(!($server_response = fgets($socket, 100)))
	{
		error_die(GENERAL_ERROR, "Couldn't get mail server response codes");
	}
	if(!(substr($server_response, 0, 3) == $response))
	{
		error_die(GENERAL_ERROR, "Ran into problems sending Mail");
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
	global $smtp_vars;
	var $errno;
	var $errstr;
	
	// Fix any bare linefeeds in the message to make it RFC821 Compliant.
	$message = ereg_replace("[^\r]\n", "\r\n", $message);
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

		// Make sure there are no bare linefeeds in the headers 
		$headers = ereg_replace("[^\r]\n", "\r\n", $headers);
	}
	if(trim($mail_to) == "")
	{
		error_die(GENERAL_ERROR, "No email address specified");
		exit;
	}
	if(trim($subject) == "")
	{
		error_die(GENERAL_ERROR, "No email Subject specified");
	}
	if(trim($message) == "")
	{
		error_die(GENERAL_ERROR, "Email message was blank!");
	}
	$mail_to_array = explode(",", $mail_to);
	// Ok we have error checked as much as we can to this point let's get on
	// it already.
	$socket = fsockopen($smtp_vars['host'], $smtp_vars['port'], &$errno, @$errstr, 30) or 
		error_die(GENERAL_ERROR, "Could not connect to smtp host!");
	server_parse($socket, "220");
	
	// Send the RFC821 specified HELO.
	fputs($socket, "HELO " . $stmp_vars['host'] . "\r\n");

	// From this point onward most server response codes should be 250
	server_parse($socket, "250");
	
	// Specify who the mail is from....
	fputs($socket, "MAIL FROM: $email_from\r\n");
	server_parse($socket, "250");

	// Specify each user to send to and build to header.
	$to_header = "To: ";
	foreach($mail_to_array as $mail_to_address)
	{
		fputs($socket, "RCPT TO: $mail_to_address\r\n";
		server_parse($socket, "250");
		$to_header .= "<$mail_to_address>, ";
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
	fputs($socket, "quit\r\n");
	fclose($socket);
	return(1);
}






?>
