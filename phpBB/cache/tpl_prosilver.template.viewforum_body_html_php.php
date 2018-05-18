<?php

// eXtreme Styles mod cache. Generated on Thu, 17 May 2018 09:51:16 +0000 (time=1526550676)

?><!-- INCLUDEX overall_header.html -->
<!-- EVENT viewforum_forum_title_before -->
<h2 class="forum-title"><!-- EVENT viewforum_forum_name_prepend --><a href="<?php echo isset($this->vars['U_VIEW_FORUM']) ? $this->vars['U_VIEW_FORUM'] : $this->lang('U_VIEW_FORUM'); ?>"><?php echo isset($this->vars['FORUM_NAME']) ? $this->vars['FORUM_NAME'] : $this->lang('FORUM_NAME'); ?></a><!-- EVENT viewforum_forum_name_append --></h2>
<!-- EVENT viewforum_forum_title_after -->
<?php if ($this->vars['FORUM_DESC'] || $this->vars['MODERATORS'] || $this->vars['U_MCP']) {  ?>
<div>
	<!-- NOTE: remove the style="display: none" when you want to have the forum description on the forum body -->
	<?php if ($this->vars['FORUM_DESC']) {  ?><div style="display: none !important;"><?php echo isset($this->vars['FORUM_DESC']) ? $this->vars['FORUM_DESC'] : $this->lang('FORUM_DESC'); ?><br /></div><?php } ?>
	<?php if ($this->vars['MODERATORS']) {  ?><p><strong><?php if ($this->vars['S_SINGLE_MODERATOR']) {  ?><?php echo isset($this->vars['L_MODERATOR']) ? $this->vars['L_MODERATOR'] : $this->lang('L_MODERATOR'); ?><?php } else { ?><?php echo isset($this->vars['L_MODERATORS']) ? $this->vars['L_MODERATORS'] : $this->lang('L_MODERATORS'); ?><?php } ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <?php echo isset($this->vars['MODERATORS']) ? $this->vars['MODERATORS'] : $this->lang('MODERATORS'); ?></p><?php } ?>
</div>
<?php } ?>

<?php if ($this->vars['S_FORUM_RULES']) {  ?>
	<div class="rules<?php if ($this->vars['U_FORUM_RULES']) {  ?> rules-link<?php } ?>">
		<div class="inner">

		<?php if ($this->vars['U_FORUM_RULES']) {  ?>
			<a href="<?php echo isset($this->vars['U_FORUM_RULES']) ? $this->vars['U_FORUM_RULES'] : $this->lang('U_FORUM_RULES'); ?>"><?php echo isset($this->vars['L_FORUM_RULES']) ? $this->vars['L_FORUM_RULES'] : $this->lang('L_FORUM_RULES'); ?></a>
		<?php } else { ?>
			<strong><?php echo isset($this->vars['L_FORUM_RULES']) ? $this->vars['L_FORUM_RULES'] : $this->lang('L_FORUM_RULES'); ?></strong><br />
			<?php echo isset($this->vars['FORUM_RULES']) ? $this->vars['FORUM_RULES'] : $this->lang('FORUM_RULES'); ?>
		<?php } ?>

		</div>
	</div>
<?php } ?>

<?php if ($this->vars['S_HAS_SUBFORUM']) {  ?>
<?php if (! $this->vars['S_IS_BOT'] && $this->vars['U_MARK_FORUMS']) {  ?>
	<div class="action-bar compact">
		<a href="<?php echo isset($this->vars['U_MARK_FORUMS']) ? $this->vars['U_MARK_FORUMS'] : $this->lang('U_MARK_FORUMS'); ?>" class="mark-read rightside" data-ajax="mark_forums_read"><?php echo isset($this->vars['L_MARK_SUBFORUMS_READ']) ? $this->vars['L_MARK_SUBFORUMS_READ'] : $this->lang('L_MARK_SUBFORUMS_READ'); ?></a>
	</div>
<?php } ?>
	<?php  $this->set_filename('xs_include_b51e269216595a616b6e43b092f7ad27', 'forumlist_body.html', true);  $this->pparse('xs_include_b51e269216595a616b6e43b092f7ad27');  ?>
<?php } ?>

<?php if ($this->vars['S_DISPLAY_POST_INFO'] || $this->vars['PAGINATION'] || $this->vars['TOTAL_POSTS'] || $this->vars['TOTAL_TOPICS']) {  ?>
	<div class="action-bar top">

	<?php if (! $this->vars['S_IS_BOT'] && $this->vars['S_DISPLAY_POST_INFO']) {  ?>
		<div class="buttons">
			<!-- EVENT viewforum_buttons_top_before -->

			<a href="<?php echo isset($this->vars['U_POST_NEW_TOPIC']) ? $this->vars['U_POST_NEW_TOPIC'] : $this->lang('U_POST_NEW_TOPIC'); ?>" class="button icon-button <?php if ($this->vars['S_IS_LOCKED']) {  ?>locked-icon<?php } else { ?>post-icon<?php } ?>" title="<?php if ($this->vars['S_IS_LOCKED']) {  ?><?php echo isset($this->vars['L_FORUM_LOCKED']) ? $this->vars['L_FORUM_LOCKED'] : $this->lang('L_FORUM_LOCKED'); ?><?php } else { ?><?php echo isset($this->vars['L_POST_TOPIC']) ? $this->vars['L_POST_TOPIC'] : $this->lang('L_POST_TOPIC'); ?><?php } ?>">
				<?php if ($this->vars['S_IS_LOCKED']) {  ?><?php echo isset($this->vars['L_BUTTON_FORUM_LOCKED']) ? $this->vars['L_BUTTON_FORUM_LOCKED'] : $this->lang('L_BUTTON_FORUM_LOCKED'); ?><?php } else { ?><?php echo isset($this->vars['L_BUTTON_NEW_TOPIC']) ? $this->vars['L_BUTTON_NEW_TOPIC'] : $this->lang('L_BUTTON_NEW_TOPIC'); ?><?php } ?>
			</a>

			<!-- EVENT viewforum_buttons_top_after -->
		</div>
	<?php } ?>

	<?php if ($this->vars['S_DISPLAY_SEARCHBOX']) {  ?>
		<div class="search-box" role="search">
			<form method="get" id="forum-search" action="<?php echo isset($this->vars['S_SEARCHBOX_ACTION']) ? $this->vars['S_SEARCHBOX_ACTION'] : $this->lang('S_SEARCHBOX_ACTION'); ?>">
			<fieldset>
				<input class="inputbox search tiny" type="search" name="keywords" id="search_keywords" size="20" placeholder="<?php echo isset($this->vars['L_SEARCH_FORUM']) ? $this->vars['L_SEARCH_FORUM'] : $this->lang('L_SEARCH_FORUM'); ?>" />
				<button class="button icon-button search-icon" type="submit" title="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>"><?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?></button>
				<a href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" class="button icon-button search-adv-icon" title="<?php echo isset($this->vars['L_SEARCH_ADV']) ? $this->vars['L_SEARCH_ADV'] : $this->lang('L_SEARCH_ADV'); ?>"><?php echo isset($this->vars['L_SEARCH_ADV']) ? $this->vars['L_SEARCH_ADV'] : $this->lang('L_SEARCH_ADV'); ?></a>
				<?php echo isset($this->vars['S_SEARCH_LOCAL_HIDDEN_FIELDS']) ? $this->vars['S_SEARCH_LOCAL_HIDDEN_FIELDS'] : $this->lang('S_SEARCH_LOCAL_HIDDEN_FIELDS'); ?>
			</fieldset>
			</form>
		</div>
	<?php } ?>

	<div class="pagination">
		<?php if (! $this->vars['S_IS_BOT'] && $this->vars['U_MARK_TOPICS'] && $this->vars['S_HAS_SUBFORUM']) {  ?><a href="<?php echo isset($this->vars['U_MARK_TOPICS']) ? $this->vars['U_MARK_TOPICS'] : $this->lang('U_MARK_TOPICS'); ?>" class="mark" accesskey="m" data-ajax="mark_topics_read"><?php echo isset($this->vars['L_MARK_TOPICS_READ']) ? $this->vars['L_MARK_TOPICS_READ'] : $this->lang('L_MARK_TOPICS_READ'); ?></a> &bull; <?php } ?>
		<?php echo isset($this->vars['TOTAL_TOPICS']) ? $this->vars['TOTAL_TOPICS'] : $this->lang('TOTAL_TOPICS'); ?>
		<?php if ($this->vars['PAGINATION']) {  ?>
			<?php  $this->set_filename('xs_include_aaec1812ce388c80dddfe4848de01726', 'pagination.html', true);  $this->pparse('xs_include_aaec1812ce388c80dddfe4848de01726');  ?>
		<?php } else { ?>
			&bull; <?php echo isset($this->vars['PAGE_NUMBER']) ? $this->vars['PAGE_NUMBER'] : $this->lang('PAGE_NUMBER'); ?>
		<?php } ?>
	</div>

	</div>
<?php } ?>

<?php if ($this->vars['S_NO_READ_ACCESS']) {  ?>

	<div class="panel">
		<div class="inner">
		<strong><?php echo isset($this->vars['L_NO_READ_ACCESS']) ? $this->vars['L_NO_READ_ACCESS'] : $this->lang('L_NO_READ_ACCESS'); ?></strong>
		</div>
	</div>

	<?php if (! $this->vars['S_USER_LOGGED_IN'] && ! $this->vars['S_IS_BOT']) {  ?>

		<form action="<?php echo isset($this->vars['S_LOGIN_ACTION']) ? $this->vars['S_LOGIN_ACTION'] : $this->lang('S_LOGIN_ACTION'); ?>" method="post">

		<div class="panel">
			<div class="inner">

			<div class="content">
				<h3><a href="<?php echo isset($this->vars['U_LOGIN_LOGOUT']) ? $this->vars['U_LOGIN_LOGOUT'] : $this->lang('U_LOGIN_LOGOUT'); ?>"><?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?></a><?php if ($this->vars['S_REGISTER_ENABLED']) {  ?>&nbsp; &bull; &nbsp;<a href="<?php echo isset($this->vars['U_REGISTER']) ? $this->vars['U_REGISTER'] : $this->lang('U_REGISTER'); ?>"><?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?></a><?php } ?></h3>

				<fieldset class="fields1">
				<dl>
					<dt><label for="username"><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
					<dd><input type="text" tabindex="1" name="username" id="username" size="25" value="<?php echo isset($this->vars['USERNAME']) ? $this->vars['USERNAME'] : $this->lang('USERNAME'); ?>" class="inputbox autowidth" /></dd>
				</dl>
				<dl>
					<dt><label for="password"><?php echo isset($this->vars['L_PASSWORD']) ? $this->vars['L_PASSWORD'] : $this->lang('L_PASSWORD'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
					<dd><input type="password" tabindex="2" id="password" name="password" size="25" class="inputbox autowidth" autocomplete="off" /></dd>
					<?php if ($this->vars['S_AUTOLOGIN_ENABLED']) {  ?><dd><label for="autologin"><input type="checkbox" name="autologin" id="autologin" tabindex="3" /> <?php echo isset($this->vars['L_LOG_ME_IN']) ? $this->vars['L_LOG_ME_IN'] : $this->lang('L_LOG_ME_IN'); ?></label></dd><?php } ?>
					<dd><label for="viewonline"><input type="checkbox" name="viewonline" id="viewonline" tabindex="4" /> <?php echo isset($this->vars['L_HIDE_ME']) ? $this->vars['L_HIDE_ME'] : $this->lang('L_HIDE_ME'); ?></label></dd>
				</dl>
				<dl>
					<dt>&nbsp;</dt>
					<dd><input type="submit" name="login" tabindex="5" value="<?php echo isset($this->vars['L_LOGIN']) ? $this->vars['L_LOGIN'] : $this->lang('L_LOGIN'); ?>" class="button1" /></dd>
				</dl>
				<?php echo isset($this->vars['S_LOGIN_REDIRECT']) ? $this->vars['S_LOGIN_REDIRECT'] : $this->lang('S_LOGIN_REDIRECT'); ?>
				</fieldset>
			</div>

			</div>
		</div>

		</form>

	<?php } ?>

<?php } ?>

<!-- EVENT viewforum_body_topic_row_before -->

<?php

$topicrow_count = ( isset($this->_tpldata['topicrow.']) ) ?  sizeof($this->_tpldata['topicrow.']) : 0;
for ($topicrow_i = 0; $topicrow_i < $topicrow_count; $topicrow_i++)
{
 $topicrow_item = &$this->_tpldata['topicrow.'][$topicrow_i];
 $topicrow_item['S_ROW_COUNT'] = $topicrow_i;
 $topicrow_item['S_NUM_ROWS'] = $topicrow_count;

?>

	<?php if (! $topicrow_item['S_TOPIC_TYPE_SWITCH'] && ! $topicrow_item['S_FIRST_ROW']) {  ?>
		</ul>
		</div>
	</div>
	<?php } ?>

	<?php if ($topicrow_item['S_FIRST_ROW'] || ! $topicrow_item['S_TOPIC_TYPE_SWITCH']) {  ?>
		<div class="forumbg<?php if ($topicrow_item['S_TOPIC_TYPE_SWITCH'] && ( $topicrow_item['S_POST_ANNOUNCE'] || $topicrow_item['S_POST_GLOBAL'] )) {  ?> announcement<?php } ?>">
		<div class="inner">
		<ul class="topiclist">
			<li class="header">
				<dl class="icon">
					<dt<?php if ($this->vars['S_DISPLAY_ACTIVE']) {  ?> id="active_topics"<?php } ?>><div class="list-inner"><?php if ($this->vars['S_DISPLAY_ACTIVE']) {  ?><?php echo isset($this->vars['L_ACTIVE_TOPICS']) ? $this->vars['L_ACTIVE_TOPICS'] : $this->lang('L_ACTIVE_TOPICS'); ?><?php } elseif ($topicrow_item['S_TOPIC_TYPE_SWITCH'] && ( $topicrow_item['S_POST_ANNOUNCE'] || $topicrow_item['S_POST_GLOBAL'] )) {  ?><?php echo isset($this->vars['L_ANNOUNCEMENTS']) ? $this->vars['L_ANNOUNCEMENTS'] : $this->lang('L_ANNOUNCEMENTS'); ?><?php } else { ?><?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?><?php } ?></div></dt>
					<dd class="posts"><?php echo isset($this->vars['L_REPLIES']) ? $this->vars['L_REPLIES'] : $this->lang('L_REPLIES'); ?></dd>
					<dd class="views"><?php echo isset($this->vars['L_VIEWS']) ? $this->vars['L_VIEWS'] : $this->lang('L_VIEWS'); ?></dd>
					<dd class="lastpost"><span><?php echo isset($this->vars['L_LAST_POST']) ? $this->vars['L_LAST_POST'] : $this->lang('L_LAST_POST'); ?></span></dd>
				</dl>
			</li>
		</ul>
		<ul class="topiclist topics">
	<?php } ?>

		<!-- EVENT viewforum_body_topicrow_row_before -->
		<li class="row<?php if (!($topicrow_item['S_ROW_COUNT'] % 2)) {  ?> bg1<?php } else { ?> bg2<?php } ?><?php if ($topicrow_item['S_POST_GLOBAL']) {  ?> global-announce<?php } ?><?php if ($topicrow_item['S_POST_ANNOUNCE']) {  ?> announce<?php } ?><?php if ($topicrow_item['S_POST_STICKY']) {  ?> sticky<?php } ?><?php if ($topicrow_item['S_TOPIC_REPORTED']) {  ?> reported<?php } ?>">
			<!-- EVENT viewforum_body_topic_row_prepend -->
			<dl class="icon <?php echo isset($topicrow_item['TOPIC_IMG_STYLE']) ? $topicrow_item['TOPIC_IMG_STYLE'] : ''; ?>">
				<dt<?php if ($topicrow_item['TOPIC_ICON_IMG'] && $this->vars['S_TOPIC_ICONS']) {  ?> style="background-image: url(<?php echo isset($this->vars['T_ICONS_PATH']) ? $this->vars['T_ICONS_PATH'] : $this->lang('T_ICONS_PATH'); ?><?php echo isset($topicrow_item['TOPIC_ICON_IMG']) ? $topicrow_item['TOPIC_ICON_IMG'] : ''; ?>); background-repeat: no-repeat;"<?php } ?> title="<?php echo isset($topicrow_item['TOPIC_FOLDER_IMG_ALT']) ? $topicrow_item['TOPIC_FOLDER_IMG_ALT'] : ''; ?>">
					<?php if ($topicrow_item['S_UNREAD_TOPIC'] && ! $this->vars['S_IS_BOT']) {  ?><a href="<?php echo isset($topicrow_item['U_NEWEST_POST']) ? $topicrow_item['U_NEWEST_POST'] : ''; ?>" class="icon-link"></a><?php } ?>
					<div class="list-inner">
						<!-- EVENT topiclist_row_prepend -->
						<?php if ($topicrow_item['S_UNREAD_TOPIC'] && ! $this->vars['S_IS_BOT']) {  ?><a href="<?php echo isset($topicrow_item['U_NEWEST_POST']) ? $topicrow_item['U_NEWEST_POST'] : ''; ?>"><?php echo isset($this->vars['NEWEST_POST_IMG']) ? $this->vars['NEWEST_POST_IMG'] : $this->lang('NEWEST_POST_IMG'); ?></a> <?php } ?><a href="<?php echo isset($topicrow_item['U_VIEW_TOPIC']) ? $topicrow_item['U_VIEW_TOPIC'] : ''; ?>" class="topictitle"><?php echo isset($topicrow_item['TOPIC_TITLE']) ? $topicrow_item['TOPIC_TITLE'] : ''; ?></a>
						<?php if ($topicrow_item['S_TOPIC_UNAPPROVED'] || $topicrow_item['S_POSTS_UNAPPROVED']) {  ?><a href="<?php echo isset($topicrow_item['U_MCP_QUEUE']) ? $topicrow_item['U_MCP_QUEUE'] : ''; ?>"><?php echo isset($topicrow_item['UNAPPROVED_IMG']) ? $topicrow_item['UNAPPROVED_IMG'] : ''; ?></a> <?php } ?>
						<?php if ($topicrow_item['S_TOPIC_DELETED']) {  ?><a href="<?php echo isset($topicrow_item['U_MCP_QUEUE']) ? $topicrow_item['U_MCP_QUEUE'] : ''; ?>"><?php echo isset($this->vars['DELETED_IMG']) ? $this->vars['DELETED_IMG'] : $this->lang('DELETED_IMG'); ?></a> <?php } ?>
						<?php if ($topicrow_item['S_TOPIC_REPORTED']) {  ?><a href="<?php echo isset($topicrow_item['U_MCP_REPORT']) ? $topicrow_item['U_MCP_REPORT'] : ''; ?>"><?php echo isset($this->vars['REPORTED_IMG']) ? $this->vars['REPORTED_IMG'] : $this->lang('REPORTED_IMG'); ?></a><?php } ?><br />
						<!-- EVENT topiclist_row_topic_title_after -->
						<?php if (! $this->vars['S_IS_BOT']) {  ?>
						<div class="responsive-show" style="display: none;">
							<?php echo isset($this->vars['L_LAST_POST']) ? $this->vars['L_LAST_POST'] : $this->lang('L_LAST_POST'); ?> <?php echo isset($this->vars['L_POST_BY_AUTHOR']) ? $this->vars['L_POST_BY_AUTHOR'] : $this->lang('L_POST_BY_AUTHOR'); ?> <?php echo isset($topicrow_item['LAST_POST_AUTHOR_FULL']) ? $topicrow_item['LAST_POST_AUTHOR_FULL'] : ''; ?> &laquo; <a href="<?php echo isset($topicrow_item['U_LAST_POST']) ? $topicrow_item['U_LAST_POST'] : ''; ?>" title="<?php echo isset($this->vars['L_GOTO_LAST_POST']) ? $this->vars['L_GOTO_LAST_POST'] : $this->lang('L_GOTO_LAST_POST'); ?>"><?php echo isset($topicrow_item['LAST_POST_TIME']) ? $topicrow_item['LAST_POST_TIME'] : ''; ?></a>
							<?php if ($topicrow_item['S_POST_GLOBAL'] && $this->vars['FORUM_ID'] != $topicrow_item['FORUM_ID']) {  ?><br /><?php echo isset($this->vars['L_POSTED']) ? $this->vars['L_POSTED'] : $this->lang('L_POSTED'); ?> <?php echo isset($this->vars['L_IN']) ? $this->vars['L_IN'] : $this->lang('L_IN'); ?> <a href="<?php echo isset($topicrow_item['U_VIEW_FORUM']) ? $topicrow_item['U_VIEW_FORUM'] : ''; ?>"><?php echo isset($topicrow_item['FORUM_NAME']) ? $topicrow_item['FORUM_NAME'] : ''; ?></a><?php } ?>
						</div>
						<?php if ($topicrow_item['REPLIES']) {  ?><span class="responsive-show left-box" style="display: none;"><?php echo isset($this->vars['L_REPLIES']) ? $this->vars['L_REPLIES'] : $this->lang('L_REPLIES'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <strong><?php echo isset($topicrow_item['REPLIES']) ? $topicrow_item['REPLIES'] : ''; ?></strong></span><?php } ?>
						<?php } ?>

						<?php if ($this->vars['S_HAS_SUBFORUMPAGINATION']) {  ?>
						<div class="pagination">
							<ul>
							<?php

$pagination_count = ( isset($topicrow_item['pagination.']) ) ? sizeof($topicrow_item['pagination.']) : 0;
for ($pagination_i = 0; $pagination_i < $pagination_count; $pagination_i++)
{
 $pagination_item = &$topicrow_item['pagination.'][$pagination_i];
 $pagination_item['S_ROW_COUNT'] = $pagination_i;
 $pagination_item['S_NUM_ROWS'] = $pagination_count;

?>
								<?php if (topicrowPAGINATION.S_IS_PREV) {  ?>
								<?php } elseif (topicrowPAGINATION.S_IS_CURRENT) {  ?><li class="active"><span><?php echo isset($topicrowPAGINATION_item['PAGE_NUMBER']) ? $topicrowPAGINATION_item['PAGE_NUMBER'] : ''; ?></span></li>
								<?php } elseif (topicrowPAGINATION.S_IS_ELLIPSIS) {  ?><li class="ellipsis"><span><?php echo isset($this->vars['L_ELLIPSIS']) ? $this->vars['L_ELLIPSIS'] : $this->lang('L_ELLIPSIS'); ?></span></li>
								<?php } elseif (topicrowPAGINATION.S_IS_NEXT) {  ?>
								<?php } else { ?><li><a href="<?php echo isset($topicrowPAGINATION_item['PAGE_URL']) ? $topicrowPAGINATION_item['PAGE_URL'] : ''; ?>"><?php echo isset($topicrowPAGINATION_item['PAGE_NUMBER']) ? $topicrowPAGINATION_item['PAGE_NUMBER'] : ''; ?></a></li>
								<?php } ?>
							<?php

} // END pagination

if(isset($pagination_item)) { unset($pagination_item); } 

?>
							</ul>
						</div>
						<?php } ?>

						<div class="responsive-hide">
							<?php if ($topicrow_item['S_HAS_POLL']) {  ?><?php echo isset($this->vars['POLL_IMG']) ? $this->vars['POLL_IMG'] : $this->lang('POLL_IMG'); ?> <?php } ?>
							<?php if ($topicrow_item['ATTACH_ICON_IMG']) {  ?><?php echo isset($topicrow_item['ATTACH_ICON_IMG']) ? $topicrow_item['ATTACH_ICON_IMG'] : ''; ?> <?php } ?>
							<?php echo isset($this->vars['L_POST_BY_AUTHOR']) ? $this->vars['L_POST_BY_AUTHOR'] : $this->lang('L_POST_BY_AUTHOR'); ?> <?php echo isset($topicrow_item['TOPIC_AUTHOR_FULL']) ? $topicrow_item['TOPIC_AUTHOR_FULL'] : ''; ?> &raquo; <?php echo isset($topicrow_item['FIRST_POST_TIME']) ? $topicrow_item['FIRST_POST_TIME'] : ''; ?>
							<?php if ($topicrow_item['S_POST_GLOBAL'] && $this->vars['FORUM_ID'] != $topicrow_item['FORUM_ID']) {  ?> &raquo; <?php echo isset($this->vars['L_IN']) ? $this->vars['L_IN'] : $this->lang('L_IN'); ?> <a href="<?php echo isset($topicrow_item['U_VIEW_FORUM']) ? $topicrow_item['U_VIEW_FORUM'] : ''; ?>"><?php echo isset($topicrow_item['FORUM_NAME']) ? $topicrow_item['FORUM_NAME'] : ''; ?></a><?php } ?>
						</div>

						<!-- EVENT topiclist_row_append -->
					</div>
				</dt>
				<dd class="posts"><?php echo isset($topicrow_item['REPLIES']) ? $topicrow_item['REPLIES'] : ''; ?> <dfn><?php echo isset($this->vars['L_REPLIES']) ? $this->vars['L_REPLIES'] : $this->lang('L_REPLIES'); ?></dfn></dd>
				<dd class="views"><?php echo isset($topicrow_item['VIEWS']) ? $topicrow_item['VIEWS'] : ''; ?> <dfn><?php echo isset($this->vars['L_VIEWS']) ? $this->vars['L_VIEWS'] : $this->lang('L_VIEWS'); ?></dfn></dd>
				<dd class="lastpost"><span><dfn><?php echo isset($this->vars['L_LAST_POST']) ? $this->vars['L_LAST_POST'] : $this->lang('L_LAST_POST'); ?> </dfn><?php echo isset($this->vars['L_POST_BY_AUTHOR']) ? $this->vars['L_POST_BY_AUTHOR'] : $this->lang('L_POST_BY_AUTHOR'); ?> <?php echo isset($topicrow_item['LAST_POST_AUTHOR_FULL']) ? $topicrow_item['LAST_POST_AUTHOR_FULL'] : ''; ?>
					<?php if (! $this->vars['S_IS_BOT']) {  ?><a href="<?php echo isset($topicrow_item['U_LAST_POST']) ? $topicrow_item['U_LAST_POST'] : ''; ?>" title="<?php echo isset($this->vars['L_GOTO_LAST_POST']) ? $this->vars['L_GOTO_LAST_POST'] : $this->lang('L_GOTO_LAST_POST'); ?>"><?php echo isset($this->vars['LAST_POST_IMG']) ? $this->vars['LAST_POST_IMG'] : $this->lang('LAST_POST_IMG'); ?></a> <?php } ?><br /><?php echo isset($topicrow_item['LAST_POST_TIME']) ? $topicrow_item['LAST_POST_TIME'] : ''; ?></span>
				</dd>
			</dl>
			<!-- EVENT viewforum_body_topic_row_append -->
		</li>
		<!-- EVENT viewforum_body_topic_row_after -->

	<?php if ($topicrow_item['S_LAST_ROW']) {  ?>
			</ul>
		</div>
	</div>
	<?php } ?>

<?php } if(!$topicrow_count) { ?>
	<?php if ($this->vars['S_IS_POSTABLE']) {  ?>
	<div class="panel">
		<div class="inner">
		<strong><?php echo isset($this->vars['L_NO_TOPICS']) ? $this->vars['L_NO_TOPICS'] : $this->lang('L_NO_TOPICS'); ?></strong>
		</div>
	</div>
	<?php } ?>
<?php

} // END topicrow

if(isset($topicrow_item)) { unset($topicrow_item); } 

?>

<?php if ($this->vars['S_SELECT_SORT_DAYS'] && ! $this->vars['S_DISPLAY_ACTIVE']) {  ?>
	<form method="post" action="<?php echo isset($this->vars['S_FORUM_ACTION']) ? $this->vars['S_FORUM_ACTION'] : $this->lang('S_FORUM_ACTION'); ?>">
		<fieldset class="display-options">
	<?php if (! $this->vars['S_IS_BOT']) {  ?>
			<label><?php echo isset($this->vars['L_DISPLAY_TOPICS']) ? $this->vars['L_DISPLAY_TOPICS'] : $this->lang('L_DISPLAY_TOPICS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <?php echo isset($this->vars['S_SELECT_SORT_DAYS']) ? $this->vars['S_SELECT_SORT_DAYS'] : $this->lang('S_SELECT_SORT_DAYS'); ?></label>
			<label><?php echo isset($this->vars['L_SORT_BY']) ? $this->vars['L_SORT_BY'] : $this->lang('L_SORT_BY'); ?> <?php echo isset($this->vars['S_SELECT_SORT_KEY']) ? $this->vars['S_SELECT_SORT_KEY'] : $this->lang('S_SELECT_SORT_KEY'); ?></label>
			<label><?php echo isset($this->vars['S_SELECT_SORT_DIR']) ? $this->vars['S_SELECT_SORT_DIR'] : $this->lang('S_SELECT_SORT_DIR'); ?></label>
			<input type="submit" name="sort" value="<?php echo isset($this->vars['L_GO']) ? $this->vars['L_GO'] : $this->lang('L_GO'); ?>" class="button2" />
	<?php } ?>
		</fieldset>
	</form>
	<hr />
<?php } ?>

<?php if ($this->vars['S_HAS_SUBFORUM'] && ! $this->vars['S_DISPLAY_ACTIVE']) {  ?>
	<div class="action-bar bottom">
		<?php if (! $this->vars['S_IS_BOT'] && $this->vars['S_DISPLAY_POST_INFO']) {  ?>
			<div class="buttons">
				<!-- EVENT viewforum_buttons_bottom_before -->

				<a href="<?php echo isset($this->vars['U_POST_NEW_TOPIC']) ? $this->vars['U_POST_NEW_TOPIC'] : $this->lang('U_POST_NEW_TOPIC'); ?>" class="button icon-button <?php if ($this->vars['S_IS_LOCKED']) {  ?>locked-icon<?php } else { ?>post-icon<?php } ?>" title="<?php if ($this->vars['S_IS_LOCKED']) {  ?><?php echo isset($this->vars['L_FORUM_LOCKED']) ? $this->vars['L_FORUM_LOCKED'] : $this->lang('L_FORUM_LOCKED'); ?><?php } else { ?><?php echo isset($this->vars['L_POST_TOPIC']) ? $this->vars['L_POST_TOPIC'] : $this->lang('L_POST_TOPIC'); ?><?php } ?>">
					<?php if ($this->vars['S_IS_LOCKED']) {  ?><?php echo isset($this->vars['L_BUTTON_FORUM_LOCKED']) ? $this->vars['L_BUTTON_FORUM_LOCKED'] : $this->lang('L_BUTTON_FORUM_LOCKED'); ?><?php } else { ?><?php echo isset($this->vars['L_BUTTON_NEW_TOPIC']) ? $this->vars['L_BUTTON_NEW_TOPIC'] : $this->lang('L_BUTTON_NEW_TOPIC'); ?><?php } ?>
				</a>

				<!-- EVENT viewforum_buttons_bottom_after -->
			</div>
		<?php } ?>

		<div class="pagination">
			<?php if (! $this->vars['S_IS_BOT'] && $this->vars['U_MARK_TOPICS'] && $this->vars['S_HAS_SUBFORUM']) {  ?><a href="<?php echo isset($this->vars['U_MARK_TOPICS']) ? $this->vars['U_MARK_TOPICS'] : $this->lang('U_MARK_TOPICS'); ?>" data-ajax="mark_topics_read"><?php echo isset($this->vars['L_MARK_TOPICS_READ']) ? $this->vars['L_MARK_TOPICS_READ'] : $this->lang('L_MARK_TOPICS_READ'); ?></a> &bull; <?php } ?>
			<?php echo isset($this->vars['TOTAL_TOPICS']) ? $this->vars['TOTAL_TOPICS'] : $this->lang('TOTAL_TOPICS'); ?>
			<?php if ($this->vars['PAGINATION']) {  ?>
				<?php  $this->set_filename('xs_include_918f5c2c0ca64e7f8d357541e7170387', 'pagination.html', true);  $this->pparse('xs_include_918f5c2c0ca64e7f8d357541e7170387');  ?>
			<?php } else { ?>
				 &bull; <?php echo isset($this->vars['PAGE_NUMBER']) ? $this->vars['PAGE_NUMBER'] : $this->lang('PAGE_NUMBER'); ?>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<?php  $this->set_filename('xs_include_f2b0887c64f1868efe0b86af85952a0c', 'jumpbox.html', true);  $this->pparse('xs_include_f2b0887c64f1868efe0b86af85952a0c');  ?>

<?php if ($this->vars['S_DISPLAY_ONLINE_LIST'] && $this->vars['U_VIEWONLINE']) {  ?>
	<div class="stat-block online-list">
		<h3><a href="<?php echo isset($this->vars['U_VIEWONLINE']) ? $this->vars['U_VIEWONLINE'] : $this->lang('U_VIEWONLINE'); ?>"><?php echo isset($this->vars['L_WHO_IS_ONLINE']) ? $this->vars['L_WHO_IS_ONLINE'] : $this->lang('L_WHO_IS_ONLINE'); ?></a></h3>
		<p><?php echo isset($this->vars['LOGGED_IN_USER_LIST']) ? $this->vars['LOGGED_IN_USER_LIST'] : $this->lang('LOGGED_IN_USER_LIST'); ?></p>
	</div>
<?php } ?>

<?php if ($this->vars['S_DISPLAY_POST_INFO']) {  ?>
	<div class="stat-block permissions">
		<h3><?php echo isset($this->vars['L_FORUM_PERMISSIONS']) ? $this->vars['L_FORUM_PERMISSIONS'] : $this->lang('L_FORUM_PERMISSIONS'); ?></h3>
		<p><?php

$rules_count = ( isset($this->_tpldata['rules.']) ) ?  sizeof($this->_tpldata['rules.']) : 0;
for ($rules_i = 0; $rules_i < $rules_count; $rules_i++)
{
 $rules_item = &$this->_tpldata['rules.'][$rules_i];
 $rules_item['S_ROW_COUNT'] = $rules_i;
 $rules_item['S_NUM_ROWS'] = $rules_count;

?><?php echo isset($rules_item['RULE']) ? $rules_item['RULE'] : ''; ?><br /><?php

} // END rules

if(isset($rules_item)) { unset($rules_item); } 

?></p>
	</div>
<?php } ?>

<!-- INCLUDEX overall_footer.html -->
