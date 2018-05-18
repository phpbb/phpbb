<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="{S_CONTENT_DIRECTION}">
<head>
	<meta http-equiv="content-type" content="text/html; charset={S_CONTENT_ENCODING}" />
	<meta http-equiv="content-style-type" content="text/css" />
	{META}
	{META_TAG}
	{NAV_LINKS}
	<title>{PAGE_TITLE}</title>
	<link rel="stylesheet" href="{FULL_SITE_PATH}{T_COMMON_TPL_PATH}lofi/lofi.css" type="text/css" />
	<script type="text/javascript" src="{FULL_SITE_PATH}{T_COMMON_TPL_PATH}js/ip_scripts.js"></script>
</head>
<body>
	<div id="wrapper">
		<div id="navigation">
			<div class="nav">
				<h1 style="font-size:14px;"><a href="{FULL_SITE_PATH}{U_INDEX}">{SITENAME}</a></h1>
				<h2 style="font-size:12px;">{SITE_DESCRIPTION}</h2>
			</div>
		</div>
		<div class="nav-toolbar">
			<a href="{FULL_SITE_PATH}{U_PORTAL}">{L_HOME}</a>&nbsp;&#8226;&nbsp;
			<a href="{FULL_SITE_PATH}{U_INDEX}">{L_FORUM}</a>&nbsp;&#8226;&nbsp;
			<a href="{FULL_SITE_PATH}{U_FAQ}">{L_FAQ}</a>&nbsp;&#8226;&nbsp;
			<a href="{FULL_SITE_PATH}{U_SEARCH}">{L_SEARCH}</a>&nbsp;&#8226;&nbsp;
			<a href="{FULL_SITE_PATH}{U_RECENT}">{L_RECENT}</a>&nbsp;&#8226;&nbsp;
			<!-- BEGIN switch_upi2db_off -->
			<a href="{FULL_SITE_PATH}{U_SEARCH_NEW}">{L_NEW2}</a>&nbsp;&#8226;&nbsp;
			<!-- END switch_upi2db_off -->
			<!-- BEGIN switch_upi2db_on -->
			<a href="{FULL_SITE_PATH}{U_SEARCH_NEW}">{L_NEW2}</a>&nbsp;&#8226;&nbsp;{L_DISPLAY_U}&nbsp;&#8226;&nbsp;{L_DISPLAY_M}&nbsp;&#8226;&nbsp;{L_DISPLAY_P}&nbsp;&#8226;&nbsp;
			<!-- END switch_upi2db_on -->
			<a href="{FULL_SITE_PATH}{U_MEMBERLIST}">{L_MEMBERLIST}</a>&nbsp;&#8226;&nbsp;
			<!-- IF not S_LOGGED_IN -->
			<a href="{FULL_SITE_PATH}{U_REGISTER}">{L_REGISTER}</a>&nbsp;&#8226;&nbsp;
			<!-- ELSE -->
			<a href="{FULL_SITE_PATH}{U_GROUP_CP}">{L_USERGROUPS}</a>&nbsp;&#8226;&nbsp;
			<a href="{FULL_SITE_PATH}{U_PROFILE}">{L_PROFILE}</a>&nbsp;&#8226;&nbsp;
			<!-- ENDIF -->
			<a href="{FULL_SITE_PATH}{U_LOGIN_LOGOUT}">{L_LOGIN_LOGOUT}</a>&nbsp;
		</div>
		<div id="content" class="content">
