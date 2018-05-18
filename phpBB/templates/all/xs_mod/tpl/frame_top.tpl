<!-- BEGIN xs_file_version -->
/**
*
* @package Icy Phoenix eXtreme Style 2.4.1
* @file $Id frame_top.tpl
* @author Vjacheslav Trushkin
* @copyright (C) 2003 - 2007
* @support http://www.stsoftware.biz/forum
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
<!-- END xs_file_version -->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="{S_CONTENT_DIRECTION}">
<head>
<meta http-equiv="content-type" content="text/html; charset={S_CONTENT_ENCODING}" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<link rel="stylesheet" href="{XS_PATH}style.css" type="text/css" />
<style type="text/css">
<!--
body { background-image: url('{XS_PATH}images/top_bg.gif'); }
td.white { background-color: #1d80b2; color: #ffffff; font-size: 11px; }
td.white a, td.white a:visited { color: #ffffff; text-decoration: underline; }
td.white a:hover, td.white a:active { color: #ff9933; text-decoration: none; }
.div_logo { position: absolute; top: 0px; left: 0px; }
.div_nav { position: absolute; top: 59px; left: 10px; white-space: nowrap; font-size: 11px; color: #e0e0e0; }
.div_nav a, .div_nav a:visited { color: #ffffff; text-decoration: none; }
.div_nav a:active, .div_nav a:hover { color: #ffffff; text-decoration: underline; }
-->
</style>
</head>
<body>
<table class="th100pct">
<tr>
	<td align="left" valign="top" width="285">
		<div class="div_logo"><img src="{XS_PATH}images/top_logo.jpg" width="285" height="50" alt="" /></div>
		<div class="div_nav">
		<!-- BEGIN left_nav -->
		[<a href="{left_nav.URL}" target="xs_main">{left_nav.TEXT}</a>]
		<!-- END left_nav -->
		</div>
	</td>
	<td align="center" valign="middle" style="padding: 2px;">
	<!--
	<table cellspacing="1" cellpadding="3" class="forumline">
	<tr>
		<td class="white"></td>
		<td class="white"></td>
	</tr>
	</table>
	-->
	</td>
</tr>
</table>
</body>
</html>