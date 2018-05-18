<?php

// eXtreme Styles mod cache. Generated on Thu, 17 May 2018 09:51:15 +0000 (time=1526550675)

?><!DOCTYPE html>
<html dir="<?php echo isset($this->vars['S_CONTENT_DIRECTION']) ? $this->vars['S_CONTENT_DIRECTION'] : $this->lang('S_CONTENT_DIRECTION'); ?>" lang="<?php echo isset($this->vars['S_USER_LANG']) ? $this->vars['S_USER_LANG'] : $this->lang('S_USER_LANG'); ?>">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php echo isset($this->vars['META']) ? $this->vars['META'] : $this->lang('META'); ?>
<title><?php if ($this->vars['UNREAD_NOTIFICATIONS_COUNT']) {  ?>(<?php echo isset($this->vars['UNREAD_NOTIFICATIONS_COUNT']) ? $this->vars['UNREAD_NOTIFICATIONS_COUNT'] : $this->lang('UNREAD_NOTIFICATIONS_COUNT'); ?>) <?php } ?><?php if (! $this->vars['S_VIEWTOPIC'] && ! $this->vars['S_VIEWFORUM']) {  ?><?php echo isset($this->vars['SITENAME']) ? $this->vars['SITENAME'] : $this->lang('SITENAME'); ?> - <?php } ?><?php if ($this->vars['S_IN_MCP']) {  ?><?php echo isset($this->vars['L_MCP']) ? $this->vars['L_MCP'] : $this->lang('L_MCP'); ?> - <?php } elseif ($this->vars['S_IN_UCP']) {  ?><?php echo isset($this->vars['L_UCP']) ? $this->vars['L_UCP'] : $this->lang('L_UCP'); ?> - <?php } ?><?php echo isset($this->vars['PAGE_TITLE']) ? $this->vars['PAGE_TITLE'] : $this->lang('PAGE_TITLE'); ?><?php if ($this->vars['S_VIEWTOPIC'] || $this->vars['S_VIEWFORUM']) {  ?> - <?php echo isset($this->vars['SITENAME']) ? $this->vars['SITENAME'] : $this->lang('SITENAME'); ?><?php } ?></title>

<?php if ($this->vars['S_ENABLE_FEEDS']) {  ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_OVERALL']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['SITENAME']) ? $this->vars['SITENAME'] : $this->lang('SITENAME'); ?>" href="<?php echo isset($this->vars['U_FEED']) ? $this->vars['U_FEED'] : $this->lang('U_FEED'); ?>"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_NEWS']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_FEED_NEWS']) ? $this->vars['L_FEED_NEWS'] : $this->lang('L_FEED_NEWS'); ?>" href="<?php echo isset($this->vars['U_FEED']) ? $this->vars['U_FEED'] : $this->lang('U_FEED'); ?>?mode=news"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_FORUMS']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_ALL_FORUMS']) ? $this->vars['L_ALL_FORUMS'] : $this->lang('L_ALL_FORUMS'); ?>" href="<?php echo isset($this->vars['U_FEED']) ? $this->vars['U_FEED'] : $this->lang('U_FEED'); ?>?mode=forums"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_TOPICS']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_FEED_TOPICS_NEW']) ? $this->vars['L_FEED_TOPICS_NEW'] : $this->lang('L_FEED_TOPICS_NEW'); ?>" href="<?php echo isset($this->vars['U_FEED']) ? $this->vars['U_FEED'] : $this->lang('U_FEED'); ?>?mode=topics"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_TOPICS_ACTIVE']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_FEED_TOPICS_ACTIVE']) ? $this->vars['L_FEED_TOPICS_ACTIVE'] : $this->lang('L_FEED_TOPICS_ACTIVE'); ?>" href="<?php echo isset($this->vars['U_FEED']) ? $this->vars['U_FEED'] : $this->lang('U_FEED'); ?>?mode=topics_active"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_FORUM'] && $this->vars['S_FORUM_ID']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_FORUM']) ? $this->vars['L_FORUM'] : $this->lang('L_FORUM'); ?> - <?php echo isset($this->vars['FORUM_NAME']) ? $this->vars['FORUM_NAME'] : $this->lang('FORUM_NAME'); ?>" href="<?php echo isset($this->vars['U_FEED']) ? $this->vars['U_FEED'] : $this->lang('U_FEED'); ?>?f=<?php echo isset($this->vars['S_FORUM_ID']) ? $this->vars['S_FORUM_ID'] : $this->lang('S_FORUM_ID'); ?>"><?php } ?>
	<?php if ($this->vars['S_ENABLE_FEEDS_TOPIC'] && $this->vars['S_TOPIC_ID']) {  ?><link rel="alternate" type="application/atom+xml" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($this->vars['L_TOPIC']) ? $this->vars['L_TOPIC'] : $this->lang('L_TOPIC'); ?> - <?php echo isset($this->vars['TOPIC_TITLE']) ? $this->vars['TOPIC_TITLE'] : $this->lang('TOPIC_TITLE'); ?>" href="<?php echo isset($this->vars['U_FEED']) ? $this->vars['U_FEED'] : $this->lang('U_FEED'); ?>?f=<?php echo isset($this->vars['S_FORUM_ID']) ? $this->vars['S_FORUM_ID'] : $this->lang('S_FORUM_ID'); ?>&amp;t=<?php echo isset($this->vars['S_TOPIC_ID']) ? $this->vars['S_TOPIC_ID'] : $this->lang('S_TOPIC_ID'); ?>"><?php } ?>
	<!-- EVENT overall_header_feeds -->
<?php } ?>

<?php if ($this->vars['U_CANONICAL']) {  ?>
	<link rel="canonical" href="<?php echo isset($this->vars['U_CANONICAL']) ? $this->vars['U_CANONICAL'] : $this->lang('U_CANONICAL'); ?>">
<?php } ?>

<!--
	phpBB style name: prosilver
	Based on style:   prosilver (this is the default phpBB3 style)
	Original author:  Tom Beddard ( http://www.subBlue.com/ )
	Modified by:
-->

<?php if ($this->vars['S_ALLOW_CDN']) {  ?>
<script>
	WebFontConfig = {
		google: {
			families: ['Open+Sans:600:cyrillic-ext,latin,greek-ext,greek,vietnamese,latin-ext,cyrillic']
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
<link href="<?php echo isset($this->vars['T_STYLESHEET_LINK']) ? $this->vars['T_STYLESHEET_LINK'] : $this->lang('T_STYLESHEET_LINK'); ?>" rel="stylesheet">
<link href="<?php echo isset($this->vars['T_STYLESHEET_LANG_LINK']) ? $this->vars['T_STYLESHEET_LANG_LINK'] : $this->lang('T_STYLESHEET_LANG_LINK'); ?>" rel="stylesheet">
<link href="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/responsive.css?assets_version=<?php echo isset($this->vars['T_ASSETS_VERSION']) ? $this->vars['T_ASSETS_VERSION'] : $this->lang('T_ASSETS_VERSION'); ?>" rel="stylesheet" media="all and (max-width: 700px)">

<?php if ($this->vars['S_CONTENT_DIRECTION'] == 'rtl') {  ?>
	<link href="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/bidi.css?assets_version=<?php echo isset($this->vars['T_ASSETS_VERSION']) ? $this->vars['T_ASSETS_VERSION'] : $this->lang('T_ASSETS_VERSION'); ?>" rel="stylesheet">
<?php } ?>

<?php if ($this->vars['S_PLUPLOAD']) {  ?>
	<link href="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/plupload.css?assets_version=<?php echo isset($this->vars['T_ASSETS_VERSION']) ? $this->vars['T_ASSETS_VERSION'] : $this->lang('T_ASSETS_VERSION'); ?>" rel="stylesheet">
<?php } ?>

<!--[if lte IE 9]>
	<link href="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/tweaks.css?assets_version=<?php echo isset($this->vars['T_ASSETS_VERSION']) ? $this->vars['T_ASSETS_VERSION'] : $this->lang('T_ASSETS_VERSION'); ?>" rel="stylesheet">
<![endif]-->

<!-- EVENT overall_header_head_append -->

<?php echo isset($this->_tpldata['DEFINE']['.']['STYLESHEETS']) ? $this->_tpldata['DEFINE']['.']['STYLESHEETS'] : ''; ?>

<!-- EVENT overall_header_stylesheets_after -->

</head>
<body id="phpbb" class="nojs notouch section-<?php echo isset($this->vars['SCRIPT_NAME']) ? $this->vars['SCRIPT_NAME'] : $this->lang('SCRIPT_NAME'); ?> <?php echo isset($this->vars['S_CONTENT_DIRECTION']) ? $this->vars['S_CONTENT_DIRECTION'] : $this->lang('S_CONTENT_DIRECTION'); ?> <?php echo isset($this->vars['BODY_CLASS']) ? $this->vars['BODY_CLASS'] : $this->lang('BODY_CLASS'); ?>">

<!-- EVENT overall_header_body_before -->

<div id="wrap">
	<a id="top" class="anchor" accesskey="t"></a>
	<div id="page-header">
		<div class="headerbar" role="banner">
		<!-- EVENT overall_header_headerbar_before -->
			<div class="inner">

			<div id="site-description">
				<a id="logo" class="logo" href="<?php if ($this->vars['U_SITE_HOME']) {  ?><?php echo isset($this->vars['U_SITE_HOME']) ? $this->vars['U_SITE_HOME'] : $this->lang('U_SITE_HOME'); ?><?php } else { ?><?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?><?php } ?>" title="<?php if ($this->vars['U_SITE_HOME']) {  ?><?php echo isset($this->vars['L_SITE_HOME']) ? $this->vars['L_SITE_HOME'] : $this->lang('L_SITE_HOME'); ?><?php } else { ?><?php echo isset($this->vars['L_INDEX']) ? $this->vars['L_INDEX'] : $this->lang('L_INDEX'); ?><?php } ?>"><?php echo isset($this->vars['SITE_LOGO_IMG']) ? $this->vars['SITE_LOGO_IMG'] : $this->lang('SITE_LOGO_IMG'); ?></a>
				<h1><?php echo isset($this->vars['SITENAME']) ? $this->vars['SITENAME'] : $this->lang('SITENAME'); ?></h1>
				<p><?php echo isset($this->vars['SITE_DESCRIPTION']) ? $this->vars['SITE_DESCRIPTION'] : $this->lang('SITE_DESCRIPTION'); ?></p>
				<p class="skiplink"><a href="#start_here"><?php echo isset($this->vars['L_SKIP']) ? $this->vars['L_SKIP'] : $this->lang('L_SKIP'); ?></a></p>
			</div>

			<!-- EVENT overall_header_searchbox_before -->
			<?php if ($this->vars['S_DISPLAY_SEARCH'] && ! $this->vars['S_IN_SEARCH']) {  ?>
			<div id="search-box" class="search-box search-header" role="search">
				<form action="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" method="get" id="search">
				<fieldset>
					<input name="keywords" id="keywords" type="search" maxlength="128" title="<?php echo isset($this->vars['L_SEARCH_KEYWORDS']) ? $this->vars['L_SEARCH_KEYWORDS'] : $this->lang('L_SEARCH_KEYWORDS'); ?>" class="inputbox search tiny" size="20" value="<?php echo isset($this->vars['SEARCH_WORDS']) ? $this->vars['SEARCH_WORDS'] : $this->lang('SEARCH_WORDS'); ?>" placeholder="<?php echo isset($this->vars['L_SEARCH_MINI']) ? $this->vars['L_SEARCH_MINI'] : $this->lang('L_SEARCH_MINI'); ?>" />
					<button class="button icon-button search-icon" type="submit" title="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>"><?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?></button>
					<a href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" class="button icon-button search-adv-icon" title="<?php echo isset($this->vars['L_SEARCH_ADV']) ? $this->vars['L_SEARCH_ADV'] : $this->lang('L_SEARCH_ADV'); ?>"><?php echo isset($this->vars['L_SEARCH_ADV']) ? $this->vars['L_SEARCH_ADV'] : $this->lang('L_SEARCH_ADV'); ?></a>
					<?php echo isset($this->vars['S_SEARCH_HIDDEN_FIELDS']) ? $this->vars['S_SEARCH_HIDDEN_FIELDS'] : $this->lang('S_SEARCH_HIDDEN_FIELDS'); ?>
				</fieldset>
				</form>
			</div>
			<?php } ?>
			<!-- EVENT overall_header_searchbox_after -->

			</div>
			<!-- EVENT overall_header_headerbar_after -->
		</div>
		<!-- EVENT overall_header_navbar_before -->
		<?php  $this->set_filename('xs_include_ab571ea425c8347d4504138d89362805', 'navbar_header.html', true);  $this->pparse('xs_include_ab571ea425c8347d4504138d89362805');  ?>
	</div>

	<!-- EVENT overall_header_page_body_before -->

	<a id="start_here" class="anchor"></a>
	<div id="page-body" role="main">
		<?php if ($this->vars['S_BOARD_DISABLED'] && $this->vars['S_USER_LOGGED_IN'] && ( $this->vars['U_MCP'] || $this->vars['U_ACP'] )) {  ?>
		<div id="information" class="rules">
			<div class="inner">
				<strong><?php echo isset($this->vars['L_INFORMATION']) ? $this->vars['L_INFORMATION'] : $this->lang('L_INFORMATION'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <?php echo isset($this->vars['L_BOARD_DISABLED']) ? $this->vars['L_BOARD_DISABLED'] : $this->lang('L_BOARD_DISABLED'); ?>
			</div>
		</div>
		<?php } ?>

		<!-- EVENT overall_header_content_before -->
