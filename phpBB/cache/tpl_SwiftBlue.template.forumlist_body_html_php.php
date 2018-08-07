<?php

// eXtreme Styles mod cache. Generated on Fri, 27 Jul 2018 16:44:47 +0000 (time=1532709887)

?><table class="tablebg" cellspacing="1" width="100--">
<thead>
<tr>
	<td class="cat" colspan="5" align="<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>"><?php if (! $this->vars['S_IS_BOT'] && $this->vars['U_MARK_FORUMS']) {  ?><a class="nav" href="<?php echo isset($this->vars['U_MARK_FORUMS']) ? $this->vars['U_MARK_FORUMS'] : $this->lang('U_MARK_FORUMS'); ?>"><?php echo isset($this->vars['L_MARK_FORUMS_READ']) ? $this->vars['L_MARK_FORUMS_READ'] : $this->lang('L_MARK_FORUMS_READ'); ?></a><?php } ?>&nbsp;</td>
</tr>

<tr>	
		<th data-last-responsive="true" class="table1 " colspan="2">&nbsp;<?php echo isset($this->vars['L_FORUM']) ? $this->vars['L_FORUM'] : $this->lang('L_FORUM'); ?>&nbsp;</th>
		<th class="table1 "  width="5">&nbsp;<?php echo isset($this->vars['L_TOPICS']) ? $this->vars['L_TOPICS'] : $this->lang('L_TOPICS'); ?>&nbsp;</th>
		<th class="table1 topicdetails responsive-hide" width="5">&nbsp;<?php echo isset($this->vars['L_POSTS']) ? $this->vars['L_POSTS'] : $this->lang('L_POSTS'); ?>&nbsp;</th>
		<th class="table1 topicdetails responsive-hide">&nbsp;<?php echo isset($this->vars['L_LAST_POST']) ? $this->vars['L_LAST_POST'] : $this->lang('L_LAST_POST'); ?>&nbsp;</th>
	
</tr>
</thead>
<tbody>
<?php

$forumrow_count = ( isset($this->_tpldata['forumrow.']) ) ?  sizeof($this->_tpldata['forumrow.']) : 0;
for ($forumrow_i = 0; $forumrow_i < $forumrow_count; $forumrow_i++)
{
 $forumrow_item = &$this->_tpldata['forumrow.'][$forumrow_i];
 $forumrow_item['S_ROW_COUNT'] = $forumrow_i;
 $forumrow_item['S_NUM_ROWS'] = $forumrow_count;

?>
	<!-- EVENT forumlist_body_category_header_before -->
	<?php if ($forumrow_item['S_IS_CAT']) {  ?>
		<tr>
			<td class="cat" colspan="2"><h4><a href="<?php echo isset($forumrow_item['U_VIEWFORUM']) ? $forumrow_item['U_VIEWFORUM'] : ''; ?>"><?php echo isset($forumrow_item['FORUM_NAME']) ? $forumrow_item['FORUM_NAME'] : ''; ?></a></h4></td>
			<td class="catdiv table1" colspan="1">&nbsp;</td>

			<td class="catdiv table1 topicdetails responsive-hide" colspan="2">&nbsp;</td>			
		</tr>
	<!-- EVENT forumlist_body_category_header_after -->
	<?php } elseif ($forumrow_item['S_IS_LINK']) {  ?>
		<?php if ($forumrow_item['S_NO_CAT']) {  ?>
		<tr>
			<td class="cat" colspan="2"><h4><?php echo isset($this->vars['L_FORUM']) ? $this->vars['L_FORUM'] : $this->lang('L_FORUM'); ?></h4></td>
			<td class="catdiv table1" colspan="1">&nbsp;</td>

			<td class="catdiv table1 topicdetails responsive-hide" colspan="2">&nbsp;</td>			
		</tr>
		<?php } ?>
		<tr>
			<td class="row1" width="50" align="center"><?php echo isset($forumrow_item['FORUM_FOLDER_IMG']) ? $forumrow_item['FORUM_FOLDER_IMG'] : ''; ?></td>
			<td class="row1">
				<?php if ($forumrow_item['FORUM_IMAGE']) {  ?>
					<div style="float: <?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?>; margin-<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> 5px;"><?php echo isset($forumrow_item['FORUM_IMAGE']) ? $forumrow_item['FORUM_IMAGE'] : ''; ?></div>
				<?php } ?>
				<a class="forumlink" href="<?php echo isset($forumrow_item['U_VIEWFORUM']) ? $forumrow_item['U_VIEWFORUM'] : ''; ?>"><?php echo isset($forumrow_item['FORUM_NAME']) ? $forumrow_item['FORUM_NAME'] : ''; ?></a>
				<p class="forumdesc"><?php echo isset($forumrow_item['FORUM_DESC']) ? $forumrow_item['FORUM_DESC'] : ''; ?></p>
			</td>
			<?php if ($forumrow_item['CLICKS']) {  ?>
			<td class="row2 table1 topicdetails" colspan="1" align="center"><span class="genmed"><?php echo isset($this->vars['L_REDIRECTS']) ? $this->vars['L_REDIRECTS'] : $this->lang('L_REDIRECTS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <?php echo isset($forumrow_item['CLICKS']) ? $forumrow_item['CLICKS'] : ''; ?></span></td>
			<td class="row2 table1 topicdetails responsive-hide" colspan="2" align="center"><span class="genmed"><?php echo isset($this->vars['L_REDIRECTS']) ? $this->vars['L_REDIRECTS'] : $this->lang('L_REDIRECTS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <?php echo isset($forumrow_item['CLICKS']) ? $forumrow_item['CLICKS'] : ''; ?></span></td>			
			<?php } else { ?>
			<td class="row2 table1 topicdetails" colspan="1" align="center">&nbsp;</td>

			<td class="row2 table1 topicdetails responsive-hide" colspan="2" align="center">&nbsp;</td>			
			<?php } ?>
		</tr>
	<?php } else { ?>
		<?php if ($forumrow_item['S_NO_CAT']) {  ?>
			<tr>
				<td class="cat" colspan="2"><h4><?php echo isset($this->vars['L_FORUM']) ? $this->vars['L_FORUM'] : $this->lang('L_FORUM'); ?></h4></td>
				<td class="catdiv table1" colspan="1">&nbsp;</td>
				
				<td class="catdiv table1 topicdetails responsive-hide" colspan="2">&nbsp;</td>
			</tr>
		<?php } ?>
		<!-- EVENT forumlist_body_forum_row_before -->
		<tr>
			<!-- EVENT forumlist_body_forum_row_prepend -->
			<td class="row1 forumlink" width="60" align="center"><?php echo isset($forumrow_item['FORUM_FOLDER_IMG']) ? $forumrow_item['FORUM_FOLDER_IMG'] : ''; ?></td>
			<td class="row1 cat-title" width="50--">
				<?php if ($forumrow_item['FORUM_IMAGE']) {  ?>
					<div style="float: <?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?>; margin-<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> 5px;"><?php echo isset($forumrow_item['FORUM_IMAGE']) ? $forumrow_item['FORUM_IMAGE'] : ''; ?></div>
				<?php } ?>
				<a class="forumlink" href="<?php echo isset($forumrow_item['U_VIEWFORUM']) ? $forumrow_item['U_VIEWFORUM'] : ''; ?>"><?php echo isset($forumrow_item['FORUM_NAME']) ? $forumrow_item['FORUM_NAME'] : ''; ?></a>
				<p class="forumdesc"><?php echo isset($forumrow_item['FORUM_DESC']) ? $forumrow_item['FORUM_DESC'] : ''; ?></p>
				<?php if ($forumrow_item['MODERATORS']) {  ?>
					<p class="forumdesc"><strong><?php echo isset($forumrow_item['L_MODERATOR_STR']) ? $forumrow_item['L_MODERATOR_STR'] : ''; ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <?php echo isset($forumrow_item['MODERATORS']) ? $forumrow_item['MODERATORS'] : ''; ?></p>
				<?php } ?>
				<?php if (forumrow.subforum|length && $forumrow_item['S_LIST_SUBFORUMS']) {  ?>
					<!-- EVENT forumlist_body_subforums_before -->
					<p class="forumdesc"><strong><?php echo isset($forumrow_item['L_SUBFORUM_STR']) ? $forumrow_item['L_SUBFORUM_STR'] : ''; ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong>
					<?php

$subforum_count = ( isset($forumrow_item['subforum.']) ) ? sizeof($forumrow_item['subforum.']) : 0;
for ($subforum_i = 0; $subforum_i < $subforum_count; $subforum_i++)
{
 $subforum_item = &$forumrow_item['subforum.'][$subforum_i];
 $subforum_item['S_ROW_COUNT'] = $subforum_i;
 $subforum_item['S_NUM_ROWS'] = $subforum_count;

?>
						<a href="<?php echo isset($subforum_item['U_SUBFORUM']) ? $subforum_item['U_SUBFORUM'] : ''; ?>" class="subforum<?php if ($subforum_item['S_UNREAD']) {  ?> unread<?php } else { ?> read<?php } ?>" title="<?php if ($subforum_item['S_UNREAD']) {  ?><?php echo isset($this->vars['L_UNREAD_POSTS']) ? $this->vars['L_UNREAD_POSTS'] : $this->lang('L_UNREAD_POSTS'); ?><?php } else { ?><?php echo isset($this->vars['L_NO_UNREAD_POSTS']) ? $this->vars['L_NO_UNREAD_POSTS'] : $this->lang('L_NO_UNREAD_POSTS'); ?><?php } ?>"><?php echo isset($subforum_item['SUBFORUM_NAME']) ? $subforum_item['SUBFORUM_NAME'] : ''; ?></a><?php if (! $subforum_item['S_LAST_ROW']) {  ?><?php echo isset($this->vars['L_COMMA_SEPARATOR']) ? $this->vars['L_COMMA_SEPARATOR'] : $this->lang('L_COMMA_SEPARATOR'); ?><?php } ?>
					<?php

} // END subforum

if(isset($subforum_item)) { unset($subforum_item); } 

?>
					</p>
					<!-- EVENT forumlist_body_subforums_after -->
				<?php } ?>
			</td>
			<td class="row2 topicdetails" align="center" width="10"><p class="topicdetails"><?php echo isset($forumrow_item['TOPICS']) ? $forumrow_item['TOPICS'] : ''; ?></p></td>
			<td class="row2 topicdetails responsive-hide" align="center" width="10"><p class="topicdetails"><?php echo isset($forumrow_item['POSTS']) ? $forumrow_item['POSTS'] : ''; ?></p></td>
			<td class="row2 topicdetails responsive-hide" align="center" width="30--">
				<?php if ($forumrow_item['LAST_POST_TIME']) {  ?>
					<?php if ($forumrow_item['S_DISPLAY_SUBJECT']) {  ?>
						<!-- EVENT forumlist_body_last_post_title_prepend -->
						<p class="topicdetails"><a href="<?php echo isset($forumrow_item['U_LAST_POST']) ? $forumrow_item['U_LAST_POST'] : ''; ?>" title="<?php echo isset($forumrow_item['LAST_POST_SUBJECT']) ? $forumrow_item['LAST_POST_SUBJECT'] : ''; ?>" class="lastsubject"><?php echo isset($forumrow_item['LAST_POST_SUBJECT_TRUNCATED']) ? $forumrow_item['LAST_POST_SUBJECT_TRUNCATED'] : ''; ?></a></p>
					<?php } ?>
					<p class="topicdetails">
						<?php if ($forumrow_item['U_UNAPPROVED_TOPICS']) {  ?>
							<a href="<?php echo isset($forumrow_item['U_UNAPPROVED_TOPICS']) ? $forumrow_item['U_UNAPPROVED_TOPICS'] : ''; ?>" class="imageset"><?php echo isset($this->vars['UNAPPROVED_IMG']) ? $this->vars['UNAPPROVED_IMG'] : $this->lang('UNAPPROVED_IMG'); ?></a>&nbsp;
						<?php } elseif ($forumrow_item['U_UNAPPROVED_POSTS']) {  ?>
							<a href="<?php echo isset($forumrow_item['U_UNAPPROVED_POSTS']) ? $forumrow_item['U_UNAPPROVED_POSTS'] : ''; ?>" class="imageset"><?php echo isset($this->vars['UNAPPROVED_POST_IMG']) ? $this->vars['UNAPPROVED_POST_IMG'] : $this->lang('UNAPPROVED_POST_IMG'); ?></a>&nbsp;
						<?php } ?>
						<?php echo isset($forumrow_item['LAST_POST_TIME']) ? $forumrow_item['LAST_POST_TIME'] : ''; ?>
					</p>
					<p class="topicdetails"><?php echo isset($forumrow_item['LAST_POSTER_FULL']) ? $forumrow_item['LAST_POSTER_FULL'] : ''; ?>
						<?php if (! $this->vars['S_IS_BOT']) {  ?><a href="<?php echo isset($forumrow_item['U_LAST_POST']) ? $forumrow_item['U_LAST_POST'] : ''; ?>" class="imageset"><?php echo isset($this->vars['LAST_POST_IMG']) ? $this->vars['LAST_POST_IMG'] : $this->lang('LAST_POST_IMG'); ?></a><?php } ?>
					</p>
				<?php } else { ?>
					<p class="topicdetails"><?php echo isset($this->vars['L_NO_POSTS']) ? $this->vars['L_NO_POSTS'] : $this->lang('L_NO_POSTS'); ?></p>
				<?php } ?>
			</td>
			<!-- EVENT forumlist_body_forum_row_append -->
		</tr>
		<!-- EVENT forumlist_body_forum_row_after -->
	<?php } ?>
	<!-- EVENT forumlist_body_last_row_after -->
<?php } if(!$forumrow_count) { ?>
	<tr>
		<td class="row1" colspan="5" align="center"><p class="gensmall"><?php echo isset($this->vars['L_NO_FORUMS']) ? $this->vars['L_NO_FORUMS'] : $this->lang('L_NO_FORUMS'); ?></p></td>
	</tr>
<?php

} // END forumrow

if(isset($forumrow_item)) { unset($forumrow_item); } 

?>
</tbody>
</table>
