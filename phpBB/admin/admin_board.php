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
        $module['board']['config']=__FILE__;
        //$module['users']['edit']=__FILE__.'?mode=edit';
        //$module['users']['delete']=__FILE__.'?mode=delete';
        return;
}

print "Got past the \$setmodules check<br>\n";
print "Requested action was: $mode<br>\n";



?>
