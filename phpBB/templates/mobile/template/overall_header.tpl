{DOCTYPE_HTML}
<head>
<!-- INCLUDE overall_inc_header.tpl -->
<!-- This goes first, so that the other scripts can be 'jQuerized' -->
<script type="text/javascript" src="{FULL_SITE_PATH}templates/all/jquery/jquery_compressed.js"></script>
<script type="text/javascript">
// <![CDATA[
/*
$("a.menu-main").click(function() {
	$("ul.menu").toggle();
});
*/
// ]]>
</script>
<link href="{T_STYLESHEET_LINK}" rel="stylesheet" type="text/css" media="screen, projection" />
<link href="{T_STYLESHEET_LANG_LINK}" rel="stylesheet" type="text/css" media="screen, projection" />
</head>
<body id="phpbb" class="section-index {S_CONTENT_DIRECTION}">
<div id="page-header">
	<h1><?php
	$list = array('_TITLE', 'TOPIC_TITLE_SHORT', 'SITENAME');
	for($i=0; $i < count($list); $i++)
	{
		if(isset($this->vars[$list[$i]]))
		{
			echo $this->vars[$list[$i]];
			break;
		}
	}
	?></h1>
	<div class="header-links">
		<a href="javascript:void(0);" class="menu-main"></a>
		<ul class="menu">

			<li><a href="{U_INDEX}">{L_INDEX}</a></li>
			<!-- BEGIN switch_upi2db_off -->
			<li><a href="{U_SEARCH_NEW}">{L_NEW2}</a></li>
			<!-- END switch_upi2db_off -->
			<!-- BEGIN switch_upi2db_on -->
			<li><a href="{U_SEARCH_NEW}">{L_NEW2}</a></li>
			<!-- END switch_upi2db_on -->
			<!-- IF S_LOGGED_IN -->
			<li><a href="{U_PROFILE}">{L_PROFILE}</a></li>
			<!-- ENDIF -->
			<li><a href="{U_SEARCH}">{L_SEARCH}</a></li>
			<li><a href="{U_FAQ}">{L_FAQ}</a></li>
			<!-- IF not S_LOGGED_IN -->
			<li><a href="{U_REGISTER}">{L_REGISTER}</a></li>
			<!-- ENDIF -->
			<li><a href="{U_LOGIN_LOGOUT}">{L_LOGIN_LOGOUT2}</a></li>
			<!-- IF S_MOBILE --><li>{MOBILE_STYLE}</li><!-- ENDIF -->
		</ul>
	</div>
</div>
<div id="page-body">

<!-- IF S_PAGE_NAV --><!-- INCLUDE breadcrumbs_main.tpl --><!-- ENDIF -->


<div id="wrap">
	<a id="top" name="top"></a>
	<div id="page-header">
		<div class="headerbar">
			<div class="inner"><span class="corners-top"><span></span></span>
			<div id="site-description">
				<a href="{U_INDEX}" title="{L_INDEX}" id="logo"><h1>{SITENAME}</h1></a>
				
				<p>{SITE_DESCRIPTION}</p>
				<p style="display: none;"><a href="#start_here">{L_SKIP}</a></p>
			</div>
			<div id="search-box">
				<form action="{U_SEARCH}" method="get" id="search">
				<fieldset>
					<input name="search_keywords" id="keywords" type="text" maxlength="128" title="" class="inputbox search" value="{L_SEARCH}..." onclick="if(this.value=='{L_SEARCH}...')this.value='';" onblur="if(this.value=='')this.value='{L_SEARCH}...';" /> 
					<input class="button2" value="{L_SEARCH}" type="submit" /><br />
					{S_HIDDEN_FIELDS}
				</fieldset>
				</form>
			</div>
			<span class="corners-bottom"><span></span></span></div>
		</div>
	</div>
	<a name="start_here"></a>
	<div id="page-body">

<!-- ENDIF -->