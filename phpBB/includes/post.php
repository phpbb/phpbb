<?php
/***************************************************************************  
 *
 *                            -------------------                         
 *   begin                : Saturday, Feb 13, 2001 
 *   copyright            : (C) 2001 The phpBB Group        
 *   email                : support@phpbb.com                           
 *                                                          
 *   $Id$
 *                                                            
 * 
 ***************************************************************************/ 


/***************************************************************************  
 *                                                     
 *   This program is free software; you can redistribute it and/or modify    
 *   it under the terms of the GNU General Public License as published by   
 *   the Free Software Foundation; either version 2 of the License, or  
 *   (at your option) any later version.                      
 *                                                          
 * 
 ***************************************************************************/ 

//
// This function will prepare a posted message for 
// entry into the database.
//
function prepare_message($message, $html_on, $bbcode_on, $smile_on, $bbcode_uid = 0)
{
	$message = trim($message);

	if(!$html_on)
	{
		$message = htmlspecialchars($message);
	}

	if($bbcode_on)
	{
		$message = bbencode_first_pass($message, $bbcode_uid);
	}

	if($smile_on)
	{
		// No smile() function yet, write one...
		//$message = smile($message);
	}

	$message = addslashes($message);

	return($message);
}

?>
