<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                              frame_top.tpl
 *                              -------------
 *   copyright            : (C) 2003 - 2005 Vjacheslav Trushkin
 *   support              : http://www.stsoftware.biz/forum
 *
 *   version              : 2.4.0
 *
 *   file revision        : 79
 *   project revision     : 83
 *   last modified        : 12 Mar 2007  10:28:53
 *
 ***************************************************************************/
<!-- END xs_file_version -->

<html>
<head>
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" href="{XS_PATH}style.css" type="text/css">
<style>
<!--
body { background-image: url('{XS_PATH}images/top_bg.gif'); }
td.white { background-color: #1D80B2; color: #FFF; font-size: 11px; }
td.white a, td.white a:visited { color: #FFF; text-decoration: underline; }
td.white a:hover, td.white a:active { color: #FF9933; text-decoration: none; }
.div_logo { position: absolute; top: 0px; left: 0px; }
.div_nav { position: absolute; top: 59px; left: 10px; white-space: nowrap; font-size: 11px; color: #E0E0E0; }
.div_nav a, .div_nav a:visited { color: #FFF; text-decoration: none; }
.div_nav a:active, .div_nav a:hover { color: #FFF; text-decoration: underline; }
-->
</style>
</head>
<body>
<table width="100%" height="100%" cellspacing="0" cellpadding="0">
<tr>
	<td align="left" valign="top" width="285">

<div class="div_logo"><img src="{XS_PATH}images/top_logo.jpg" width="285" height="50" border="0" alt="" /></div>

<div class="div_nav">
<!-- BEGIN left_nav -->
[<a href="{left_nav.URL}" target="xs_main">{left_nav.TEXT}</a>] 
<!-- END left_nav -->
</div>

	</td>
	<td align="center" valign="middle" style="padding: 2px;"><!-- <table cellspacing="1" cellpadding="3" class="forumline">
	<tr>
		<td class="white"></td>
		<td class="white"></td>
	</tr>
	</table> --></td>
</tr>
</table>
</body>
</html>