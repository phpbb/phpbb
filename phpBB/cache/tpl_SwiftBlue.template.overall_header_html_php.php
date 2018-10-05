<?php

// eXtreme Styles mod cache. Generated on Wed, 26 Sep 2018 04:17:50 +0000 (time=1537935470)

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo isset($this->vars['S_CONTENT_DIRECTION']) ? $this->vars['S_CONTENT_DIRECTION'] : $this->lang('S_CONTENT_DIRECTION'); ?>" lang="<?php echo isset($this->vars['S_USER_LANG']) ? $this->vars['S_USER_LANG'] : $this->lang('S_USER_LANG'); ?>" xml:lang="<?php echo isset($this->vars['S_USER_LANG']) ? $this->vars['S_USER_LANG'] : $this->lang('S_USER_LANG'); ?>">
<head>

<meta http-equiv="content-type" content="text/html; charset=<?php echo isset($this->vars['S_CONTENT_ENCODING']) ? $this->vars['S_CONTENT_ENCODING'] : $this->lang('S_CONTENT_ENCODING'); ?>" />
<meta http-equiv="content-language" content="<?php echo isset($this->vars['S_USER_LANG']) ? $this->vars['S_USER_LANG'] : $this->lang('S_USER_LANG'); ?>" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="imagetoolbar" content="no" />
<meta name="resource-type" content="document" />
<meta name="distribution" content="global" />
<meta name="copyright" content="2000, 2002, 2005, 2007 phpBB Group" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php echo isset($this->vars['META']) ? $this->vars['META'] : $this->lang('META'); ?>
<title><?php if ($this->vars['UNREAD_NOTIFICATIONS_COUNT']) {  ?>(<?php echo isset($this->vars['UNREAD_NOTIFICATIONS_COUNT']) ? $this->vars['UNREAD_NOTIFICATIONS_COUNT'] : $this->lang('UNREAD_NOTIFICATIONS_COUNT'); ?>) <?php } ?><?php if (! $this->vars['S_VIEWTOPIC'] && ! $this->vars['S_VIEWFORUM']) {  ?><?php echo isset($this->vars['SITENAME']) ? $this->vars['SITENAME'] : $this->lang('SITENAME'); ?> - <?php } ?><?php if ($this->vars['S_IN_MCP']) {  ?><?php echo isset($this->vars['L_MCP']) ? $this->vars['L_MCP'] : $this->lang('L_MCP'); ?> - <?php } elseif ($this->vars['S_IN_UCP']) {  ?><?php echo isset($this->vars['L_UCP']) ? $this->vars['L_UCP'] : $this->lang('L_UCP'); ?> - <?php } ?><?php echo isset($this->vars['PAGE_TITLE']) ? $this->vars['PAGE_TITLE'] : $this->lang('PAGE_TITLE'); ?><?php if ($this->vars['S_VIEWTOPIC'] || $this->vars['S_VIEWFORUM']) {  ?> - <?php echo isset($this->vars['SITENAME']) ? $this->vars['SITENAME'] : $this->lang('SITENAME'); ?><?php } ?></title>

<?php if ($this->vars['S_ENABLE_FEEDS']) {  ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_OVERALL']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['SITENAME']) ? $this->vars['SITENAME'] : $this->lang('SITENAME'); ?>" href="{{ path('phpbb_feed_index') }}"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_NEWS']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_FEED_NEWS']) ? $this->vars['L_FEED_NEWS'] : $this->lang('L_FEED_NEWS'); ?>" href="{{ path('phpbb_feed_news') }}"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_FORUMS']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_ALL_FORUMS']) ? $this->vars['L_ALL_FORUMS'] : $this->lang('L_ALL_FORUMS'); ?>" href="{{ path('phpbb_feed_forums') }}"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_TOPICS']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_FEED_TOPICS_NEW']) ? $this->vars['L_FEED_TOPICS_NEW'] : $this->lang('L_FEED_TOPICS_NEW'); ?>" href="{{ path('phpbb_feed_topics') }}"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_TOPICS_ACTIVE']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_FEED_TOPICS_ACTIVE']) ? $this->vars['L_FEED_TOPICS_ACTIVE'] : $this->lang('L_FEED_TOPICS_ACTIVE'); ?>" href="{{ path('phpbb_feed_topics_active') }}"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_FORUM'] && $this->vars['S_FORUM_ID']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_FORUM']) ? $this->vars['L_FORUM'] : $this->lang('L_FORUM'); ?> - <?php echo isset($this->vars['FORUM_NAME']) ? $this->vars['FORUM_NAME'] : $this->lang('FORUM_NAME'); ?>" href="{{ path('phpbb_feed_forum', { forum_id : S_FORUM_ID } ) }}"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_TOPIC'] && $this->vars['S_TOPIC_ID']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_TOPIC']) ? $this->vars['L_TOPIC'] : $this->lang('L_TOPIC'); ?> - <?php echo isset($this->vars['TOPIC_TITLE']) ? $this->vars['TOPIC_TITLE'] : $this->lang('TOPIC_TITLE'); ?>" href="{{ path('phpbb_feed_topic', { topic_id : S_TOPIC_ID } ) }}"><?php } ?>
	<!-- EVENT overall_header_feeds -->
<?php } ?>

<?php if ($this->vars['U_CANONICAL']) {  ?>
	<link rel="canonical" href="<?php echo isset($this->vars['U_CANONICAL']) ? $this->vars['U_CANONICAL'] : $this->lang('U_CANONICAL'); ?>" />
<?php } ?>

<?php if ($this->vars['S_ALLOW_CDN']) {  ?>
<script>
	WebFontConfig = {
		google: {
			families: ['Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese']
		}
	};

	(function(d) {
		var wf = d.createElement('script'), s = d.scripts[0];
		wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1.5.18/webfont.js';
		wf.async = true;
		s.parentNode.insertBefore(wf, s);
	})(document);
</script>
<?php } ?>

<link href="<?php echo isset($this->vars['T_FONT_AWESOME_LINK']) ? $this->vars['T_FONT_AWESOME_LINK'] : $this->lang('T_FONT_AWESOME_LINK'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo isset($this->vars['T_STYLESHEET_LINK']) ? $this->vars['T_STYLESHEET_LINK'] : $this->lang('T_STYLESHEET_LINK'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo isset($this->vars['T_STYLESHEET_LANG_LINK']) ? $this->vars['T_STYLESHEET_LANG_LINK'] : $this->lang('T_STYLESHEET_LANG_LINK'); ?>" rel="stylesheet" type="text/css" />

<?php if ($this->vars['S_CONTENT_DIRECTION'] == 'rtl') {  ?>
	<link href="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/bidi.css?assets_version=<?php echo isset($this->vars['T_ASSETS_VERSION']) ? $this->vars['T_ASSETS_VERSION'] : $this->lang('T_ASSETS_VERSION'); ?>" rel="stylesheet" type="text/css" />
<?php } ?>

<?php if ($this->vars['S_PLUPLOAD']) {  ?>
	<link href="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/plupload.css?assets_version=<?php echo isset($this->vars['T_ASSETS_VERSION']) ? $this->vars['T_ASSETS_VERSION'] : $this->lang('T_ASSETS_VERSION'); ?>" rel="stylesheet" type="text/css" />
<?php } ?>

<?php if ($this->vars['S_COOKIE_NOTICE']) {  ?>
	<link href="<?php echo isset($this->vars['T_ASSETS_PATH']) ? $this->vars['T_ASSETS_PATH'] : $this->lang('T_ASSETS_PATH'); ?>/cookieconsent/cookieconsent.min.css?assets_version=<?php echo isset($this->vars['T_ASSETS_VERSION']) ? $this->vars['T_ASSETS_VERSION'] : $this->lang('T_ASSETS_VERSION'); ?>" rel="stylesheet" type="text/css" />
<?php } ?>

<link href="<?php echo isset($this->vars['T_STYLESHEET_LINK']) ? $this->vars['T_STYLESHEET_LINK'] : $this->lang('T_STYLESHEET_LINK'); ?>" rel="stylesheet" type="text/css" />

<script type="text/javascript">
// <![CDATA[
<?php if ($this->vars['S_USER_PM_POPUP']) {  ?>
	if (<?php echo isset($this->vars['S_NEW_PM']) ? $this->vars['S_NEW_PM'] : $this->lang('S_NEW_PM'); ?>)
	{
		popup('<?php echo isset($this->vars['UA_POPUP_PM']) ? $this->vars['UA_POPUP_PM'] : $this->lang('UA_POPUP_PM'); ?>', 400, 225, '_phpbbprivmsg');
	}
<?php } ?>

function popup(url, width, height, name)
{
	if (!name)
	{
		name = '_popup';
	}

	window.open(url.replace(/&amp;/g, '&'), name, 'height=' + height + ',resizable=yes,scrollbars=yes,width=' + width);
	return false;
}

function jumpto()
{
	var page = prompt('<?php echo isset($this->vars['LA_JUMP_PAGE']) ? $this->vars['LA_JUMP_PAGE'] : $this->lang('LA_JUMP_PAGE'); ?>:', '<?php echo isset($this->vars['ON_PAGE']) ? $this->vars['ON_PAGE'] : $this->lang('ON_PAGE'); ?>');
	var perpage = '<?php echo isset($this->vars['PER_PAGE']) ? $this->vars['PER_PAGE'] : $this->lang('PER_PAGE'); ?>';
	var base_url = '<?php echo isset($this->vars['A_BASE_URL']) ? $this->vars['A_BASE_URL'] : $this->lang('A_BASE_URL'); ?>';

	if (page !== null && !isNaN(page) && page > 0)
	{
		document.location.href = base_url.replace(/&amp;/g, '&') + '&start=' + ((page - 1) * perpage);
	}
}



/**
* Find a member
*/
function find_username(url)
{
	popup(url, 760, 570, '_usersearch');
	return false;
}

/**
* Mark/unmark checklist
* id = ID of parent container, name = name prefix, state = state [true/false]
*/
function marklist(id, name, state)
{
	var parent = document.getElementById(id);
	if (!parent)
	{
		eval('parent = document.' + id);
	}

	if (!parent)
	{
		return;
	}

	var rb = parent.getElementsByTagName('input');
	
	for (var r = 0; r < rb.length; r++)
	{
		if (rb[r].name.substr(0, name.length) == name)
		{
			rb[r].checked = state;
		}
	}
}

<?php if ($_file) {  ?>

	/**
	* Play quicktime file by determining it's width/height
	* from the displayed rectangle area
	*
	* Only defined if there is a file block present.
	*/
	function play_qt_file(obj)
	{
		var rectangle = obj.GetRectangle();

		if (rectangle)
		{
			rectangle = rectangle.split(',')
			var x1 = parseInt(rectangle[0]);
			var x2 = parseInt(rectangle[2]);
			var y1 = parseInt(rectangle[1]);
			var y2 = parseInt(rectangle[3]);

			var width = (x1 < 0) ? (x1 * -1) + x2 : x2 - x1;
			var height = (y1 < 0) ? (y1 * -1) + y2 : y2 - y1;
		}
		else
		{
			var width = 200;
			var height = 0;
		}

		obj.width = width;
		obj.height = height + 16;

		obj.SetControllerVisible(true);

		obj.Play();
	}
<?php } ?>

// ]]>
</script>

<!-- EVENT overall_header_head_append -->

<?php echo isset($this->_tpldata['DEFINE']['.']['STYLESHEETS']) ? $this->_tpldata['DEFINE']['.']['STYLESHEETS'] : ''; ?>

<!-- EVENT overall_header_stylesheets_after -->

</head>

<!-- EVENT overall_header_body_before -->
<body id="phpbb" bgcolor="#7EB5E8" text="#000000" link="#072978" vlink="#072978" class="nojs notouch section-<?php echo isset($this->vars['SCRIPT_NAME']) ? $this->vars['SCRIPT_NAME'] : $this->lang('SCRIPT_NAME'); ?> <?php echo isset($this->vars['S_CONTENT_DIRECTION']) ? $this->vars['S_CONTENT_DIRECTION'] : $this->lang('S_CONTENT_DIRECTION'); ?> <?php echo isset($this->vars['BODY_CLASS']) ? $this->vars['BODY_CLASS'] : $this->lang('BODY_CLASS'); ?>">

<a name="top" class="anchor"></a>

<div id="wrapheader">
	
<table width="100--" cellspacing="0" cellpadding="1" border="0" align="center" class="table1 main_table">
	
	<!-- EVENT overall_header_headerbar_before -->	
	<thead>	
	<tr class="bg1">		
		<td class="bodyline"><a id="logo" class="imageset site_logo responsive-hide" href="<?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?>"><!-- <?php echo isset($this->vars['SITE_LOGO_IMG']) ? $this->vars['SITE_LOGO_IMG'] : $this->lang('SITE_LOGO_IMG'); ?>--></a>
			
			<div id="logodesc">
				
				<table width="100--" align="center" cellspacing="0" cellpadding="5" border="0" class="table1 main_table">
					<tbody>
					<tr class="bg1">
						
						<td class="table1 responsive-hide" style="text-align:center;" width="5--">&nbsp;&nbsp;</td>						
						
						<td class="table1 responsive-hide" align="center" valign="middle" width="15--">
							<span class="responsive-hide">&nbsp;&nbsp;</span>
						</td>
						
						<td class="table1 responsive-hide" align="right" valign="middle" width="15--">
							<span class="pagetitle">&nbsp;&nbsp;<?php echo isset($this->vars['PAGE_TITLE']) ? $this->vars['PAGE_TITLE'] : $this->lang('PAGE_TITLE'); ?></span>
						</td>						
						
						<td class="table1" style="text-align:center;" valign="middle" width="60--">
							<h1><?php echo isset($this->vars['SITENAME']) ? $this->vars['SITENAME'] : $this->lang('SITENAME'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</h1>
							<span class="gen"><?php echo isset($this->vars['SITE_DESCRIPTION']) ? $this->vars['SITE_DESCRIPTION'] : $this->lang('SITE_DESCRIPTION'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</span>						
						</td>						
						
						<td class="table1 responsive-hide" align="center" valign="middle" width="5--">&nbsp;</td>					
					
					</tr>
					</tbody>
				</table>
			
			</div>			
			
			
			<!-- EVENT overall_header_searchbox_before -->
			<?php if ($this->vars['S_DISPLAY_SEARCH'] && ! $this->vars['S_IN_SEARCH']) {  ?>
			<div id="search-box" class="search-box search-header right" role="search">
					
					<table width="100--" cellspacing="0" cellpadding="5" border="0" align="center" class="table1 main_table">
					<tbody>						
						<tr class="bg1">
							
							<td class="table1 responsive-hide" style="text-align:center;"></td>
							
							<td class="table1 responsive-hide" valign="top" align="left" width="5" height="5" >&nbsp;</td>
							
							<td class="table1" valign="top" align="right" width="100--" height="5" >
				
							<form action="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" method="get" id="search">
							<fieldset>
								<input name="search_keywords" id="keywords" type="text" maxlength="128" title="<?php echo isset($this->vars['L_SEARCH_KEYWORDS']) ? $this->vars['L_SEARCH_KEYWORDS'] : $this->lang('L_SEARCH_KEYWORDS'); ?>" class="inputbox search tiny rightside responsive-search" value="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>..." onclick="if(this.value=='<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>...')this.value='';" onblur="if(this.value=='')this.value='<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>...';" placeholder="<?php echo isset($this->vars['L_SEARCH_MINI']) ? $this->vars['L_SEARCH_MINI'] : $this->lang('L_SEARCH_MINI'); ?>" /> 
								<button class="button button-search" type="submit" title="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>">
									<i class="icon fa-search fa-fw" aria-hidden="true"></i><span class="sr-only"><?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?></span>
								</button>								
								<a href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" class="button button-search-end" title="<?php echo isset($this->vars['L_SEARCH_ADV']) ? $this->vars['L_SEARCH_ADV'] : $this->lang('L_SEARCH_ADV'); ?>">
									<i class="icon fa-cog fa-fw" aria-hidden="true"></i><span class="sr-only"><?php echo isset($this->vars['L_SEARCH_ADV']) ? $this->vars['L_SEARCH_ADV'] : $this->lang('L_SEARCH_ADV'); ?></span>
								</a>
								<?php echo isset($this->vars['S_SEARCH_HIDDEN_FIELDS']) ? $this->vars['S_SEARCH_HIDDEN_FIELDS'] : $this->lang('S_SEARCH_HIDDEN_FIELDS'); ?>
							</fieldset>
							</form>
							
							</td>
							
							<td class="table1 responsive-hide" valign="top" align="left" width="5" height="5" >
								<span class="responsive-hide">&nbsp;&nbsp;</span>
							</td>
							
							<td class="table1 responsive-hide" align="center" valign="middle" width="5--">&nbsp;</td>						
						
						</tr>
					</tbody>					
					</table>
			</div>
			<?php } ?>
			<!-- EVENT overall_header_searchbox_after -->	
		
		
		</td>
	</tr>
	</thead>	
		
	<!-- EVENT overall_header_navbar_before -->		
	<tbody>
	<div id="page-header" role="navigation">

	<tr id="nav-main" class="nav-main linklist bg2" role="menubar">
				
		<td class="genmed" style="width: 5--; text-align: center;">	
							
		<!-- EVENT overall_header_navigation_prepend -->				
		<table id="nav-main" class="table1 nav-main linklist" cellspacing="6" cellpadding="2" border="0" role="menubar">
		<div class="navbar-top" role="navigation">
		<thead>
			<div class="inner">						
					<ul id="nav-main" class="nav-main linklist" role="menubar">						
					<tr class="bg2"> 
						<td id="quick-links" class="quick-links dropdown-container responsive-menu<?php if (! $this->vars['S_DISPLAY_QUICK_LINKS'] && ! $this->vars['S_DISPLAY_SEARCH']) {  ?> hidden<?php } ?>" data-skip-responsive="true" role="menuitem">
						
						<span class="mainmenu dropdown-trigger">
							<a href="#" class="dropdown-trigger">
								<i class="icon fa-bars fa-fw" aria-hidden="true">
								<!-- img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/nav_forum.gif"  border="0" alt="<?php echo isset($this->vars['L_QUICK_LINKS']) ? $this->vars['L_QUICK_LINKS'] : $this->lang('L_QUICK_LINKS'); ?>" hspace="3" /-->
								</i>
							</a>
						</span>						
														
						<div class="dropdown">
							<div class="pointer"><div class="pointer-inner"></div></div>
							<ul class="dropdown-contents" role="menu">
								<!-- EVENT navbar_header_quick_links_before -->

								<?php if ($this->vars['S_DISPLAY_SEARCH']) {  ?>
									<li class="separator"></li>
									<?php if ($this->vars['S_REGISTERED_USER']) {  ?>
										<li>
											<a href="<?php echo isset($this->vars['U_SEARCH_SELF']) ? $this->vars['U_SEARCH_SELF'] : $this->lang('U_SEARCH_SELF'); ?>" role="menuitem">
												<i class="icon fa-file-o fa-fw icon-gray" aria-hidden="true"></i><span><?php echo isset($this->vars['L_SEARCH_SELF']) ? $this->vars['L_SEARCH_SELF'] : $this->lang('L_SEARCH_SELF'); ?></span>
											</a>
										</li>
									<?php } ?>
									<?php if ($this->vars['S_USER_LOGGED_IN']) {  ?>
										<li>
											<a href="<?php echo isset($this->vars['U_SEARCH_NEW']) ? $this->vars['U_SEARCH_NEW'] : $this->lang('U_SEARCH_NEW'); ?>" role="menuitem">
												<i class="icon fa-file-o fa-fw icon-red" aria-hidden="true"></i><span><?php echo isset($this->vars['L_SEARCH_NEW']) ? $this->vars['L_SEARCH_NEW'] : $this->lang('L_SEARCH_NEW'); ?></span>
											</a>
										</li>
									<?php } ?>
									<?php if ($this->vars['S_LOAD_UNREADS']) {  ?>
										<li>
											<a href="<?php echo isset($this->vars['U_SEARCH_UNREAD']) ? $this->vars['U_SEARCH_UNREAD'] : $this->lang('U_SEARCH_UNREAD'); ?>" role="menuitem">
												<i class="icon fa-file-o fa-fw icon-red" aria-hidden="true"></i><span><?php echo isset($this->vars['L_SEARCH_UNREAD']) ? $this->vars['L_SEARCH_UNREAD'] : $this->lang('L_SEARCH_UNREAD'); ?></span>
											</a>
										</li>
									<?php } ?>
										<li>
											<a href="<?php echo isset($this->vars['U_SEARCH_UNANSWERED']) ? $this->vars['U_SEARCH_UNANSWERED'] : $this->lang('U_SEARCH_UNANSWERED'); ?>" role="menuitem">
												<i class="icon fa-file-o fa-fw icon-gray" aria-hidden="true"></i><span><?php echo isset($this->vars['L_SEARCH_UNANSWERED']) ? $this->vars['L_SEARCH_UNANSWERED'] : $this->lang('L_SEARCH_UNANSWERED'); ?></span>
											</a>
										</li>
										<li>
											<a href="<?php echo isset($this->vars['U_SEARCH_ACTIVE_TOPICS']) ? $this->vars['U_SEARCH_ACTIVE_TOPICS'] : $this->lang('U_SEARCH_ACTIVE_TOPICS'); ?>" role="menuitem">
												<i class="icon fa-file-o fa-fw icon-blue" aria-hidden="true"></i><span><?php echo isset($this->vars['L_SEARCH_ACTIVE_TOPICS']) ? $this->vars['L_SEARCH_ACTIVE_TOPICS'] : $this->lang('L_SEARCH_ACTIVE_TOPICS'); ?></span>
											</a>
										</li>
										<li class="separator"></li>
										<li>
											<a href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" role="menuitem">
												<i class="icon fa-search fa-fw" aria-hidden="true"></i><span><?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?></span>
											</a>
										</li>
								<?php } ?>

								<?php if (! $this->vars['S_IS_BOT'] && ( $this->vars['S_DISPLAY_MEMBERLIST'] || $this->vars['U_TEAM'] )) {  ?>
									<li class="separator"></li>
									<?php if ($this->vars['S_DISPLAY_MEMBERLIST']) {  ?>
										<li>
											<a href="<?php echo isset($this->vars['U_MEMBERLIST']) ? $this->vars['U_MEMBERLIST'] : $this->lang('U_MEMBERLIST'); ?>" role="menuitem">
												<i class="icon fa-group fa-fw" aria-hidden="true"></i><span><?php echo isset($this->vars['L_MEMBERLIST']) ? $this->vars['L_MEMBERLIST'] : $this->lang('L_MEMBERLIST'); ?></span>
											</a>
										</li>
									<?php } ?>
									<?php if ($this->vars['U_TEAM']) {  ?>
										<li>
											<a href="<?php echo isset($this->vars['U_TEAM']) ? $this->vars['U_TEAM'] : $this->lang('U_TEAM'); ?>" role="menuitem">
												<i class="icon fa-shield fa-fw" aria-hidden="true"></i><span><?php echo isset($this->vars['L_THE_TEAM']) ? $this->vars['L_THE_TEAM'] : $this->lang('L_THE_TEAM'); ?></span>
											</a>
										</li>
									<?php } ?>
								<?php } ?>
								<li class="separator"></li>

								<!-- EVENT navbar_header_quick_links_after -->
							</ul>
						</div>
								
						</td>
						
						<td data-last-responsive="true" class="nav-main linklist table1 responsive-hide" role="menubar" height="15" align="center" valign="top"><span class="mainmenu">
							<span class="mainmenu">						
							<a href="<?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?>" title="<?php echo isset($this->vars['L_INDEX']) ? $this->vars['L_INDEX'] : $this->lang('L_INDEX'); ?>" role="menuitem" class="mainmenu">
								<img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/nav_forum.gif" border="0" alt="<?php echo isset($this->vars['L_FORUM']) ? $this->vars['L_FORUM'] : $this->lang('L_FORUM'); ?>" hspace="3" />
							</a>						
							</span>
						</td>
						
						<td data-last-responsive="true" class="table1" height="15" align="center" valign="top">
							<span class="mainmenu">
							<a href="<?php echo isset($this->vars['U_FAQ']) ? $this->vars['U_FAQ'] : $this->lang('U_FAQ'); ?>" title="<?php echo isset($this->vars['L_FAQ']) ? $this->vars['L_FAQ'] : $this->lang('L_FAQ'); ?>" role="menuitem" class="mainmenu" height="15" align="center" valign="top">
								<img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/nav_help.gif" border="0" alt="<?php echo isset($this->vars['L_FAQ']) ? $this->vars['L_FAQ'] : $this->lang('L_FAQ'); ?>" hspace="3" />
							</a>
							</span>
						</td>
						
						<?php if ($this->vars['U_ACP']) {  ?>
						<td data-last-responsive="true" class="table1 responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">							
							<a href="<?php echo isset($this->vars['U_ACP']) ? $this->vars['U_ACP'] : $this->lang('U_ACP'); ?>" title="<?php echo isset($this->vars['L_ACP']) ? $this->vars['L_ACP'] : $this->lang('L_ACP'); ?>" role="menuitem" class="table1" height="15" align="center" valign="top">
								<img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/nav_settings.gif" aria-hidden="true" border="0" alt="<?php echo isset($this->vars['L_ACP_SHORT']) ? $this->vars['L_ACP_SHORT'] : $this->lang('L_ACP_SHORT'); ?>" hspace="3" />
							</a>
							</span>						
						</td>
						<?php } ?>
						<?php if ($this->vars['U_MCP']) {  ?>
						<td data-last-responsive="true" class="table1 responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">							
							<a href="<?php echo isset($this->vars['U_MCP']) ? $this->vars['U_MCP'] : $this->lang('U_MCP'); ?>" title="<?php echo isset($this->vars['L_MCP']) ? $this->vars['L_MCP'] : $this->lang('L_MCP'); ?>" role="menuitem" class="mainmenu">
								<img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/nav_groups.gif" aria-hidden="true" border="0" alt="<?php echo isset($this->vars['L_MCP_SHORT']) ? $this->vars['L_MCP_SHORT'] : $this->lang('L_MCP_SHORT'); ?>" hspace="3" />								
							</a>
							</span>						
						</td>
						<?php } ?>						
												
						<?php if ($this->vars['S_DISPLAY_SEARCH']) {  ?>
						<td data-last-responsive="true" class="table1 responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">
							<a href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" title="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>" role="menuitem" class="mainmenu">				
								<img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/nav_search.gif" border="0" alt="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>" hspace="3" />
							</a>
							</span>
						</td>
						<?php } ?>
						<?php if (! $this->vars['S_IS_BOT']) {  ?>
						<?php if ($this->vars['S_DISPLAY_MEMBERLIST']) {  ?>
						<td data-last-responsive="true" class="table1 responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">
							<a href="<?php echo isset($this->vars['U_MEMBERLIST']) ? $this->vars['U_MEMBERLIST'] : $this->lang('U_MEMBERLIST'); ?>" title="<?php echo isset($this->vars['L_MEMBERLIST']) ? $this->vars['L_MEMBERLIST'] : $this->lang('L_MEMBERLIST'); ?>" role="menuitem" class="mainmenu">								
								<img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/nav_members.gif" border="0" alt="<?php echo isset($this->vars['L_MEMBERLIST']) ? $this->vars['L_MEMBERLIST'] : $this->lang('L_MEMBERLIST'); ?>" hspace="3" />
							</a>
							</span>
						</td>
						<?php } ?>
						<?php if ($this->vars['S_USER_LOGGED_IN']) {  ?>
						<td data-last-responsive="true" class="table1" height="15" align="center" valign="top">
							<span class="mainmenu">
							<a href="<?php echo isset($this->vars['U_PROFILE']) ? $this->vars['U_PROFILE'] : $this->lang('U_PROFILE'); ?>" title="<?php echo isset($this->vars['L_PROFILE']) ? $this->vars['L_PROFILE'] : $this->lang('L_PROFILE'); ?>" role="menuitem" class="mainmenu">
								<img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/nav_profile.gif" border="0" alt="<?php echo isset($this->vars['L_PROFILE']) ? $this->vars['L_PROFILE'] : $this->lang('L_PROFILE'); ?>" hspace="3" />
							</a>
							</span>
						</td>
						<?php } ?>
						<?php } ?>						
						<?php if ($this->vars['S_DISPLAY_PM']) {  ?>
						<td data-last-responsive="true" class="table1" height="15" align="center" valign="top">
							<span class="mainmenu">
							<a href="<?php echo isset($this->vars['U_PRIVATEMSGS']) ? $this->vars['U_PRIVATEMSGS'] : $this->lang('U_PRIVATEMSGS'); ?>" title="<?php echo isset($this->vars['L_PRIVATE_MESSAGES']) ? $this->vars['L_PRIVATE_MESSAGES'] : $this->lang('L_PRIVATE_MESSAGES'); ?>" class="mainmenu" role="menuitem">
								<img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/nav_email.gif" border="0" alt="<?php echo isset($this->vars['PRIVATE_MESSAGE_INFO']) ? $this->vars['PRIVATE_MESSAGE_INFO'] : $this->lang('PRIVATE_MESSAGE_INFO'); ?>" hspace="3" />
							</a>
							</span>
						</td>						
						<?php } ?>
						<?php if (! $this->vars['S_IS_BOT']) {  ?>						
						<td data-last-responsive="true" class="table1 responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">
							<a href="<?php echo isset($this->vars['U_LOGIN_LOGOUT']) ? $this->vars['U_LOGIN_LOGOUT'] : $this->lang('U_LOGIN_LOGOUT'); ?>" title="<?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?>" class="mainmenu" role="menuitem">
								<img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/nav_login.gif" border="0" alt="<?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?>" hspace="3" />
							</a>							
							</span>
						</td>
						<?php } ?>
						<?php if ($this->vars['S_USER_LOGGED_OUT']) {  ?>
						<td data-last-responsive="true" class="table1 responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">							
							<a href="<?php echo isset($this->vars['U_REGISTER']) ? $this->vars['U_REGISTER'] : $this->lang('U_REGISTER'); ?>" title="<?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?>" class="mainmenu" role="menuitem">
								<img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/nav_register.gif" border="0" alt="<?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?>" hspace="3" />
							</a>
							</span>
						</td>
						&nbsp;
						<?php } ?>
	<?php if ($this->vars['S_REGISTERED_USER']) {  ?>
						<td data-last-responsive="true" class="forumtitle genmed" height="15" align="center" valign="top">	
		<!-- EVENT navbar_header_user_profile_prepend -->
		<div id="username_logged_in" class="rightside <?php if ($this->vars['CURRENT_USER_AVATAR']) {  ?> no-bulletin<?php } ?>" data-skip-responsive="true">
			<!-- EVENT navbar_header_username_prepend -->
			<div class="header-profile dropdown-container">
				<a href="<?php echo isset($this->vars['U_PROFILE']) ? $this->vars['U_PROFILE'] : $this->lang('U_PROFILE'); ?>" class="header-avatar dropdown-trigger"><?php if ($this->vars['CURRENT_USER_AVATAR']) {  ?><?php echo isset($this->vars['CURRENT_USER_AVATAR']) ? $this->vars['CURRENT_USER_AVATAR'] : $this->lang('CURRENT_USER_AVATAR'); ?> <?php } ?> <?php echo isset($this->vars['CURRENT_USERNAME_SIMPLE']) ? $this->vars['CURRENT_USERNAME_SIMPLE'] : $this->lang('CURRENT_USERNAME_SIMPLE'); ?></a>
				<div class="dropdown">
					<div class="pointer"><div class="pointer-inner"></div></div>
					<ul class="dropdown-contents" role="menu">
						<?php if ($this->vars['U_RESTORE_PERMISSIONS']) {  ?>
							<li>
								<a href="<?php echo isset($this->vars['U_RESTORE_PERMISSIONS']) ? $this->vars['U_RESTORE_PERMISSIONS'] : $this->lang('U_RESTORE_PERMISSIONS'); ?>">
									<i class="icon fa-refresh fa-fw" aria-hidden="true"></i><span><?php echo isset($this->vars['L_RESTORE_PERMISSIONS']) ? $this->vars['L_RESTORE_PERMISSIONS'] : $this->lang('L_RESTORE_PERMISSIONS'); ?></span>
								</a>
							</li>
						<?php } ?>

					<!-- EVENT navbar_header_profile_list_before -->

						<li>
							<a href="<?php echo isset($this->vars['U_PROFILE']) ? $this->vars['U_PROFILE'] : $this->lang('U_PROFILE'); ?>" title="<?php echo isset($this->vars['L_PROFILE']) ? $this->vars['L_PROFILE'] : $this->lang('L_PROFILE'); ?>" role="menuitem">
								<i class="icon fa-sliders fa-fw" aria-hidden="true"></i><span><?php echo isset($this->vars['L_PROFILE']) ? $this->vars['L_PROFILE'] : $this->lang('L_PROFILE'); ?></span>
							</a>
						</li>
						<li>
							<a href="<?php echo isset($this->vars['U_USER_PROFILE']) ? $this->vars['U_USER_PROFILE'] : $this->lang('U_USER_PROFILE'); ?>" title="<?php echo isset($this->vars['L_READ_PROFILE']) ? $this->vars['L_READ_PROFILE'] : $this->lang('L_READ_PROFILE'); ?>" role="menuitem">
								<i class="icon fa-user fa-fw" aria-hidden="true"></i><span><?php echo isset($this->vars['L_READ_PROFILE']) ? $this->vars['L_READ_PROFILE'] : $this->lang('L_READ_PROFILE'); ?></span>
							</a>
						</li>

						<!-- EVENT navbar_header_profile_list_after -->

						<li class="separator"></li>
						<li>
							<a href="<?php echo isset($this->vars['U_LOGIN_LOGOUT']) ? $this->vars['U_LOGIN_LOGOUT'] : $this->lang('U_LOGIN_LOGOUT'); ?>" title="<?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?>" accesskey="x" role="menuitem">
								<i class="icon fa-power-off fa-fw" aria-hidden="true"></i><span><?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<!-- EVENT navbar_header_username_append -->
		</div>

	<?php } else { ?>
		<div class="rightside" data-skip-responsive="true">
			<a href="<?php echo isset($this->vars['U_LOGIN_LOGOUT']) ? $this->vars['U_LOGIN_LOGOUT'] : $this->lang('U_LOGIN_LOGOUT'); ?>" title="<?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?>" accesskey="x" role="menuitem">
				<i class="icon fa-power-off fa-fw" aria-hidden="true"></i><span><?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?></span>
			</a>
		</div>
		<?php if ($this->vars['S_REGISTER_ENABLED'] && ! ( $this->vars['S_SHOW_COPPA'] || $this->vars['S_REGISTRATION'] )) {  ?>
			<div class="rightside" data-skip-responsive="true">
				<a href="<?php echo isset($this->vars['U_REGISTER']) ? $this->vars['U_REGISTER'] : $this->lang('U_REGISTER'); ?>" role="menuitem">
					<i class="icon fa-pencil-square-o  fa-fw" aria-hidden="true"></i><span><?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?></span>
				</a>
			</div>
		<?php } ?>
		<!-- EVENT navbar_header_logged_out_content -->
						</td>		
	<?php } ?>							
					</tr>
					</ul>
			</div>					
					</thead>
		</div>					
					<tbody>				
					<tr class="bg2">
						<td data-last-responsive="true" class="table1 genmed" height="15" align="center" valign="top">
							<span class="mainmenu">
								<a href="#" title="<?php echo isset($this->vars['L_QUICK_LINKS']) ? $this->vars['L_QUICK_LINKS'] : $this->lang('L_QUICK_LINKS'); ?>" class="mainmenu">
								<span><?php echo isset($this->vars['L_QUICK_LINKS']) ? $this->vars['L_QUICK_LINKS'] : $this->lang('L_QUICK_LINKS'); ?> </span>
								</a>
							</span>
						</td>					
						
						<td data-last-responsive="true" class="table1 forumtitle responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">
								<a href="<?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?>" title="<?php echo isset($this->vars['L_INDEX']) ? $this->vars['L_INDEX'] : $this->lang('L_INDEX'); ?>" class="mainmenu">
								<span><?php echo isset($this->vars['L_FORUM']) ? $this->vars['L_FORUM'] : $this->lang('L_FORUM'); ?> </span>
								</a>
							</span>
						</td>
						
						<td data-last-responsive="true" class="table1 forumtitle" height="15" align="center" valign="top">
							<span class="mainmenu">
								<a href="<?php echo isset($this->vars['U_FAQ']) ? $this->vars['U_FAQ'] : $this->lang('U_FAQ'); ?>" title="<?php echo isset($this->vars['L_FAQ']) ? $this->vars['L_FAQ'] : $this->lang('L_FAQ'); ?>" class="mainmenu">
								<span><?php echo isset($this->vars['L_FAQ']) ? $this->vars['L_FAQ'] : $this->lang('L_FAQ'); ?> </span>
								</a>
							</span>
						</td>
						
						<?php if ($this->vars['U_ACP']) {  ?>
						<td data-last-responsive="true" class="table1 forumtitle responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">							
								<a href="<?php echo isset($this->vars['U_ACP']) ? $this->vars['U_ACP'] : $this->lang('U_ACP'); ?>" title="<?php echo isset($this->vars['L_ACP']) ? $this->vars['L_ACP'] : $this->lang('L_ACP'); ?>" role="menuitem">
								<span><?php echo isset($this->vars['L_ACP_SHORT']) ? $this->vars['L_ACP_SHORT'] : $this->lang('L_ACP_SHORT'); ?> </span>
								</a>
							</span>						
						</td>
						<?php } ?>
						<?php if ($this->vars['U_MCP']) {  ?>
						<td data-last-responsive="true" class="table1 forumtitle responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">							
								<a href="<?php echo isset($this->vars['U_MCP']) ? $this->vars['U_MCP'] : $this->lang('U_MCP'); ?>" title="<?php echo isset($this->vars['L_MCP']) ? $this->vars['L_MCP'] : $this->lang('L_MCP'); ?>" role="menuitem">
								<span><?php echo isset($this->vars['L_MCP_SHORT']) ? $this->vars['L_MCP_SHORT'] : $this->lang('L_MCP_SHORT'); ?> </span>
								</a>
							</span>						
						</td>
						<?php } ?>						
						
						<?php if ($this->vars['S_DISPLAY_SEARCH']) {  ?>						
						<td data-last-responsive="true" class="forumtitle responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">
								<a href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" title="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>" class="mainmenu">
								<span><?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?> </span>
								</a>
							</span>
						</td>
						<?php } ?>
						
						<?php if (! $this->vars['S_IS_BOT']) {  ?>
						
						<?php if ($this->vars['S_DISPLAY_MEMBERLIST']) {  ?>
						<td data-last-responsive="true" class="forumtitle responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">
								<a href="<?php echo isset($this->vars['U_MEMBERLIST']) ? $this->vars['U_MEMBERLIST'] : $this->lang('U_MEMBERLIST'); ?>" title="<?php echo isset($this->vars['L_MEMBERLIST']) ? $this->vars['L_MEMBERLIST'] : $this->lang('L_MEMBERLIST'); ?>" class="mainmenu">
								<span><?php echo isset($this->vars['L_MEMBERLIST']) ? $this->vars['L_MEMBERLIST'] : $this->lang('L_MEMBERLIST'); ?> </span>
								</a>
							</span>
						</td>
						<?php } ?>
						
						<?php if ($this->vars['S_USER_LOGGED_IN']) {  ?>
						<td data-last-responsive="true" class="forumtitle genmed" height="15" align="center" valign="top">
							<span class="mainmenu">
								<a href="<?php echo isset($this->vars['U_PROFILE']) ? $this->vars['U_PROFILE'] : $this->lang('U_PROFILE'); ?>" title="<?php echo isset($this->vars['L_PROFILE']) ? $this->vars['L_PROFILE'] : $this->lang('L_PROFILE'); ?>" class="mainmenu">
								<span><?php echo isset($this->vars['L_UCP']) ? $this->vars['L_UCP'] : $this->lang('L_UCP'); ?> </span>
								</a>
							</span>
						</td>
						<?php } ?>	
						
						<?php } ?>						
						
						<?php if ($this->vars['S_DISPLAY_PM']) {  ?>
						<td data-last-responsive="true"class="forumtitle genmed" height="15" align="center" valign="top">
							<span class="mainmenu">
								<a href="<?php echo isset($this->vars['U_PRIVATEMSGS']) ? $this->vars['U_PRIVATEMSGS'] : $this->lang('U_PRIVATEMSGS'); ?>" title="<?php echo isset($this->vars['L_PRIVATE_MESSAGES']) ? $this->vars['L_PRIVATE_MESSAGES'] : $this->lang('L_PRIVATE_MESSAGES'); ?>" class="mainmenu">
									<span><?php echo isset($this->vars['L_PRIVATE_MESSAGES']) ? $this->vars['L_PRIVATE_MESSAGES'] : $this->lang('L_PRIVATE_MESSAGES'); ?> </span>
									<strong class="badge<?php if (! $this->vars['PRIVATE_MESSAGE_COUNT']) {  ?> hidden<?php } ?>">
									<?php echo isset($this->vars['PRIVATE_MESSAGE_COUNT']) ? $this->vars['PRIVATE_MESSAGE_COUNT'] : $this->lang('PRIVATE_MESSAGE_COUNT'); ?>
									</strong>
									<?php if ($this->vars['PRIVATE_MESSAGE_INFO_UNREAD']) {  ?>, <?php echo isset($this->vars['PRIVATE_MESSAGE_INFO_UNREAD']) ? $this->vars['PRIVATE_MESSAGE_INFO_UNREAD'] : $this->lang('PRIVATE_MESSAGE_INFO_UNREAD'); ?><?php } ?>
								</a>
							</span>
						</td>
						<?php } ?>
						
						<?php if (! $this->vars['S_IS_BOT']) {  ?>						
						<td data-last-responsive="true" class="forumtitle genmed responsive-hide" height="15" align="center" valign="top">
							<span class="mainmenu">
								<a href="<?php echo isset($this->vars['U_LOGIN_LOGOUT']) ? $this->vars['U_LOGIN_LOGOUT'] : $this->lang('U_LOGIN_LOGOUT'); ?>" title="<?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?>" class="mainmenu">
								<span><?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?>&nbsp; </span>
								</a>
							</span>
						</td>
						<?php } ?>
						
						<?php if ($this->vars['S_USER_LOGGED_OUT']) {  ?>
						<td data-last-responsive="true" class="forumtitle genmed responsive-hide" height="15" align="center" valign="top">
						<span class="mainmenu">						
							<a href="<?php echo isset($this->vars['U_REGISTER']) ? $this->vars['U_REGISTER'] : $this->lang('U_REGISTER'); ?>" title="<?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?>" class="mainmenu">
							<span><?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?> </span>
							</a>
						</span>
						</td>
						<?php } ?>
	
						<?php if ($this->vars['S_REGISTERED_USER']) {  ?>
						<td data-last-responsive="true" class="forumtitle genmed" height="15" align="center" valign="top">	

						<?php if ($this->vars['S_NOTIFICATIONS_DISPLAY']) {  ?>
							<div class="dropdown-container dropdown-<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?> rightside" data-skip-responsive="true">
								<span class="mainmenu">
								<a href="<?php echo isset($this->vars['U_VIEW_ALL_NOTIFICATIONS']) ? $this->vars['U_VIEW_ALL_NOTIFICATIONS'] : $this->lang('U_VIEW_ALL_NOTIFICATIONS'); ?>" id="notification_list_button" class="dropdown-trigger">
									<i class="icon fa-bell fa-fw" aria-hidden="true"></i>
									<span><?php echo isset($this->vars['L_NOTIFICATIONS']) ? $this->vars['L_NOTIFICATIONS'] : $this->lang('L_NOTIFICATIONS'); ?> </span>
									<strong class="badge<?php if (! $this->vars['NOTIFICATIONS_COUNT']) {  ?> hidden<?php } ?>">
									<?php echo isset($this->vars['NOTIFICATIONS_COUNT']) ? $this->vars['NOTIFICATIONS_COUNT'] : $this->lang('NOTIFICATIONS_COUNT'); ?>
									</strong>
								</a>
								</span>
								<?php  $this->set_filename('xs_include_395fcba1228560f41d20987e23e01b10', 'notification_dropdown.html', true);  $this->pparse('xs_include_395fcba1228560f41d20987e23e01b10');  ?>
							</div>
						<?php } ?>
						<!-- EVENT navbar_header_user_profile_append -->						
						
						</td>		
						<?php } ?>						

						</tr>
					<tr class="bg2"> 					
						<td data-last-responsive="true" class="forumtitle genmed" align="<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>">					
						<?php if ($this->vars['S_BOARD_DISABLED'] && $this->vars['S_USER_LOGGED_IN']) {  ?> &nbsp;<span style="color: red;"><?php echo isset($this->vars['L_BOARD_DISABLED']) ? $this->vars['L_BOARD_DISABLED'] : $this->lang('L_BOARD_DISABLED'); ?></span><?php } ?>							
						</td>	
					</tr>					
					</tbody>
					
				</table>
			<!-- EVENT overall_header_navigation_append -->					
				
		</td>	
	</tr>
	
	</div>	
	</tbody>
	<!-- EVENT overall_header_navbar_after -->	
</table>								

	<div id="datebar">
		<table width="100--" cellspacing="0">
		<tr>
			<td class="gensmall"><?php if ($this->vars['S_USER_LOGGED_IN']) {  ?><?php echo isset($this->vars['LAST_VISIT_DATE']) ? $this->vars['LAST_VISIT_DATE'] : $this->lang('LAST_VISIT_DATE'); ?><?php } ?></td>
			<td class="gensmall" align="<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>"><?php echo isset($this->vars['CURRENT_TIME']) ? $this->vars['CURRENT_TIME'] : $this->lang('CURRENT_TIME'); ?><br /></td>
		</tr>
		</table>
	</div>

</div>

<!-- EVENT overall_header_body_content_before -->
<div id="wrapcentre wrap" class="wrap">

	<?php if ($this->vars['S_DISPLAY_SEARCH']) {  ?>
	<p class="searchbar">
		<span style="float: <?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?>;"><a href="<?php echo isset($this->vars['U_SEARCH_UNANSWERED']) ? $this->vars['U_SEARCH_UNANSWERED'] : $this->lang('U_SEARCH_UNANSWERED'); ?>"><?php echo isset($this->vars['L_SEARCH_UNANSWERED']) ? $this->vars['L_SEARCH_UNANSWERED'] : $this->lang('L_SEARCH_UNANSWERED'); ?></a> | <a href="<?php echo isset($this->vars['U_SEARCH_ACTIVE_TOPICS']) ? $this->vars['U_SEARCH_ACTIVE_TOPICS'] : $this->lang('U_SEARCH_ACTIVE_TOPICS'); ?>"><?php echo isset($this->vars['L_SEARCH_ACTIVE_TOPICS']) ? $this->vars['L_SEARCH_ACTIVE_TOPICS'] : $this->lang('L_SEARCH_ACTIVE_TOPICS'); ?></a></span>
		<?php if ($this->vars['S_USER_LOGGED_IN']) {  ?>
		<span style="float: <?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>;"><a href="<?php echo isset($this->vars['U_SEARCH_NEW']) ? $this->vars['U_SEARCH_NEW'] : $this->lang('U_SEARCH_NEW'); ?>"><?php echo isset($this->vars['L_SEARCH_NEW']) ? $this->vars['L_SEARCH_NEW'] : $this->lang('L_SEARCH_NEW'); ?></a> | <a href="<?php echo isset($this->vars['U_SEARCH_SELF']) ? $this->vars['U_SEARCH_SELF'] : $this->lang('U_SEARCH_SELF'); ?>"><?php echo isset($this->vars['L_SEARCH_SELF']) ? $this->vars['L_SEARCH_SELF'] : $this->lang('L_SEARCH_SELF'); ?></a></span>
		<?php } ?>
	</p>
	<?php } ?>

	<br style="clear: both;" />

	<?php  $this->set_filename('xs_include_8dc1799e47d705c059ca07222becefcc', 'breadcrumbs.html', true);  $this->pparse('xs_include_8dc1799e47d705c059ca07222becefcc');  ?>

	<!-- EVENT overall_header_page_body_before -->

	<a id="start_here" class="anchor"></a>
	<div id="page-body" class="page-body" role="main">
		<?php if ($this->vars['S_BOARD_DISABLED'] && $this->vars['S_USER_LOGGED_IN'] && ( $this->vars['U_MCP'] || $this->vars['U_ACP'] )) {  ?>
		<div id="information" class="rules">
			<div class="inner">
				<strong><?php echo isset($this->vars['L_INFORMATION']) ? $this->vars['L_INFORMATION'] : $this->lang('L_INFORMATION'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <?php echo isset($this->vars['L_BOARD_DISABLED']) ? $this->vars['L_BOARD_DISABLED'] : $this->lang('L_BOARD_DISABLED'); ?>
			</div>
		</div>
		<?php } ?>

		<!-- EVENT overall_header_content_before -->
