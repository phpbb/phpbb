<?php
/***************************************************************************  
 *                              page_tail.php
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

// Load/parse the footer template we need based on pagetype.
switch($pagetype) 
{
 case 'index':
   $template->pparse("output", "footer");
   break;
}

// Show the overall footer.
if($user_logged_in) 
{
   $admin_link = "<a href=\"admin/index.php\">Administration Panel</a>";
}
$template->set_var(array("PHPBB_VERSION" => "2.0-alpha",
			 "ADMIN_LINK" => $admin_link));
$template->pparse("output", "overall_footer");

// Close our DB connection.
$db->sql_close();

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);

printf("<center><font size=-2>phpBB Created this page in %f seconds.</font></center>", $totaltime);

?>
