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

$phpbb_root_path = "./../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
init_userprefs($userdata);
// 
// End sessionmanagement
//

if ($pane == 'top')
{
	$page_title = $lang['View_topic'] ." - $topic_title";
	$pagetype = "viewtopic";

	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

}
elseif ($pane == 'left')
{
	print "<BASE TARGET=\"main\">";
	$dir = opendir(".");

	$setmodules = 1;
	while($file = readdir($dir))
	{
		if(preg_match("/^admin_.*/", $file))
		{
			//print "$file<br>\n";
			include($file);
		}
	}

	while( list($cat, $action_array) = each($module) )
	{
		print "<H3>$cat</H3>\n";
		print "<ul>\n";
		
		while( list($action, $file) = each($action_array) )
		{
			print "<li><a href=\"$file\">$action</a></li>\n";
		}
		
		print "</ul>\n";
	}
	//var_dump($module);

	$setmodules = 0;
}
elseif ($pane == 'right')
{

	echo "This the right pane ;)";

}
else
{ 
	
// Generate frameset

?>
<html>
<head>
<title>Admin</title>
</head>

<frameset rows="150,*" border="0" frameborder="0">
	<frame src="index.<?php echo $phpEx?>?pane=top" name="top" SCROLLING="NO">
	<frameset cols="150,*" border="0" frameborder="0">
		<frame src="index.<?php echo $phpEx?>?pane=left" name="nav">
		<frame src="index.<?php echo $phpEx?>?pane=right" name="main">
	</frameset>
</frameset>
<noframes>
	<body bgcolor="#FFFFFF">
	Sorry, your browser doesn't seem to support Frames..
</body>
</noframes>
</html>
<?

}

?>