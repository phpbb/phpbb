<?php
/** 
*
* @package phpBB2
* Author:  CRLin - http://web.dhjh.tcc.edu.tw/~gzqbyr/
*
*/

$phpbb_root_path = (defined('PORTAL_ROOT_PATH')) ? PORTAL_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
define('IN_PHPBB', true);
define('Inst_dir', $phpbb_root_path);
define('Frame_hide', true); // you can set false
define('Top_frame_height', 31); // Maybe you'll have to edit this line to fit your

$my_style = empty($_GET['style']) ? '' : 'style=' . $_GET['style'];
$my_php_self = explode("/", $_SERVER["PHP_SELF"]);
$my_php_self = $my_php_self[count($my_php_self)-1];

if(isset($_GET['del_cookie']))
{
	@define('IN_PHPBB', true);
	define('IN_PORTAL', true);	
	$phpbb_root_path = "../";
	$module_root_path = "./";

	$phpEx = substr(strrchr(__FILE__, '.'), 1); 
	include_once($phpbb_root_path . 'common.'.$phpEx);
	
	//
	// Page selector
	//
	$page_id = request_var('page', 1);	

	//
	// Set page ID for session management
	//
	$userdata = session_pagestart($user_ip, $page_id);
	init_userprefs($userdata);
	//
	// End session management
	//

	$title = 'Styles Demo';
	$block_size = ( isset($block_size) && !empty($block_size) ? $block_size : '315' );
	$is_block = FALSE;
}

if (empty($_GET['pane']))
{
	if (!empty($my_style))
		$my_style = '&' . $my_style;
	
	echo '
<!!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
	<head>
	<title>Styles Demo</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
	</head>';
	if (Frame_hide)
	{
		echo '
	<frameset id="stylemain" name="stylemain" rows="', Top_frame_height, ',10,*" border="0" framespacing="0" frameborder="0">'; 
	}
	else
	{
		echo '
	<frameset rows="', Top_frame_height, ',*" border="0" framespacing="0" frameborder="0">'; 
	}
	echo '
		<frame src="', $my_php_self, '?pane=top', $my_style,'" name="top" noresize scrolling="no">';
	if (Frame_hide)
	{
		echo '
		<frame src="', Inst_dir, 'stylesdemo/frame_insert.html" marginwidth="0" marginheight="0" frameborder="no" scrolling="no" noresize>';
	}
	echo '
		<frame src="', $my_php_self, '?pane=bottom', $my_style, '" name="bottom" scrolling="auto">
	</frameset>
	<noframes> 
	<body bgcolor="#FFFFFF" text="#000000">
	<p>Sorry, but your browser doesn\'t seem to support frames</p>
	</body>
	</noframes> 
</html>';
}
elseif ($_GET['pane'] == 'bottom')
{
	if (!empty($my_style))
	{
		$my_style = '?' . $my_style;
	}
	header("Location: " . Inst_dir . "index." . $phpEx . $my_style);
}
elseif ($_GET['pane'] == 'top')
{
	if(!isset($_GET['del_cookie']))
	{
		define('IN_PORTAL', true);
		include($phpbb_root_path . 'common.' . $phpEx);
		
		//
		// Page selector
		//
		$page_id = request_var('page', 1);

		//
		// Set page ID for session management
		//
		$userdata = session_pagestart($user_ip, $page_id);
		init_userprefs($userdata);
		//
		// End session management
		//
	}

	if (isset($_GET['style'])) 
	{
		$style_value = $_GET['style'];
		if (intval($style_value) == 0)
		{
			$sql = 'SELECT t.themes_id as style_id, t.style_name
				FROM ' . THEMES_TABLE . " t			
					WHERE t.style_name = '$style_value'";
			if(($result = $db->sql_query($sql)) && ($row = $db->sql_fetchrow($result)))
			{
				$style_value = $row['style_id'];
			}
			else
			{
				die('Could not find style name ' . $style_value . '!');
			}
		}
		else
		{
			$sql = 'SELECT t.themes_id as style_id, t.style_name
				FROM ' . THEMES_TABLE . " t
					WHERE t.themes_id = $style_value";
			if(!(($result = $db->sql_query($sql)) && ($row = $db->sql_fetchrow($result))))
			{
				die ('style_id ' . $style_value . ' not found');
			}
		}
	}
	elseif (isset($_COOKIE[$board_config['cookie_name'] . '_change_style']))
	{
		$style_value = $_COOKIE[$board_config['cookie_name'] . '_change_style'];
	}
	else
	{
		$style_value = ((!$board_config['override_user_style'] && $user->data['user_id'] != ANONYMOUS) ? $user->data['user_style'] : $board_config['default_style']);
	}
	
	/**
	*  Pick a template/theme combo, 
	* @ignore	
	*/
	
	$sql_where = isset($all) ? '' : '';
	$sql = 'SELECT t.themes_id, t.style_name
		FROM ' . THEMES_TABLE . " t
		$sql_where
		ORDER BY style_name";
	if (!($result = $db->sql_query($sql)))
	{
		message_die(CRITICAL_ERROR, 'Could not query user theme info');
	}
		
	$style_options = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$selected = ($row['themes_id'] == $board_config['default_style']) ? ' selected="selected"' : '';
		$style_options .= '<option value="' . $row['themes_id'] . '"' . $selected . '>' . $row['style_name'] . '</option>';
	}
	$db->sql_freeresult($result);

	$style_value = $style_options;

	// how much styles do we have in database vs . case "addnew" from admin_styles.php
	$sql = 'SELECT t.themes_id as style_id, t.style_name
		FROM ' . THEMES_TABLE . " t
		ORDER BY t.style_name ASC";
	$result = $db->sql_query($sql);
	$num_styles = sizeof($db->sql_fetchrowset($result));
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);


	echo '
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	echo '
	<script language="JavaScript" type="text/javascript" src="', Inst_dir, 'stylesdemo/fileinfo.js"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function download()
		{
			var thebox = document.framecombo;
			var id = thebox.framecombo2.options[thebox.framecombo2.selectedIndex].value
			if(downloads[id])
			{
				if(downloads[id].substring(0,7)=="http://")
					window.open(downloads[id],"download");
				else
					confirm(downloads[id])
			}
			else
				alert("URL undefined! downloads["+id+"] undefined!");
		}
		function jumpbox()
		{
			var thebox = document.framecombo
			var Style = thebox.framecombo2.options[thebox.framecombo2.selectedIndex].value
			window.parent.bottom.location = "', Inst_dir, 'index.' , $phpEx, '?style=" + Style;
			return;
		}
		function jumpbox1()
		{
			window.parent.location =  "', Inst_dir, 'index.' , $phpEx, '";
		}
		function delete_cookie()
		{
			window.parent.location =  "', $my_php_self, '?del_cookie";
		}
	// ]]></script>

	<style type="text/css">
		body
		{
			background-color: orange;
			margin: 0px;
		}
		td
		{
			font-family: Verdana, serif; font-size: 10px;
		}
		.lang_style
		{
			background-color: #E5E5E8;
			font-size: 10px;
			/*font-weight: bold;*/
			border: 0;
			padding: 0;
			text-align: right
		}
		select
		{
			color: White;
			background-color: #B22222;
			font-family: Verdana, sans-serif;
			font-size: 10px;
			font-weight: bold;
			border: 1px solid #A9B8C2;
			padding: 1px;
			width: 250px;
		}
		.button
		{
			background-color: #2400ff;
			background-attachment: scroll;
			font-family: Verdana, sans-serif;
			font-size: 10px;
			font-weight: bold;
			color: #FFFFFF;
			border-height: 2;
			text-decoration: none;
			border: 1 solid #FFFFFF;
			padding: 1
		}
	</style>
</head>
<body>
	<form name="framecombo">
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td><a href="', Inst_dir, 'index.' , $phpEx, '?style=" target="_parent" title="', $board_config['sitename'], ' Styles Demo"><img src="', Inst_dir, 'stylesdemo/site_logo.png" width="88" height="31" border="0" title="', $board_config['sitename'], ' Style Demo" /></a></td>
			<td><span>Styles:', $num_styles, '</span></td>
			<td>
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td align="right">
							Style:
							<select name="framecombo2" onChange="jumpbox();">', $style_value, '</select>';
	echo '
							<input type="button" onClick="if(document.forms[0].framecombo2.selectedIndex>0){ document.forms[0].framecombo2.selectedIndex--; jumpbox();}else{document.forms[0].framecombo2.selectedIndex=document.forms[0].framecombo2.length-1;jumpbox()}" id="Previous" value="&laquo;" class="button" />
							<input type="button" onClick="if(document.forms[0].framecombo2.selectedIndex<document.forms[0].framecombo2.length-1){ document.forms[0].framecombo2.selectedIndex++; jumpbox();}else{document.forms[0].framecombo2.selectedIndex=0;jumpbox()}" id="Next" value="&raquo;" class="button" />
							<input type="button" id="download1" value="Download style" onClick="download()" class="button">
							<input type="button" onClick="delete_cookie()" id="del_cookie" value="Delete cookies" class="button" />&nbsp;
						</td>
					</tr>
				</table>
			</td>
		    <td align="right"><a href="..', $board_config['script_path'], '/" onclick="window.open(this.href); return false;" title="', $board_config['sitename'], '"><img src="', Inst_dir, 'stylesdemo/site_logo.png" width="88" height="31" border="0" title="', $board_config['sitename'], '" /></a></td>
		</tr>
	</table>
	</form>
</body>
</html>';
}
?>