<?php

// eXtreme Styles mod cache. Generated on Tue, 07 Aug 2018 01:34:08 +0000 (time=1533605648)

?><!-- INCLUDEX overall_header.html -->

<?php if ($this->vars['S_FORUM_RULES']) {  ?>
	<div class="forumrules<?php if ($this->vars['U_FORUM_RULES']) {  ?> rules-link<?php } ?>">
		<?php if ($this->vars['U_FORUM_RULES']) {  ?>
			<h3><?php echo isset($this->vars['L_FORUM_RULES']) ? $this->vars['L_FORUM_RULES'] : $this->lang('L_FORUM_RULES'); ?></h3><br />
			<a href="<?php echo isset($this->vars['U_FORUM_RULES']) ? $this->vars['U_FORUM_RULES'] : $this->lang('U_FORUM_RULES'); ?>"><b><?php echo isset($this->vars['L_FORUM_RULES_LINK']) ? $this->vars['L_FORUM_RULES_LINK'] : $this->lang('L_FORUM_RULES_LINK'); ?></b></a>
		<?php } else { ?>
			<h3><?php echo isset($this->vars['L_FORUM_RULES']) ? $this->vars['L_FORUM_RULES'] : $this->lang('L_FORUM_RULES'); ?></h3><br />
			<?php echo isset($this->vars['FORUM_RULES']) ? $this->vars['FORUM_RULES'] : $this->lang('FORUM_RULES'); ?>
		<?php } ?>
	</div>

	<br clear="all" />
<?php } ?>

<?php if ($this->vars['S_DISPLAY_ACTIVE']) {  ?>
	<table class="table1 tablebg" width="100--" cellspacing="1">
	<thead>	
	<tr>
		<td class="cat" colspan="<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>7<?php } else { ?>6<?php } ?>"><span class="nav"><?php echo isset($this->vars['L_ACTIVE_TOPICS']) ? $this->vars['L_ACTIVE_TOPICS'] : $this->lang('L_ACTIVE_TOPICS'); ?></span></td>
	</tr>

	<tr>
		<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>
			<th colspan="3">&nbsp;<?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?>&nbsp;</th>
		<?php } else { ?>
			<th colspan="2">&nbsp;<?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?>&nbsp;</th>
		<?php } ?>
		<th>&nbsp;<?php echo isset($this->vars['L_AUTHOR']) ? $this->vars['L_AUTHOR'] : $this->lang('L_AUTHOR'); ?>&nbsp;</th>
		<th>&nbsp;<?php echo isset($this->vars['L_REPLIES']) ? $this->vars['L_REPLIES'] : $this->lang('L_REPLIES'); ?>&nbsp;</th>
		<th>&nbsp;<?php echo isset($this->vars['L_VIEWS']) ? $this->vars['L_VIEWS'] : $this->lang('L_VIEWS'); ?>&nbsp;</th>
		<th>&nbsp;<?php echo isset($this->vars['L_LAST_POST']) ? $this->vars['L_LAST_POST'] : $this->lang('L_LAST_POST'); ?>&nbsp;</th>
	</tr>
	</thead>
	<tbody>
	<!-- EVENT viewforum_body_topic_row_before -->

	<?php

$topicrow_count = ( isset($this->_tpldata['topicrow.']) ) ?  sizeof($this->_tpldata['topicrow.']) : 0;
for ($topicrow_i = 0; $topicrow_i < $topicrow_count; $topicrow_i++)
{
 $topicrow_item = &$this->_tpldata['topicrow.'][$topicrow_i];
 $topicrow_item['S_ROW_COUNT'] = $topicrow_i;
 $topicrow_item['S_NUM_ROWS'] = $topicrow_count;

?>

		<!-- EVENT viewforum_body_topicrow_row_before -->
		<tr>
			<!-- EVENT viewforum_body_topic_row_prepend -->
			<td class="row1" width="25" align="center"><?php echo isset($topicrow_item['TOPIC_FOLDER_IMG']) ? $topicrow_item['TOPIC_FOLDER_IMG'] : ''; ?></td>
			<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>
				<td class="row1" width="25" align="center"><?php if ($topicrow_item['TOPIC_ICON_IMG']) {  ?><img src="<?php echo isset($this->vars['T_ICONS_PATH']) ? $this->vars['T_ICONS_PATH'] : $this->lang('T_ICONS_PATH'); ?><?php echo isset($topicrow_item['TOPIC_ICON_IMG']) ? $topicrow_item['TOPIC_ICON_IMG'] : ''; ?>" width="<?php echo isset($topicrow_item['TOPIC_ICON_IMG_WIDTH']) ? $topicrow_item['TOPIC_ICON_IMG_WIDTH'] : ''; ?>" height="<?php echo isset($topicrow_item['TOPIC_ICON_IMG_HEIGHT']) ? $topicrow_item['TOPIC_ICON_IMG_HEIGHT'] : ''; ?>" alt="" title="" /><?php } ?></td>
			<?php } ?>
			<td class="row1">
				<!-- EVENT topiclist_row_prepend -->
				<?php if ($topicrow_item['S_UNREAD_TOPIC']) {  ?><a href="<?php echo isset($topicrow_item['U_NEWEST_POST']) ? $topicrow_item['U_NEWEST_POST'] : ''; ?>" class="imageset"><?php echo isset($this->vars['NEWEST_POST_IMG']) ? $this->vars['NEWEST_POST_IMG'] : $this->lang('NEWEST_POST_IMG'); ?></a><?php } ?>
				<?php echo isset($topicrow_item['ATTACH_ICON_IMG']) ? $topicrow_item['ATTACH_ICON_IMG'] : ''; ?> <?php if ($topicrow_item['S_HAS_POLL'] || $topicrow_item['S_TOPIC_MOVED']) {  ?><b><?php echo isset($topicrow_item['TOPIC_TYPE']) ? $topicrow_item['TOPIC_TYPE'] : ''; ?></b> <?php } ?><a title="<?php echo isset($this->vars['L_POSTED']) ? $this->vars['L_POSTED'] : $this->lang('L_POSTED'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <?php echo isset($topicrow_item['FIRST_POST_TIME']) ? $topicrow_item['FIRST_POST_TIME'] : ''; ?>" href="<?php echo isset($topicrow_item['U_VIEW_TOPIC']) ? $topicrow_item['U_VIEW_TOPIC'] : ''; ?>"class="topictitle"><?php echo isset($topicrow_item['TOPIC_TITLE']) ? $topicrow_item['TOPIC_TITLE'] : ''; ?></a>
				<?php if ($topicrow_item['S_TOPIC_UNAPPROVED'] || $topicrow_item['S_POSTS_UNAPPROVED']) {  ?>
					<a href="<?php echo isset($topicrow_item['U_MCP_QUEUE']) ? $topicrow_item['U_MCP_QUEUE'] : ''; ?>" class="imageset"><?php echo isset($topicrow_item['UNAPPROVED_IMG']) ? $topicrow_item['UNAPPROVED_IMG'] : ''; ?></a>&nbsp;
				<?php } ?>
				<?php if ($topicrow_item['S_TOPIC_DELETED']) {  ?>
					<a href="<?php echo isset($topicrow_item['U_MCP_QUEUE']) ? $topicrow_item['U_MCP_QUEUE'] : ''; ?>" class="imageset"><?php echo isset($this->vars['DELETED_IMG']) ? $this->vars['DELETED_IMG'] : $this->lang('DELETED_IMG'); ?></a>&nbsp;
				<?php } ?>
				<?php if ($topicrow_item['S_TOPIC_REPORTED']) {  ?>
					<a href="<?php echo isset($topicrow_item['U_MCP_REPORT']) ? $topicrow_item['U_MCP_REPORT'] : ''; ?>" class="imageset"><?php echo isset($this->vars['REPORTED_IMG']) ? $this->vars['REPORTED_IMG'] : $this->lang('REPORTED_IMG'); ?></a>&nbsp;
				<?php } ?>
				<!-- EVENT topiclist_row_topic_title_after -->
				<?php if ($this->vars['S_HAS_SUBFORUMPAGINATION']) {  ?>
					<p class="gensmall"> [ <?php echo isset($this->vars['GOTO_PAGE_IMG']) ? $this->vars['GOTO_PAGE_IMG'] : $this->lang('GOTO_PAGE_IMG'); ?><?php echo isset($this->vars['L_GOTO_PAGE']) ? $this->vars['L_GOTO_PAGE'] : $this->lang('L_GOTO_PAGE'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?>
					<?php

$pagination_count = ( isset($topicrow_item['pagination.']) ) ? sizeof($topicrow_item['pagination.']) : 0;
for ($pagination_i = 0; $pagination_i < $pagination_count; $pagination_i++)
{
 $pagination_item = &$topicrow_item['pagination.'][$pagination_i];
 $pagination_item['S_ROW_COUNT'] = $pagination_i;
 $pagination_item['S_NUM_ROWS'] = $pagination_count;

?>
						<?php if (topicrowPAGINATION.S_IS_PREV) {  ?>
						<?php } elseif (topicrowPAGINATION.S_IS_CURRENT) {  ?><strong><?php echo isset($topicrowPAGINATION_item['PAGE_NUMBER']) ? $topicrowPAGINATION_item['PAGE_NUMBER'] : ''; ?></strong>
						<?php } elseif (topicrowPAGINATION.S_IS_ELLIPSIS) {  ?> <?php echo isset($this->vars['L_ELLIPSIS']) ? $this->vars['L_ELLIPSIS'] : $this->lang('L_ELLIPSIS'); ?>
						<?php } elseif (topicrowPAGINATION.S_IS_NEXT) {  ?>
						<?php } else { ?><a href="<?php echo isset($topicrowPAGINATION_item['PAGE_URL']) ? $topicrowPAGINATION_item['PAGE_URL'] : ''; ?>"><?php echo isset($topicrowPAGINATION_item['PAGE_NUMBER']) ? $topicrowPAGINATION_item['PAGE_NUMBER'] : ''; ?></a>
						<?php } ?>
					<?php

} // END pagination

if(isset($pagination_item)) { unset($pagination_item); } 

?>
					] </p>
				<?php } ?>
				<!-- EVENT topiclist_row_append -->
			</td>
			<td class="row2" width="130" align="center"><p class="topicauthor"><?php echo isset($topicrow_item['TOPIC_AUTHOR_FULL']) ? $topicrow_item['TOPIC_AUTHOR_FULL'] : ''; ?></p></td>
			<td class="row1" width="50" align="center"><p class="topicdetails"><?php echo isset($topicrow_item['REPLIES']) ? $topicrow_item['REPLIES'] : ''; ?></p></td>
			<td class="row2" width="50" align="center"><p class="topicdetails"><?php echo isset($topicrow_item['VIEWS']) ? $topicrow_item['VIEWS'] : ''; ?></p></td>
			<td class="row1" width="140" align="center">
				<p class="topicdetails" style="white-space: nowrap;"><?php echo isset($topicrow_item['LAST_POST_TIME']) ? $topicrow_item['LAST_POST_TIME'] : ''; ?></p>
				<p class="topicdetails"><?php echo isset($topicrow_item['LAST_POST_AUTHOR_FULL']) ? $topicrow_item['LAST_POST_AUTHOR_FULL'] : ''; ?>
					<?php if (! $this->vars['S_IS_BOT']) {  ?><a href="<?php echo isset($topicrow_item['U_LAST_POST']) ? $topicrow_item['U_LAST_POST'] : ''; ?>" class="imageset"><?php echo isset($this->vars['LAST_POST_IMG']) ? $this->vars['LAST_POST_IMG'] : $this->lang('LAST_POST_IMG'); ?></a><?php } ?>
				</p>
			</td>
			<!-- EVENT viewforum_body_topic_row_append -->
		</tr>
		<!-- EVENT viewforum_body_topic_row_after -->

	<?php } if(!$topicrow_count) { ?>

		<tr>
			<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>
				<td class="row1" colspan="7" height="30" align="center" valign="middle"><span class="gen"><?php if (! $this->vars['S_SORT_DAYS']) {  ?><?php echo isset($this->vars['L_NO_TOPICS']) ? $this->vars['L_NO_TOPICS'] : $this->lang('L_NO_TOPICS'); ?><?php } else { ?><?php echo isset($this->vars['L_NO_TOPICS_TIME_FRAME']) ? $this->vars['L_NO_TOPICS_TIME_FRAME'] : $this->lang('L_NO_TOPICS_TIME_FRAME'); ?><?php } ?></span></td>
			<?php } else { ?>
				<td class="row1" colspan="6" height="30" align="center" valign="middle"><span class="gen"><?php if (! $this->vars['S_SORT_DAYS']) {  ?><?php echo isset($this->vars['L_NO_TOPICS']) ? $this->vars['L_NO_TOPICS'] : $this->lang('L_NO_TOPICS'); ?><?php } else { ?><?php echo isset($this->vars['L_NO_TOPICS_TIME_FRAME']) ? $this->vars['L_NO_TOPICS_TIME_FRAME'] : $this->lang('L_NO_TOPICS_TIME_FRAME'); ?><?php } ?></span></td>
			<?php } ?>
		</tr>
	<?php

} // END topicrow

if(isset($topicrow_item)) { unset($topicrow_item); } 

?>

	<tr align="center">
		<td class="cat" colspan="<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>7<?php } else { ?>6<?php } ?>">&nbsp;</td>
	</tr>
	</tbody>	
	</table>

	<br clear="all" />
<?php } ?>

<?php if ($this->vars['S_HAS_SUBFORUM']) {  ?>
	<?php  $this->set_filename('xs_include_7f7f01137d7e2a0bcbed20fbcb0ff6e3', 'forumlist_body.html', true);  $this->pparse('xs_include_7f7f01137d7e2a0bcbed20fbcb0ff6e3');  ?>
	<br clear="all" />
<?php } ?>

<?php if ($this->vars['S_IS_POSTABLE'] || $this->vars['S_NO_READ_ACCESS']) {  ?>
	<div id="pageheader">
		<!-- EVENT viewforum_forum_title_before -->
		<h2><!-- EVENT viewforum_forum_name_prepend --><a class="titles" href="<?php echo isset($this->vars['U_VIEW_FORUM']) ? $this->vars['U_VIEW_FORUM'] : $this->lang('U_VIEW_FORUM'); ?>"><?php echo isset($this->vars['FORUM_NAME']) ? $this->vars['FORUM_NAME'] : $this->lang('FORUM_NAME'); ?></a><!-- EVENT viewforum_forum_name_append --></h2>
		<!-- EVENT viewforum_forum_title_after -->
		<?php if ($this->vars['MODERATORS']) {  ?>
			<p class="moderators"><?php if ($this->vars['S_SINGLE_MODERATOR']) {  ?><?php echo isset($this->vars['L_MODERATOR']) ? $this->vars['L_MODERATOR'] : $this->lang('L_MODERATOR'); ?><?php } else { ?><?php echo isset($this->vars['L_MODERATORS']) ? $this->vars['L_MODERATORS'] : $this->lang('L_MODERATORS'); ?><?php } ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <?php echo isset($this->vars['MODERATORS']) ? $this->vars['MODERATORS'] : $this->lang('MODERATORS'); ?></p>
		<?php } ?>
		<?php if ($this->vars['U_MCP']) {  ?>
			<p class="linkmcp">[&nbsp;<?php if ($this->vars['U_ACP']) {  ?><a href="<?php echo isset($this->vars['U_ACP']) ? $this->vars['U_ACP'] : $this->lang('U_ACP'); ?>"><?php echo isset($this->vars['L_ACP']) ? $this->vars['L_ACP'] : $this->lang('L_ACP'); ?></a><?php if ($this->vars['U_MCP']) {  ?>&nbsp;|&nbsp;<?php } ?><?php } ?><?php if ($this->vars['U_MCP']) {  ?><a href="<?php echo isset($this->vars['U_MCP']) ? $this->vars['U_MCP'] : $this->lang('U_MCP'); ?>"><?php echo isset($this->vars['L_MCP']) ? $this->vars['L_MCP'] : $this->lang('L_MCP'); ?></a><?php } ?>&nbsp;]</p>
		<?php } ?>
	</div>

	<br clear="all" /><br />
<?php } ?>

<div id="pagecontent">

<?php if ($this->vars['S_NO_READ_ACCESS']) {  ?>
	<table class="table1 tablebg" width="100--" cellspacing="1">
	<tr>
		<td class="row1" height="30" align="center" valign="middle"><span class="gen"><?php echo isset($this->vars['L_NO_READ_ACCESS']) ? $this->vars['L_NO_READ_ACCESS'] : $this->lang('L_NO_READ_ACCESS'); ?></span></td>
	</tr>
	</table>

	<?php if (! $this->vars['S_USER_LOGGED_IN'] && ! $this->vars['S_IS_BOT']) {  ?>

		<br /><br />

		<form method="post" action="<?php echo isset($this->vars['S_LOGIN_ACTION']) ? $this->vars['S_LOGIN_ACTION'] : $this->lang('S_LOGIN_ACTION'); ?>">

		<table class="tablebg" width="100--" cellspacing="1">
		<tr>
			<td class="cat"><h4><a href="<?php echo isset($this->vars['U_LOGIN_LOGOUT']) ? $this->vars['U_LOGIN_LOGOUT'] : $this->lang('U_LOGIN_LOGOUT'); ?>"><?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?></a></h4></td>
		</tr>
		<tr>
			<td class="row1" align="center"><span class="genmed"><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></span> <input class="post" type="text" name="username" size="10" />&nbsp; <span class="genmed"><?php echo isset($this->vars['L_PASSWORD']) ? $this->vars['L_PASSWORD'] : $this->lang('L_PASSWORD'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></span> <input class="post" type="password" name="password" size="10" autocomplete="off" /><?php if ($this->vars['S_AUTOLOGIN_ENABLED']) {  ?>&nbsp; <span class="gensmall"><?php echo isset($this->vars['L_LOG_ME_IN']) ? $this->vars['L_LOG_ME_IN'] : $this->lang('L_LOG_ME_IN'); ?></span> <input type="checkbox" class="radio" name="autologin" /><?php } ?>&nbsp; <input type="submit" class="btnmain" name="login" value="<?php echo isset($this->vars['L_LOGIN']) ? $this->vars['L_LOGIN'] : $this->lang('L_LOGIN'); ?>" /></td>
		</tr>
		</table>
		<?php echo isset($this->vars['S_LOGIN_REDIRECT']) ? $this->vars['S_LOGIN_REDIRECT'] : $this->lang('S_LOGIN_REDIRECT'); ?>
		</form>

	<?php } ?>

	<br clear="all" />
<?php } ?>

	<?php if ($this->vars['S_DISPLAY_POST_INFO'] || $this->vars['TOTAL_TOPICS']) {  ?>
		<table class="table1 tablebg" width="100--" cellspacing="1">
		<tr>
			<td align="<?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?>" valign="middle" nowrap="nowrap">
			<!-- EVENT viewforum_buttons_top_before -->

			<?php if ($this->vars['S_DISPLAY_POST_INFO'] && ! $this->vars['S_IS_BOT']) {  ?>
				<a href="<?php echo isset($this->vars['U_POST_NEW_TOPIC']) ? $this->vars['U_POST_NEW_TOPIC'] : $this->lang('U_POST_NEW_TOPIC'); ?>" class="imageset"><?php echo isset($this->vars['POST_IMG']) ? $this->vars['POST_IMG'] : $this->lang('POST_IMG'); ?></a>
			<?php } ?>

			<!-- EVENT viewforum_buttons_top_after -->
			</td>

			<?php if ($this->vars['TOTAL_TOPICS']) {  ?>
				<td class="nav" valign="middle" nowrap="nowrap">&nbsp;<?php echo isset($this->vars['PAGE_NUMBER']) ? $this->vars['PAGE_NUMBER'] : $this->lang('PAGE_NUMBER'); ?><br /></td>
				<td class="gensmall" nowrap="nowrap">&nbsp;[ <?php echo isset($this->vars['TOTAL_TOPICS']) ? $this->vars['TOTAL_TOPICS'] : $this->lang('TOTAL_TOPICS'); ?> ]&nbsp;</td>
				<td class="gensmall" width="100--" align="<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>" nowrap="nowrap"><?php  $this->set_filename('xs_include_b5953cc0e7a4ed854b5ab2bcf9401d2a', 'pagination.html', true);  $this->pparse('xs_include_b5953cc0e7a4ed854b5ab2bcf9401d2a');  ?></td>
			<?php } ?>
		</tr>
		</table>
	<?php } ?>

	<?php if (! $this->vars['S_DISPLAY_ACTIVE'] && ( $this->vars['S_IS_POSTABLE'] || $this->vars['S_HAS_SUBFORUM'] )) {  ?>
		<table class="table1 tablebg" width="100--" cellspacing="1">
		<thead>
		<tr>
			<td class="cat" colspan="<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>7<?php } else { ?>6<?php } ?>">
				<table width="100--" cellspacing="0">
				<tr class="nav">
					<td valign="middle">&nbsp;<?php if ($this->vars['U_WATCH_FORUM_LINK'] && ! $this->vars['S_IS_BOT']) {  ?><a href="<?php echo isset($this->vars['U_WATCH_FORUM_LINK']) ? $this->vars['U_WATCH_FORUM_LINK'] : $this->lang('U_WATCH_FORUM_LINK'); ?>"><?php echo isset($this->vars['S_WATCH_FORUM_TITLE']) ? $this->vars['S_WATCH_FORUM_TITLE'] : $this->lang('S_WATCH_FORUM_TITLE'); ?></a><?php } ?></td>
					<td align="<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>" valign="middle"><?php if (! $this->vars['S_IS_BOT'] && $this->vars['U_MARK_TOPICS'] && $this->vars['S_HAS_SUBFORUM']) {  ?><a href="<?php echo isset($this->vars['U_MARK_TOPICS']) ? $this->vars['U_MARK_TOPICS'] : $this->lang('U_MARK_TOPICS'); ?>"><?php echo isset($this->vars['L_MARK_TOPICS_READ']) ? $this->vars['L_MARK_TOPICS_READ'] : $this->lang('L_MARK_TOPICS_READ'); ?></a><?php } ?>&nbsp;</td>
				</tr>
				</table>
			</td>
		</tr>

		<tr class="bg1">
			<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>
			<th class="table1 responsive-hide" colspan="3">&nbsp;<?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?>&nbsp;</th>
			<?php } else { ?>
			<th class="table1 responsive-hide" colspan="2">&nbsp;<?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?>&nbsp;</th>
			<?php } ?>
			<th class="table1 topicdetails responsive-hide">&nbsp;<?php echo isset($this->vars['L_AUTHOR']) ? $this->vars['L_AUTHOR'] : $this->lang('L_AUTHOR'); ?>&nbsp;</th>
			<th class="table1 topicdetails responsive-hide">&nbsp;<?php echo isset($this->vars['L_REPLIES']) ? $this->vars['L_REPLIES'] : $this->lang('L_REPLIES'); ?>&nbsp;</th>
			<th class="table1 topicdetails responsive-hide">&nbsp;<?php echo isset($this->vars['L_VIEWS']) ? $this->vars['L_VIEWS'] : $this->lang('L_VIEWS'); ?>&nbsp;</th>
			<th class="table1 topicdetails responsive-hide">&nbsp;<?php echo isset($this->vars['L_LAST_POST']) ? $this->vars['L_LAST_POST'] : $this->lang('L_LAST_POST'); ?>&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		<?php

$topicrow_count = ( isset($this->_tpldata['topicrow.']) ) ?  sizeof($this->_tpldata['topicrow.']) : 0;
for ($topicrow_i = 0; $topicrow_i < $topicrow_count; $topicrow_i++)
{
 $topicrow_item = &$this->_tpldata['topicrow.'][$topicrow_i];
 $topicrow_item['S_ROW_COUNT'] = $topicrow_i;
 $topicrow_item['S_NUM_ROWS'] = $topicrow_count;

?>

			<?php if ($topicrow_item['S_TOPIC_TYPE_SWITCH'] == 1) {  ?>
				<tr>
					<td class="row3" colspan="<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>7<?php } else { ?>6<?php } ?>"><b class="gensmall"><?php echo isset($this->vars['L_ANNOUNCEMENTS']) ? $this->vars['L_ANNOUNCEMENTS'] : $this->lang('L_ANNOUNCEMENTS'); ?></b></td>
				</tr>
			<?php } elseif ($topicrow_item['S_TOPIC_TYPE_SWITCH'] == 0) {  ?>
				<tr>
					<td class="row3" colspan="<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>7<?php } else { ?>6<?php } ?>"><b class="gensmall"><?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?></b></td>
				</tr>
			<?php } ?>

			<tr>
				<td class="row1" align="center" width="7"><?php echo isset($topicrow_item['TOPIC_FOLDER_IMG']) ? $topicrow_item['TOPIC_FOLDER_IMG'] : ''; ?></td>
				<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>
				<td class="row1 responsive-hide" width="47" align="center"><?php if ($topicrow_item['TOPIC_ICON_IMG']) {  ?><img src="<?php echo isset($this->vars['T_ICONS_PATH']) ? $this->vars['T_ICONS_PATH'] : $this->lang('T_ICONS_PATH'); ?><?php echo isset($topicrow_item['TOPIC_ICON_IMG']) ? $topicrow_item['TOPIC_ICON_IMG'] : ''; ?>" width="<?php echo isset($topicrow_item['TOPIC_ICON_IMG_WIDTH']) ? $topicrow_item['TOPIC_ICON_IMG_WIDTH'] : ''; ?>" height="<?php echo isset($topicrow_item['TOPIC_ICON_IMG_HEIGHT']) ? $topicrow_item['TOPIC_ICON_IMG_HEIGHT'] : ''; ?>" alt="" title="" /><?php } ?></td>
				<?php } ?>
				<td class="row1">
					<!-- EVENT topiclist_row_prepend -->
					<?php if ($topicrow_item['S_UNREAD_TOPIC']) {  ?><a href="<?php echo isset($topicrow_item['U_NEWEST_POST']) ? $topicrow_item['U_NEWEST_POST'] : ''; ?>" class="imageset"><?php echo isset($this->vars['NEWEST_POST_IMG']) ? $this->vars['NEWEST_POST_IMG'] : $this->lang('NEWEST_POST_IMG'); ?></a><?php } ?>
					<?php echo isset($topicrow_item['ATTACH_ICON_IMG']) ? $topicrow_item['ATTACH_ICON_IMG'] : ''; ?> <?php if ($topicrow_item['S_HAS_POLL'] || $topicrow_item['S_TOPIC_MOVED']) {  ?><b><?php echo isset($topicrow_item['TOPIC_TYPE']) ? $topicrow_item['TOPIC_TYPE'] : ''; ?></b> <?php } ?>
					<a title="<?php echo isset($this->vars['L_POSTED']) ? $this->vars['L_POSTED'] : $this->lang('L_POSTED'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <?php echo isset($topicrow_item['FIRST_POST_TIME']) ? $topicrow_item['FIRST_POST_TIME'] : ''; ?>" href="<?php echo isset($topicrow_item['U_VIEW_TOPIC']) ? $topicrow_item['U_VIEW_TOPIC'] : ''; ?>" class="topictitle"><?php echo isset($topicrow_item['TOPIC_TITLE']) ? $topicrow_item['TOPIC_TITLE'] : ''; ?></a>
					<?php if ($topicrow_item['S_TOPIC_UNAPPROVED'] || $topicrow_item['S_POSTS_UNAPPROVED']) {  ?>
						<a href="<?php echo isset($topicrow_item['U_MCP_QUEUE']) ? $topicrow_item['U_MCP_QUEUE'] : ''; ?>" class="imageset"><?php echo isset($topicrow_item['UNAPPROVED_IMG']) ? $topicrow_item['UNAPPROVED_IMG'] : ''; ?></a>&nbsp;
					<?php } ?>
					<?php if ($topicrow_item['S_TOPIC_DELETED']) {  ?>
						<a href="<?php echo isset($topicrow_item['U_MCP_QUEUE']) ? $topicrow_item['U_MCP_QUEUE'] : ''; ?>" class="imageset"><?php echo isset($this->vars['DELETED_IMG']) ? $this->vars['DELETED_IMG'] : $this->lang('DELETED_IMG'); ?></a>&nbsp;
					<?php } ?>
					<?php if ($topicrow_item['S_TOPIC_REPORTED']) {  ?>
						<a href="<?php echo isset($topicrow_item['U_MCP_REPORT']) ? $topicrow_item['U_MCP_REPORT'] : ''; ?>" class="imageset"><?php echo isset($this->vars['REPORTED_IMG']) ? $this->vars['REPORTED_IMG'] : $this->lang('REPORTED_IMG'); ?></a>&nbsp;
					<?php } ?>
					<?php if ($this->vars['S_HAS_SUBFORUMPAGINATION']) {  ?>
						<p class="gensmall"> [ <?php echo isset($this->vars['GOTO_PAGE_IMG']) ? $this->vars['GOTO_PAGE_IMG'] : $this->lang('GOTO_PAGE_IMG'); ?><?php echo isset($this->vars['L_GOTO_PAGE']) ? $this->vars['L_GOTO_PAGE'] : $this->lang('L_GOTO_PAGE'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?>
						<?php

$pagination_count = ( isset($topicrow_item['pagination.']) ) ? sizeof($topicrow_item['pagination.']) : 0;
for ($pagination_i = 0; $pagination_i < $pagination_count; $pagination_i++)
{
 $pagination_item = &$topicrow_item['pagination.'][$pagination_i];
 $pagination_item['S_ROW_COUNT'] = $pagination_i;
 $pagination_item['S_NUM_ROWS'] = $pagination_count;

?>
							<?php if (topicrowPAGINATION.S_IS_PREV) {  ?>
							<?php } elseif (topicrowPAGINATION.S_IS_CURRENT) {  ?><strong><?php echo isset($topicrowPAGINATION_item['PAGE_NUMBER']) ? $topicrowPAGINATION_item['PAGE_NUMBER'] : ''; ?></strong>
							<?php } elseif (topicrowPAGINATION.S_IS_ELLIPSIS) {  ?> <?php echo isset($this->vars['L_ELLIPSIS']) ? $this->vars['L_ELLIPSIS'] : $this->lang('L_ELLIPSIS'); ?>
							<?php } elseif (topicrowPAGINATION.S_IS_NEXT) {  ?>
							<?php } else { ?><a href="<?php echo isset($topicrowPAGINATION_item['PAGE_URL']) ? $topicrowPAGINATION_item['PAGE_URL'] : ''; ?>"><?php echo isset($topicrowPAGINATION_item['PAGE_NUMBER']) ? $topicrowPAGINATION_item['PAGE_NUMBER'] : ''; ?></a>
							<?php } ?>
						<?php

} // END pagination

if(isset($pagination_item)) { unset($pagination_item); } 

?>
						] </p>
					<?php } ?>
					<?php if ($topicrow_item['S_POST_GLOBAL'] && $this->vars['FORUM_ID'] != $topicrow_item['FORUM_ID']) {  ?><p class="gensmall"><?php echo isset($this->vars['L_IN']) ? $this->vars['L_IN'] : $this->lang('L_IN'); ?> <a href="<?php echo isset($topicrow_item['U_VIEW_FORUM']) ? $topicrow_item['U_VIEW_FORUM'] : ''; ?>"><?php echo isset($topicrow_item['FORUM_NAME']) ? $topicrow_item['FORUM_NAME'] : ''; ?></a></p><?php } ?>
					<!-- EVENT topiclist_row_append -->
				</td>
				<td class="row2" width="65" align="center"><p class="topicauthor"><?php echo isset($topicrow_item['TOPIC_AUTHOR_FULL']) ? $topicrow_item['TOPIC_AUTHOR_FULL'] : ''; ?></p></td>
				<td class="row1" width="5" align="center"><p class="topicdetails"><?php echo isset($topicrow_item['REPLIES']) ? $topicrow_item['REPLIES'] : ''; ?></p></td>
				<td class="row2" width="5" align="center"><p class="topicdetails"><?php echo isset($topicrow_item['VIEWS']) ? $topicrow_item['VIEWS'] : ''; ?></p></td>
				<td class="row1 lastpost" width="85" align="center">
					<p class="topicdetails" style="white-space: nowrap;"><?php echo isset($topicrow_item['LAST_POST_TIME']) ? $topicrow_item['LAST_POST_TIME'] : ''; ?></p>
					<p class="topicdetails"><?php echo isset($topicrow_item['LAST_POST_AUTHOR_FULL']) ? $topicrow_item['LAST_POST_AUTHOR_FULL'] : ''; ?>
						<?php if (! $this->vars['S_IS_BOT']) {  ?><a href="<?php echo isset($topicrow_item['U_LAST_POST']) ? $topicrow_item['U_LAST_POST'] : ''; ?>" class="imageset"><?php echo isset($this->vars['LAST_POST_IMG']) ? $this->vars['LAST_POST_IMG'] : $this->lang('LAST_POST_IMG'); ?></a><?php } ?>
					</p>
				</td>
			</tr>

		<?php } if(!$topicrow_count) { ?>
			<?php if ($this->vars['S_IS_POSTABLE']) {  ?>
			<tr>
				<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>
					<td class="row1" colspan="7" height="30" align="center" valign="middle"><span class="gen"><?php if (! $this->vars['S_SORT_DAYS']) {  ?><?php echo isset($this->vars['L_NO_TOPICS']) ? $this->vars['L_NO_TOPICS'] : $this->lang('L_NO_TOPICS'); ?><?php } else { ?><?php echo isset($this->vars['L_NO_TOPICS_TIME_FRAME']) ? $this->vars['L_NO_TOPICS_TIME_FRAME'] : $this->lang('L_NO_TOPICS_TIME_FRAME'); ?><?php } ?></span></td>
				<?php } else { ?>
					<td class="row1" colspan="6" height="30" align="center" valign="middle"><span class="gen"><?php if (! $this->vars['S_SORT_DAYS']) {  ?><?php echo isset($this->vars['L_NO_TOPICS']) ? $this->vars['L_NO_TOPICS'] : $this->lang('L_NO_TOPICS'); ?><?php } else { ?><?php echo isset($this->vars['L_NO_TOPICS_TIME_FRAME']) ? $this->vars['L_NO_TOPICS_TIME_FRAME'] : $this->lang('L_NO_TOPICS_TIME_FRAME'); ?><?php } ?></span></td>
				<?php } ?>
			</tr>
			<?php } ?>
		<?php

} // END topicrow

if(isset($topicrow_item)) { unset($topicrow_item); } 

?>

		<?php if (! $this->vars['S_IS_BOT']) {  ?>
		<tr align="center">
			<?php if ($this->vars['S_TOPIC_ICONS']) {  ?>
				<td class="cat" colspan="7">
			<?php } else { ?>
				<td class="cat" colspan="6">
			<?php } ?>
					<form method="post" action="<?php echo isset($this->vars['S_FORUM_ACTION']) ? $this->vars['S_FORUM_ACTION'] : $this->lang('S_FORUM_ACTION'); ?>"><span class="gensmall"><?php echo isset($this->vars['L_DISPLAY_TOPICS']) ? $this->vars['L_DISPLAY_TOPICS'] : $this->lang('L_DISPLAY_TOPICS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></span>&nbsp;<?php echo isset($this->vars['S_SELECT_SORT_DAYS']) ? $this->vars['S_SELECT_SORT_DAYS'] : $this->lang('S_SELECT_SORT_DAYS'); ?>&nbsp;<span class="gensmall"><?php echo isset($this->vars['L_SORT_BY']) ? $this->vars['L_SORT_BY'] : $this->lang('L_SORT_BY'); ?></span> <?php echo isset($this->vars['S_SELECT_SORT_KEY']) ? $this->vars['S_SELECT_SORT_KEY'] : $this->lang('S_SELECT_SORT_KEY'); ?> <?php echo isset($this->vars['S_SELECT_SORT_DIR']) ? $this->vars['S_SELECT_SORT_DIR'] : $this->lang('S_SELECT_SORT_DIR'); ?>&nbsp;<input class="btnlite" type="submit" name="sort" value="<?php echo isset($this->vars['L_GO']) ? $this->vars['L_GO'] : $this->lang('L_GO'); ?>" /></form>
				</td>
		</tr>
		<?php } ?>
		</tbody>
		</table>
	<?php } ?>

	<?php if ($this->vars['S_DISPLAY_POST_INFO'] || $this->vars['TOTAL_TOPICS']) {  ?>
		<table width="100--" cellspacing="1">
		<tr>
			<td align="<?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?>" valign="middle" nowrap="nowrap">
			<!-- EVENT viewforum_buttons_bottom_before -->

			<?php if ($this->vars['S_DISPLAY_POST_INFO'] && ! $this->vars['S_IS_BOT']) {  ?>
				<a href="<?php echo isset($this->vars['U_POST_NEW_TOPIC']) ? $this->vars['U_POST_NEW_TOPIC'] : $this->lang('U_POST_NEW_TOPIC'); ?>" class="imageset"><?php echo isset($this->vars['POST_IMG']) ? $this->vars['POST_IMG'] : $this->lang('POST_IMG'); ?></a>
			<?php } ?>

			<!-- EVENT viewforum_buttons_bottom_after -->
			</td>

			<?php if ($this->vars['TOTAL_TOPICS']) {  ?>
				<td class="nav" valign="middle" nowrap="nowrap">&nbsp;<?php echo isset($this->vars['PAGE_NUMBER']) ? $this->vars['PAGE_NUMBER'] : $this->lang('PAGE_NUMBER'); ?><br /></td>
				<td class="gensmall" nowrap="nowrap">&nbsp;[ <?php echo isset($this->vars['TOTAL_TOPICS']) ? $this->vars['TOTAL_TOPICS'] : $this->lang('TOTAL_TOPICS'); ?> ]&nbsp;</td>
				<td class="gensmall" width="100--" align="<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>" nowrap="nowrap"><?php  $this->set_filename('xs_include_3235e5abdf1a93f7f32dc70a18d94d3b', 'pagination.html', true);  $this->pparse('xs_include_3235e5abdf1a93f7f32dc70a18d94d3b');  ?></td>
			<?php } ?>
		</tr>
		</table>
	<?php } ?>

		<br clear="all" />
</div>

<?php  $this->set_filename('xs_include_16f6ae365fc6acf73bc06c550e0a95a1', 'breadcrumbs.html', true);  $this->pparse('xs_include_16f6ae365fc6acf73bc06c550e0a95a1');  ?>

<?php if ($this->vars['S_DISPLAY_ONLINE_LIST']) {  ?>
	<br clear="all" />

	<table class="tablebg stat-block online-list" width="100--" cellspacing="1">
	<tr>
		<td class="cat"><h4><?php echo isset($this->vars['L_WHO_IS_ONLINE']) ? $this->vars['L_WHO_IS_ONLINE'] : $this->lang('L_WHO_IS_ONLINE'); ?></h4></td>
	</tr>
	<tr>
		<td class="row1"><p class="gensmall"><?php echo isset($this->vars['LOGGED_IN_USER_LIST']) ? $this->vars['LOGGED_IN_USER_LIST'] : $this->lang('LOGGED_IN_USER_LIST'); ?></p></td>
	</tr>
	</table>
<?php } ?>

<?php if ($this->vars['S_DISPLAY_POST_INFO']) {  ?>
	<br clear="all" />

	<table width="100--" cellspacing="0">
	<tr>
		<td align="<?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?>" valign="top">
			<table cellspacing="3" cellpadding="0" border="0">
			<tr>
				<td width="20" style="text-align: center;"><?php echo isset($this->vars['FOLDER_UNREAD_IMG']) ? $this->vars['FOLDER_UNREAD_IMG'] : $this->lang('FOLDER_UNREAD_IMG'); ?></td>
				<td class="gensmall"><?php echo isset($this->vars['L_UNREAD_POSTS']) ? $this->vars['L_UNREAD_POSTS'] : $this->lang('L_UNREAD_POSTS'); ?></td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" style="text-align: center;"><?php echo isset($this->vars['FOLDER_IMG']) ? $this->vars['FOLDER_IMG'] : $this->lang('FOLDER_IMG'); ?></td>
				<td class="gensmall"><?php echo isset($this->vars['L_NO_UNREAD_POSTS']) ? $this->vars['L_NO_UNREAD_POSTS'] : $this->lang('L_NO_UNREAD_POSTS'); ?></td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" style="text-align: center;"><?php echo isset($this->vars['FOLDER_ANNOUNCE_IMG']) ? $this->vars['FOLDER_ANNOUNCE_IMG'] : $this->lang('FOLDER_ANNOUNCE_IMG'); ?></td>
				<td class="gensmall"><?php echo isset($this->vars['L_ICON_ANNOUNCEMENT']) ? $this->vars['L_ICON_ANNOUNCEMENT'] : $this->lang('L_ICON_ANNOUNCEMENT'); ?></td>
			</tr>
			<tr>
				<td style="text-align: center;"><?php echo isset($this->vars['FOLDER_HOT_UNREAD_IMG']) ? $this->vars['FOLDER_HOT_UNREAD_IMG'] : $this->lang('FOLDER_HOT_UNREAD_IMG'); ?></td>
				<td class="gensmall"><?php echo isset($this->vars['L_UNREAD_POSTS_HOT']) ? $this->vars['L_UNREAD_POSTS_HOT'] : $this->lang('L_UNREAD_POSTS_HOT'); ?></td>
				<td>&nbsp;&nbsp;</td>
				<td style="text-align: center;"><?php echo isset($this->vars['FOLDER_HOT_IMG']) ? $this->vars['FOLDER_HOT_IMG'] : $this->lang('FOLDER_HOT_IMG'); ?></td>
				<td class="gensmall"><?php echo isset($this->vars['L_NO_UNREAD_POSTS_HOT']) ? $this->vars['L_NO_UNREAD_POSTS_HOT'] : $this->lang('L_NO_UNREAD_POSTS_HOT'); ?></td>
				<td>&nbsp;&nbsp;</td>
				<td style="text-align: center;"><?php echo isset($this->vars['FOLDER_STICKY_IMG']) ? $this->vars['FOLDER_STICKY_IMG'] : $this->lang('FOLDER_STICKY_IMG'); ?></td>
				<td class="gensmall"><?php echo isset($this->vars['L_ICON_STICKY']) ? $this->vars['L_ICON_STICKY'] : $this->lang('L_ICON_STICKY'); ?></td>
			</tr>
			<tr>
				<td style="text-align: center;"><?php echo isset($this->vars['FOLDER_LOCKED_UNREAD_IMG']) ? $this->vars['FOLDER_LOCKED_UNREAD_IMG'] : $this->lang('FOLDER_LOCKED_UNREAD_IMG'); ?></td>
				<td class="gensmall"><?php echo isset($this->vars['L_UNREAD_POSTS_LOCKED']) ? $this->vars['L_UNREAD_POSTS_LOCKED'] : $this->lang('L_UNREAD_POSTS_LOCKED'); ?></td>
				<td>&nbsp;&nbsp;</td>
				<td style="text-align: center;"><?php echo isset($this->vars['FOLDER_LOCKED_IMG']) ? $this->vars['FOLDER_LOCKED_IMG'] : $this->lang('FOLDER_LOCKED_IMG'); ?></td>
				<td class="gensmall"><?php echo isset($this->vars['L_NO_UNREAD_POSTS_LOCKED']) ? $this->vars['L_NO_UNREAD_POSTS_LOCKED'] : $this->lang('L_NO_UNREAD_POSTS_LOCKED'); ?></td>
				<td>&nbsp;&nbsp;</td>
				<td style="text-align: center;"><?php echo isset($this->vars['FOLDER_MOVED_IMG']) ? $this->vars['FOLDER_MOVED_IMG'] : $this->lang('FOLDER_MOVED_IMG'); ?></td>
				<td class="gensmall"><?php echo isset($this->vars['L_TOPIC_MOVED']) ? $this->vars['L_TOPIC_MOVED'] : $this->lang('L_TOPIC_MOVED'); ?></td>
			</tr>
			</table>
		</td>
		<td align="<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>"><span class="gensmall"><?php

$rules_count = ( isset($this->_tpldata['rules.']) ) ?  sizeof($this->_tpldata['rules.']) : 0;
for ($rules_i = 0; $rules_i < $rules_count; $rules_i++)
{
 $rules_item = &$this->_tpldata['rules.'][$rules_i];
 $rules_item['S_ROW_COUNT'] = $rules_i;
 $rules_item['S_NUM_ROWS'] = $rules_count;

?><?php echo isset($rules_item['RULE']) ? $rules_item['RULE'] : ''; ?><br /><?php

} // END rules

if(isset($rules_item)) { unset($rules_item); } 

?></span></td>
	</tr>
	</table>
<?php } ?>

<br clear="all" />

<table width="100--" cellspacing="0">
<tr>
	<td><?php if ($this->vars['S_DISPLAY_SEARCHBOX']) {  ?><?php  $this->set_filename('xs_include_c3c3316091e58f61a49baa747fce3daf', 'searchbox.html', true);  $this->pparse('xs_include_c3c3316091e58f61a49baa747fce3daf');  ?><?php } ?></td>
	<td align="<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>"><?php  $this->set_filename('xs_include_f29b69182cd1402e5bc363ddb060e338', 'jumpbox.html', true);  $this->pparse('xs_include_f29b69182cd1402e5bc363ddb060e338');  ?></td>
</tr>
</table>

<!-- INCLUDEX overall_footer.html -->
