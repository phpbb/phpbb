<?php
/***************************************************************************  
 *                                 
 *                            -------------------                         
 *   begin                : Thursday, Jul 12, 2001 
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

if($setmodules==1)
{
	$file = basename(__FILE__);
	$module['board']['config'] = $file;
	//$module['users']['edit'] = $file.'?mode=edit';
	//$module['users']['delete'] = $file.'?mode=delete';
	return;
}

print "Got past the \$setmodules check<br>\n";
print "Requested action was: $mode<br>\n";



?>
