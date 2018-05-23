<?php

// eXtreme Styles mod cache. Generated on Fri, 18 May 2018 18:23:35 +0000 (time=1526667815)

?>
<?php

$forumrow_count = ( isset($this->_tpldata['forumrow.']) ) ?  sizeof($this->_tpldata['forumrow.']) : 0;
for ($forumrow_i = 0; $forumrow_i < $forumrow_count; $forumrow_i++)
{
 $forumrow_item = &$this->_tpldata['forumrow.'][$forumrow_i];
 $forumrow_item['S_ROW_COUNT'] = $forumrow_i;
 $forumrow_item['S_NUM_ROWS'] = $forumrow_count;

?>
	<?php if (( $forumrow_item['S_IS_CAT'] && ! $forumrow_item['S_FIRST_ROW'] ) || $forumrow_item['S_NO_CAT']) {  ?>
			</ul>

			</div>
		</div>
	<?php } ?>

	<!-- EVENT forumlist_body_category_header_before -->
	<?php if ($forumrow_item['S_IS_CAT'] || $forumrow_item['S_FIRST_ROW'] || $forumrow_item['S_NO_CAT']) {  ?>
		<div class="forabg">
			<div class="inner">
			<ul class="topiclist">
				<li class="header">
					<!-- EVENT forumlist_body_category_header_row_prepend -->
					<dl class="icon">
						<dt><div class="list-inner"><?php if ($forumrow_item['S_IS_CAT']) {  ?><a href="<?php echo isset($forumrow_item['U_VIEWFORUM']) ? $forumrow_item['U_VIEWFORUM'] : ''; ?>"><?php echo isset($forumrow_item['FORUM_NAME']) ? $forumrow_item['FORUM_NAME'] : ''; ?></a><?php } else { ?><?php echo isset($this->vars['L_FORUM']) ? $this->vars['L_FORUM'] : $this->lang('L_FORUM'); ?><?php } ?></div></dt>
						<dd class="topics"><?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?></dd>
						<dd class="posts"><?php echo isset($this->vars['L_POSTS']) ? $this->vars['L_POSTS'] : $this->lang('L_POSTS'); ?></dd>
						<dd class="lastpost"><span><?php echo isset($this->vars['L_LAST_POST']) ? $this->vars['L_LAST_POST'] : $this->lang('L_LAST_POST'); ?></span></dd>
					</dl>
					<!-- EVENT forumlist_body_category_header_row_append -->
				</li>
			</ul>
			<ul class="topiclist forums">
	<?php } ?>
	<!-- EVENT forumlist_body_category_header_after -->

	<?php if (! $forumrow_item['S_IS_CAT']) {  ?>
		<!-- EVENT forumlist_body_forum_row_before -->
		<li class="row">
			<!-- EVENT forumlist_body_forum_row_prepend -->
			<dl class="icon <?php echo isset($forumrow_item['FORUM_IMG_STYLE']) ? $forumrow_item['FORUM_IMG_STYLE'] : ''; ?>">
				<dt title="<?php echo isset($forumrow_item['FORUM_FOLDER_IMG_ALT']) ? $forumrow_item['FORUM_FOLDER_IMG_ALT'] : ''; ?>">
					<?php if ($forumrow_item['S_UNREAD_FORUM']) {  ?><a href="<?php echo isset($forumrow_item['U_VIEWFORUM']) ? $forumrow_item['U_VIEWFORUM'] : ''; ?>" class="icon-link"></a><?php } ?>
					<div class="list-inner">
						<?php if ($this->vars['S_ENABLE_FEEDS'] && $forumrow_item['S_FEED_ENABLED']) {  ?><!-- <a class="feed-icon-forum" title="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($forumrow_item['FORUM_NAME']) ? $forumrow_item['FORUM_NAME'] : ''; ?>" href="<?php echo isset($this->vars['U_FEED']) ? $this->vars['U_FEED'] : $this->lang('U_FEED'); ?>?f=<?php echo isset($forumrow_item['FORUM_ID']) ? $forumrow_item['FORUM_ID'] : ''; ?>"><img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/feed.gif" alt="<?php echo isset($this->vars['L_FEED']) ? $this->vars['L_FEED'] : $this->lang('L_FEED'); ?> - <?php echo isset($forumrow_item['FORUM_NAME']) ? $forumrow_item['FORUM_NAME'] : ''; ?>" /></a> --><?php } ?>

						<?php if ($forumrow_item['FORUM_IMAGE']) {  ?><span class="forum-image"><?php echo isset($forumrow_item['FORUM_IMAGE']) ? $forumrow_item['FORUM_IMAGE'] : ''; ?></span><?php } ?>
						<a href="<?php echo isset($forumrow_item['U_VIEWFORUM']) ? $forumrow_item['U_VIEWFORUM'] : ''; ?>" class="forumtitle"><?php echo isset($forumrow_item['FORUM_NAME']) ? $forumrow_item['FORUM_NAME'] : ''; ?></a>
						<?php if ($forumrow_item['FORUM_DESC']) {  ?><br /><?php echo isset($forumrow_item['FORUM_DESC']) ? $forumrow_item['FORUM_DESC'] : ''; ?><?php } ?>
						<?php if ($forumrow_item['MODERATORS']) {  ?>
							<br /><strong><?php echo isset($forumrow_item['L_MODERATOR_STR']) ? $forumrow_item['L_MODERATOR_STR'] : ''; ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <?php echo isset($forumrow_item['MODERATORS']) ? $forumrow_item['MODERATORS'] : ''; ?>
						<?php } ?>
						<?php if (forumrow.subforum|length && $forumrow_item['S_LIST_SUBFORUMS']) {  ?>
							<!-- EVENT forumlist_body_subforums_before -->
							<br /><strong><?php echo isset($forumrow_item['L_SUBFORUM_STR']) ? $forumrow_item['L_SUBFORUM_STR'] : ''; ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong>
							<?php

$subforum_count = ( isset($forumrow_item['subforum.']) ) ? sizeof($forumrow_item['subforum.']) : 0;
for ($subforum_i = 0; $subforum_i < $subforum_count; $subforum_i++)
{
 $subforum_item = &$forumrow_item['subforum.'][$subforum_i];
 $subforum_item['S_ROW_COUNT'] = $subforum_i;
 $subforum_item['S_NUM_ROWS'] = $subforum_count;

?>
								<!-- EVENT forumlist_body_subforum_link_prepend --><a href="<?php echo isset($subforum_item['U_SUBFORUM']) ? $subforum_item['U_SUBFORUM'] : ''; ?>" class="subforum<?php if ($subforum_item['S_UNREAD']) {  ?> unread<?php } else { ?> read<?php } ?>" title="<?php if ($subforum_item['S_UNREAD']) {  ?><?php echo isset($this->vars['L_UNREAD_POSTS']) ? $this->vars['L_UNREAD_POSTS'] : $this->lang('L_UNREAD_POSTS'); ?><?php } else { ?><?php echo isset($this->vars['L_NO_UNREAD_POSTS']) ? $this->vars['L_NO_UNREAD_POSTS'] : $this->lang('L_NO_UNREAD_POSTS'); ?><?php } ?>"><?php echo isset($subforum_item['SUBFORUM_NAME']) ? $subforum_item['SUBFORUM_NAME'] : ''; ?></a><?php if (! $subforum_item['S_LAST_ROW']) {  ?><?php echo isset($this->vars['L_COMMA_SEPARATOR']) ? $this->vars['L_COMMA_SEPARATOR'] : $this->lang('L_COMMA_SEPARATOR'); ?><?php } ?><!-- EVENT forumlist_body_subforum_link_append -->
							<?php

} // END subforum

if(isset($subforum_item)) { unset($subforum_item); } 

?>
							<!-- EVENT forumlist_body_subforums_after -->
						<?php } ?>

						<?php if (! $this->vars['S_IS_BOT']) {  ?>
						<div class="responsive-show" style="display: none;">
							<?php if ($forumrow_item['CLICKS']) {  ?>
								<?php echo isset($this->vars['L_REDIRECTS']) ? $this->vars['L_REDIRECTS'] : $this->lang('L_REDIRECTS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <strong><?php echo isset($forumrow_item['CLICKS']) ? $forumrow_item['CLICKS'] : ''; ?></strong>
							<?php } elseif (! $forumrow_item['S_IS_LINK'] && $forumrow_item['TOPICS']) {  ?>
								<?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <strong><?php echo isset($forumrow_item['TOPICS']) ? $forumrow_item['TOPICS'] : ''; ?></strong>
							<?php } ?>
						</div>
						<?php } ?>
					</div>
				</dt>
				<?php if ($forumrow_item['CLICKS']) {  ?>
					<dd class="redirect"><span><?php echo isset($this->vars['L_REDIRECTS']) ? $this->vars['L_REDIRECTS'] : $this->lang('L_REDIRECTS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <?php echo isset($forumrow_item['CLICKS']) ? $forumrow_item['CLICKS'] : ''; ?></span></dd>
				<?php } elseif (! $forumrow_item['S_IS_LINK']) {  ?>
					<dd class="topics"><?php echo isset($forumrow_item['TOPICS']) ? $forumrow_item['TOPICS'] : ''; ?> <dfn><?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?></dfn></dd>
					<dd class="posts"><?php echo isset($forumrow_item['POSTS']) ? $forumrow_item['POSTS'] : ''; ?> <dfn><?php echo isset($this->vars['L_POSTS']) ? $this->vars['L_POSTS'] : $this->lang('L_POSTS'); ?></dfn></dd>
					<dd class="lastpost"><span>
						<?php if ($forumrow_item['U_UNAPPROVED_TOPICS']) {  ?>
							<a href="<?php echo isset($forumrow_item['U_UNAPPROVED_TOPICS']) ? $forumrow_item['U_UNAPPROVED_TOPICS'] : ''; ?>"><?php echo isset($this->vars['UNAPPROVED_IMG']) ? $this->vars['UNAPPROVED_IMG'] : $this->lang('UNAPPROVED_IMG'); ?></a>
						<?php } elseif ($forumrow_item['U_UNAPPROVED_POSTS']) {  ?>
							<a href="<?php echo isset($forumrow_item['U_UNAPPROVED_POSTS']) ? $forumrow_item['U_UNAPPROVED_POSTS'] : ''; ?>"><?php echo isset($this->vars['UNAPPROVED_POST_IMG']) ? $this->vars['UNAPPROVED_POST_IMG'] : $this->lang('UNAPPROVED_POST_IMG'); ?></a>
						<?php } ?>
						<?php if ($forumrow_item['LAST_POST_TIME']) {  ?><dfn><?php echo isset($this->vars['L_LAST_POST']) ? $this->vars['L_LAST_POST'] : $this->lang('L_LAST_POST'); ?></dfn>
						<?php if ($forumrow_item['S_DISPLAY_SUBJECT']) {  ?>
							<!-- EVENT forumlist_body_last_post_title_prepend -->
							<a href="<?php echo isset($forumrow_item['U_LAST_POST']) ? $forumrow_item['U_LAST_POST'] : ''; ?>" title="<?php echo isset($forumrow_item['LAST_POST_SUBJECT']) ? $forumrow_item['LAST_POST_SUBJECT'] : ''; ?>" class="lastsubject"><?php echo isset($forumrow_item['LAST_POST_SUBJECT_TRUNCATED']) ? $forumrow_item['LAST_POST_SUBJECT_TRUNCATED'] : ''; ?></a> <br />
						<?php } ?> 
						<?php echo isset($this->vars['L_POST_BY_AUTHOR']) ? $this->vars['L_POST_BY_AUTHOR'] : $this->lang('L_POST_BY_AUTHOR'); ?> <?php echo isset($forumrow_item['LAST_POSTER_FULL']) ? $forumrow_item['LAST_POSTER_FULL'] : ''; ?>
						<?php if (! $this->vars['S_IS_BOT']) {  ?><a href="<?php echo isset($forumrow_item['U_LAST_POST']) ? $forumrow_item['U_LAST_POST'] : ''; ?>"><?php echo isset($this->vars['LAST_POST_IMG']) ? $this->vars['LAST_POST_IMG'] : $this->lang('LAST_POST_IMG'); ?></a> <?php } ?><br /><?php echo isset($forumrow_item['LAST_POST_TIME']) ? $forumrow_item['LAST_POST_TIME'] : ''; ?><?php } else { ?><?php echo isset($this->vars['L_NO_POSTS']) ? $this->vars['L_NO_POSTS'] : $this->lang('L_NO_POSTS'); ?><br />&nbsp;<?php } ?></span>
					</dd>
				<?php } else { ?>
					<dd>&nbsp;</dd>
				<?php } ?>
			</dl>
			<!-- EVENT forumlist_body_forum_row_append -->
		</li>
		<!-- EVENT forumlist_body_forum_row_after -->
	<?php } ?>

	<?php if ($forumrow_item['S_LAST_ROW']) {  ?>
			</ul>

			</div>
		</div>
	<!-- EVENT forumlist_body_last_row_after -->
	<?php } ?>

<?php } if(!$forumrow_count) { ?>
	<div class="panel">
		<div class="inner">
		<strong><?php echo isset($this->vars['L_NO_FORUMS']) ? $this->vars['L_NO_FORUMS'] : $this->lang('L_NO_FORUMS'); ?></strong>
		</div>
	</div>
<?php

} // END forumrow

if(isset($forumrow_item)) { unset($forumrow_item); } 

?>
