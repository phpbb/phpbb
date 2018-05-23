<?php

// eXtreme Styles mod cache. Generated on Tue, 22 May 2018 19:50:05 +0000 (time=1527018605)

?><!-- INCLUDEX overall_header.html -->

<h2 class="topic-title"><!-- EVENT viewtopic_topic_title_prepend --><a href="<?php echo isset($this->vars['U_VIEW_TOPIC']) ? $this->vars['U_VIEW_TOPIC'] : $this->lang('U_VIEW_TOPIC'); ?>"><?php echo isset($this->vars['TOPIC_TITLE']) ? $this->vars['TOPIC_TITLE'] : $this->lang('TOPIC_TITLE'); ?></a><!-- EVENT viewtopic_topic_title_append --></h2>
<!-- EVENT viewtopic_topic_title_after -->
<!-- NOTE: remove the style="display: none" when you want to have the forum description on the topic body -->
<?php if ($this->vars['FORUM_DESC']) {  ?><div style="display: none !important;"><?php echo isset($this->vars['FORUM_DESC']) ? $this->vars['FORUM_DESC'] : $this->lang('FORUM_DESC'); ?><br /></div><?php } ?>

<?php if ($this->vars['MODERATORS']) {  ?>
<p>
	<strong><?php if ($this->vars['S_SINGLE_MODERATOR']) {  ?><?php echo isset($this->vars['L_MODERATOR']) ? $this->vars['L_MODERATOR'] : $this->lang('L_MODERATOR'); ?><?php } else { ?><?php echo isset($this->vars['L_MODERATORS']) ? $this->vars['L_MODERATORS'] : $this->lang('L_MODERATORS'); ?><?php } ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <?php echo isset($this->vars['MODERATORS']) ? $this->vars['MODERATORS'] : $this->lang('MODERATORS'); ?>
</p>
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

<div class="action-bar top">

	<div class="buttons">
		<!-- EVENT viewtopic_buttons_top_before -->

	<?php if (! $this->vars['S_IS_BOT'] && $this->vars['S_DISPLAY_REPLY_INFO']) {  ?>
		<a href="<?php echo isset($this->vars['U_POST_REPLY_TOPIC']) ? $this->vars['U_POST_REPLY_TOPIC'] : $this->lang('U_POST_REPLY_TOPIC'); ?>" class="button icon-button <?php if ($this->vars['S_IS_LOCKED']) {  ?>locked-icon<?php } else { ?>reply-icon<?php } ?>" title="<?php if ($this->vars['S_IS_LOCKED']) {  ?><?php echo isset($this->vars['L_TOPIC_LOCKED']) ? $this->vars['L_TOPIC_LOCKED'] : $this->lang('L_TOPIC_LOCKED'); ?><?php } else { ?><?php echo isset($this->vars['L_POST_REPLY']) ? $this->vars['L_POST_REPLY'] : $this->lang('L_POST_REPLY'); ?><?php } ?>">
			<?php if ($this->vars['S_IS_LOCKED']) {  ?><?php echo isset($this->vars['L_BUTTON_TOPIC_LOCKED']) ? $this->vars['L_BUTTON_TOPIC_LOCKED'] : $this->lang('L_BUTTON_TOPIC_LOCKED'); ?><?php } else { ?><?php echo isset($this->vars['L_BUTTON_POST_REPLY']) ? $this->vars['L_BUTTON_POST_REPLY'] : $this->lang('L_BUTTON_POST_REPLY'); ?><?php } ?>
		</a>
	<?php } ?>

		<!-- EVENT viewtopic_buttons_top_after -->
	</div>

	<?php  $this->set_filename('xs_include_3dd2835da7b1fad32d63722b200ee65c', 'viewtopic_topic_tools.html', true);  $this->pparse('xs_include_3dd2835da7b1fad32d63722b200ee65c');  ?>
	<!-- EVENT viewtopic_dropdown_top_custom -->

	<?php if ($this->vars['S_DISPLAY_SEARCHBOX']) {  ?>
		<div class="search-box" role="search">
			<form method="get" id="topic-search" action="<?php echo isset($this->vars['S_SEARCHBOX_ACTION']) ? $this->vars['S_SEARCHBOX_ACTION'] : $this->lang('S_SEARCHBOX_ACTION'); ?>">
			<fieldset>
				<input class="inputbox search tiny"  type="search" name="keywords" id="search_keywords" size="20" placeholder="<?php echo isset($this->vars['L_SEARCH_TOPIC']) ? $this->vars['L_SEARCH_TOPIC'] : $this->lang('L_SEARCH_TOPIC'); ?>" />
				<button class="button icon-button search-icon" type="submit" title="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>"><?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?></button>
				<a href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" class="button icon-button search-adv-icon" title="<?php echo isset($this->vars['L_SEARCH_ADV']) ? $this->vars['L_SEARCH_ADV'] : $this->lang('L_SEARCH_ADV'); ?>"><?php echo isset($this->vars['L_SEARCH_ADV']) ? $this->vars['L_SEARCH_ADV'] : $this->lang('L_SEARCH_ADV'); ?></a>
				<?php echo isset($this->vars['S_SEARCH_LOCAL_HIDDEN_FIELDS']) ? $this->vars['S_SEARCH_LOCAL_HIDDEN_FIELDS'] : $this->lang('S_SEARCH_LOCAL_HIDDEN_FIELDS'); ?>
			</fieldset>
			</form>
		</div>
	<?php } ?>

	<?php if ($this->vars['PAGINATION'] || $this->vars['TOTAL_POSTS']) {  ?>
		<div class="pagination">
			<?php if ($this->vars['U_VIEW_UNREAD_POST'] && ! $this->vars['S_IS_BOT']) {  ?><a href="<?php echo isset($this->vars['U_VIEW_UNREAD_POST']) ? $this->vars['U_VIEW_UNREAD_POST'] : $this->lang('U_VIEW_UNREAD_POST'); ?>" class="mark"><?php echo isset($this->vars['L_VIEW_UNREAD_POST']) ? $this->vars['L_VIEW_UNREAD_POST'] : $this->lang('L_VIEW_UNREAD_POST'); ?></a> &bull; <?php } ?><?php echo isset($this->vars['TOTAL_POSTS']) ? $this->vars['TOTAL_POSTS'] : $this->lang('TOTAL_POSTS'); ?>
			<?php if ($this->vars['PAGINATION']) {  ?>
				<?php  $this->set_filename('xs_include_b6e4e170968734161ec4bef894d97232', 'pagination.html', true);  $this->pparse('xs_include_b6e4e170968734161ec4bef894d97232');  ?>
			<?php } else { ?>
				&bull; <?php echo isset($this->vars['PAGE_NUMBER']) ? $this->vars['PAGE_NUMBER'] : $this->lang('PAGE_NUMBER'); ?>
			<?php } ?>
		</div>
	<?php } ?>
	<!-- EVENT viewtopic_body_pagination_top_after -->
</div>

<!-- EVENT viewtopic_body_poll_before -->

<?php if ($this->vars['S_HAS_POLL']) {  ?>
	<form method="post" action="<?php echo isset($this->vars['S_POLL_ACTION']) ? $this->vars['S_POLL_ACTION'] : $this->lang('S_POLL_ACTION'); ?>" data-ajax="vote_poll" class="topic_poll">

	<div class="panel">
		<div class="inner">

		<div class="content">
			<h2 class="poll-title"><!-- EVENT viewtopic_body_poll_question_prepend --><?php echo isset($this->vars['POLL_QUESTION']) ? $this->vars['POLL_QUESTION'] : $this->lang('POLL_QUESTION'); ?><!-- EVENT viewtopic_body_poll_question_append --></h2>
			<p class="author"><?php echo isset($this->vars['L_POLL_LENGTH']) ? $this->vars['L_POLL_LENGTH'] : $this->lang('L_POLL_LENGTH'); ?><?php if ($this->vars['S_CAN_VOTE'] && $this->vars['L_POLL_LENGTH']) {  ?><br /><?php } ?><?php if ($this->vars['S_CAN_VOTE']) {  ?><span class="poll_max_votes"><?php echo isset($this->vars['L_MAX_VOTES']) ? $this->vars['L_MAX_VOTES'] : $this->lang('L_MAX_VOTES'); ?></span><?php } ?></p>

			<fieldset class="polls">
			<?php

$poll_option_count = ( isset($this->_tpldata['poll_option.']) ) ?  sizeof($this->_tpldata['poll_option.']) : 0;
for ($poll_option_i = 0; $poll_option_i < $poll_option_count; $poll_option_i++)
{
 $poll_option_item = &$this->_tpldata['poll_option.'][$poll_option_i];
 $poll_option_item['S_ROW_COUNT'] = $poll_option_i;
 $poll_option_item['S_NUM_ROWS'] = $poll_option_count;

?>
				<!-- EVENT viewtopic_body_poll_option_before -->
				<dl class="<?php if ($poll_option_item['POLL_OPTION_VOTED']) {  ?>voted<?php } ?><?php if ($poll_option_item['POLL_OPTION_MOST_VOTES']) {  ?> most-votes<?php } ?>"<?php if ($poll_option_item['POLL_OPTION_VOTED']) {  ?> title="<?php echo isset($this->vars['L_POLL_VOTED_OPTION']) ? $this->vars['L_POLL_VOTED_OPTION'] : $this->lang('L_POLL_VOTED_OPTION'); ?>"<?php } ?> data-alt-text="<?php echo isset($this->vars['L_POLL_VOTED_OPTION']) ? $this->vars['L_POLL_VOTED_OPTION'] : $this->lang('L_POLL_VOTED_OPTION'); ?>" data-poll-option-id="<?php echo isset($poll_option_item['POLL_OPTION_ID']) ? $poll_option_item['POLL_OPTION_ID'] : ''; ?>">
					<dt><?php if ($this->vars['S_CAN_VOTE']) {  ?><label for="vote_<?php echo isset($poll_option_item['POLL_OPTION_ID']) ? $poll_option_item['POLL_OPTION_ID'] : ''; ?>"><?php echo isset($poll_option_item['POLL_OPTION_CAPTION']) ? $poll_option_item['POLL_OPTION_CAPTION'] : ''; ?></label><?php } else { ?><?php echo isset($poll_option_item['POLL_OPTION_CAPTION']) ? $poll_option_item['POLL_OPTION_CAPTION'] : ''; ?><?php } ?></dt>
					<?php if ($this->vars['S_CAN_VOTE']) {  ?><dd style="width: auto;" class="poll_option_select"><?php if ($this->vars['S_IS_MULTI_CHOICE']) {  ?><input type="checkbox" name="vote_id[]" id="vote_<?php echo isset($poll_option_item['POLL_OPTION_ID']) ? $poll_option_item['POLL_OPTION_ID'] : ''; ?>" value="<?php echo isset($poll_option_item['POLL_OPTION_ID']) ? $poll_option_item['POLL_OPTION_ID'] : ''; ?>"<?php if ($poll_option_item['POLL_OPTION_VOTED']) {  ?> checked="checked"<?php } ?> /><?php } else { ?><input type="radio" name="vote_id[]" id="vote_<?php echo isset($poll_option_item['POLL_OPTION_ID']) ? $poll_option_item['POLL_OPTION_ID'] : ''; ?>" value="<?php echo isset($poll_option_item['POLL_OPTION_ID']) ? $poll_option_item['POLL_OPTION_ID'] : ''; ?>"<?php if ($poll_option_item['POLL_OPTION_VOTED']) {  ?> checked="checked"<?php } ?> /><?php } ?></dd><?php } ?>
					<dd class="resultbar<?php if (! $this->vars['S_DISPLAY_RESULTS']) {  ?> hidden<?php } ?>"><div class="<?php if ($poll_option_item['POLL_OPTION_PCT'] < 20) {  ?>pollbar1<?php } elseif ($poll_option_item['POLL_OPTION_PCT'] < 40) {  ?>pollbar2<?php } elseif ($poll_option_item['POLL_OPTION_PCT'] < 60) {  ?>pollbar3<?php } elseif ($poll_option_item['POLL_OPTION_PCT'] < 80) {  ?>pollbar4<?php } else { ?>pollbar5<?php } ?>" style="width:<?php echo isset($poll_option_item['POLL_OPTION_PERCENT_REL']) ? $poll_option_item['POLL_OPTION_PERCENT_REL'] : ''; ?>;"><?php echo isset($poll_option_item['POLL_OPTION_RESULT']) ? $poll_option_item['POLL_OPTION_RESULT'] : ''; ?></div></dd>
					<dd class="poll_option_percent<?php if (! $this->vars['S_DISPLAY_RESULTS']) {  ?> hidden<?php } ?>"><?php if ($poll_option_item['POLL_OPTION_RESULT'] == 0) {  ?><?php echo isset($this->vars['L_NO_VOTES']) ? $this->vars['L_NO_VOTES'] : $this->lang('L_NO_VOTES'); ?><?php } else { ?><?php echo isset($poll_option_item['POLL_OPTION_PERCENT']) ? $poll_option_item['POLL_OPTION_PERCENT'] : ''; ?><?php } ?></dd>
				</dl>
				<!-- EVENT viewtopic_body_poll_option_after -->
			<?php

} // END poll_option

if(isset($poll_option_item)) { unset($poll_option_item); } 

?>

				<dl class="poll_total_votes<?php if (! $this->vars['S_DISPLAY_RESULTS']) {  ?> hidden<?php } ?>">
					<dt>&nbsp;</dt>
					<dd class="resultbar"><?php echo isset($this->vars['L_TOTAL_VOTES']) ? $this->vars['L_TOTAL_VOTES'] : $this->lang('L_TOTAL_VOTES'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <span class="poll_total_vote_cnt"><?php echo isset($this->vars['TOTAL_VOTES']) ? $this->vars['TOTAL_VOTES'] : $this->lang('TOTAL_VOTES'); ?></span></dd>
				</dl>

			<?php if ($this->vars['S_CAN_VOTE']) {  ?>
				<dl style="border-top: none;" class="poll_vote">
					<dt>&nbsp;</dt>
					<dd class="resultbar"><input type="submit" name="update" value="<?php echo isset($this->vars['L_SUBMIT_VOTE']) ? $this->vars['L_SUBMIT_VOTE'] : $this->lang('L_SUBMIT_VOTE'); ?>" class="button1" /></dd>
				</dl>
			<?php } ?>

			<?php if (! $this->vars['S_DISPLAY_RESULTS']) {  ?>
				<dl style="border-top: none;" class="poll_view_results">
					<dt>&nbsp;</dt>
					<dd class="resultbar"><a href="<?php echo isset($this->vars['U_VIEW_RESULTS']) ? $this->vars['U_VIEW_RESULTS'] : $this->lang('U_VIEW_RESULTS'); ?>"><?php echo isset($this->vars['L_VIEW_RESULTS']) ? $this->vars['L_VIEW_RESULTS'] : $this->lang('L_VIEW_RESULTS'); ?></a></dd>
				</dl>
			<?php } ?>
			</fieldset>
			<div class="vote-submitted hidden"><?php echo isset($this->vars['L_VOTE_SUBMITTED']) ? $this->vars['L_VOTE_SUBMITTED'] : $this->lang('L_VOTE_SUBMITTED'); ?></div>
		</div>

		</div>
		<?php echo isset($this->vars['S_FORM_TOKEN']) ? $this->vars['S_FORM_TOKEN'] : $this->lang('S_FORM_TOKEN'); ?>
		<?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?>
	</div>

	</form>
	<hr />
<?php } ?>

<!-- EVENT viewtopic_body_poll_after -->

<?php

$postrow_count = ( isset($this->_tpldata['postrow.']) ) ?  sizeof($this->_tpldata['postrow.']) : 0;
for ($postrow_i = 0; $postrow_i < $postrow_count; $postrow_i++)
{
 $postrow_item = &$this->_tpldata['postrow.'][$postrow_i];
 $postrow_item['S_ROW_COUNT'] = $postrow_i;
 $postrow_item['S_NUM_ROWS'] = $postrow_count;

?>
	<!-- EVENT viewtopic_body_postrow_post_before -->
	<?php if ($postrow_item['S_FIRST_UNREAD']) {  ?>
		<a id="unread" class="anchor"<?php if ($this->vars['S_UNREAD_VIEW']) {  ?> data-url="<?php echo isset($postrow_item['U_MINI_POST']) ? $postrow_item['U_MINI_POST'] : ''; ?>"<?php } ?>></a>
	<?php } ?>
	<div id="p<?php echo isset($postrow_item['POST_ID']) ? $postrow_item['POST_ID'] : ''; ?>" class="post has-profile <?php if (($postrow_item['S_ROW_COUNT'] %	2)) {  ?>bg1<?php } else { ?>bg2<?php } ?><?php if ($postrow_item['S_UNREAD_POST']) {  ?> unreadpost<?php } ?><?php if ($postrow_item['S_POST_REPORTED']) {  ?> reported<?php } ?><?php if ($postrow_item['S_POST_DELETED']) {  ?> deleted<?php } ?><?php if ($postrow_item['S_ONLINE'] && ! $postrow_item['S_POST_HIDDEN']) {  ?> online<?php } ?><?php if ($postrow_item['POSTER_WARNINGS']) {  ?> warned<?php } ?>">
		<div class="inner">

		<dl class="postprofile" id="profile<?php echo isset($postrow_item['POST_ID']) ? $postrow_item['POST_ID'] : ''; ?>"<?php if ($postrow_item['S_POST_HIDDEN']) {  ?> style="display: none;"<?php } ?>>
			<dt class="<?php if ($postrow_item['RANK_TITLE'] || $postrow_item['RANK_IMG']) {  ?>has-profile-rank<?php } else { ?>no-profile-rank<?php } ?> <?php if ($postrow_item['POSTER_AVATAR']) {  ?>has-avatar<?php } else { ?>no-avatar<?php } ?>">
				<div class="avatar-container">
					<!-- EVENT viewtopic_body_avatar_before -->
					<?php if ($postrow_item['POSTER_AVATAR']) {  ?>
						<?php if ($postrow_item['U_POST_AUTHOR']) {  ?><a href="<?php echo isset($postrow_item['U_POST_AUTHOR']) ? $postrow_item['U_POST_AUTHOR'] : ''; ?>" class="avatar"><?php echo isset($postrow_item['POSTER_AVATAR']) ? $postrow_item['POSTER_AVATAR'] : ''; ?></a><?php } else { ?><span class="avatar"><?php echo isset($postrow_item['POSTER_AVATAR']) ? $postrow_item['POSTER_AVATAR'] : ''; ?></span><?php } ?>
					<?php } ?>
					<!-- EVENT viewtopic_body_avatar_after -->
				</div>
				<!-- EVENT viewtopic_body_post_author_before -->
				<?php if (! $postrow_item['U_POST_AUTHOR']) {  ?><strong><?php echo isset($postrow_item['POST_AUTHOR_FULL']) ? $postrow_item['POST_AUTHOR_FULL'] : ''; ?></strong><?php } else { ?><?php echo isset($postrow_item['POST_AUTHOR_FULL']) ? $postrow_item['POST_AUTHOR_FULL'] : ''; ?><?php } ?>
				<!-- EVENT viewtopic_body_post_author_after -->
			</dt>

			<!-- EVENT viewtopic_body_postrow_rank_before -->
			<?php if ($postrow_item['RANK_TITLE'] || $postrow_item['RANK_IMG']) {  ?><dd class="profile-rank"><?php echo isset($postrow_item['RANK_TITLE']) ? $postrow_item['RANK_TITLE'] : ''; ?><?php if ($postrow_item['RANK_TITLE'] && $postrow_item['RANK_IMG']) {  ?><br /><?php } ?><?php echo isset($postrow_item['RANK_IMG']) ? $postrow_item['RANK_IMG'] : ''; ?></dd><?php } ?>
			<!-- EVENT viewtopic_body_postrow_rank_after -->

		<?php if ($postrow_item['POSTER_POSTS'] != '') {  ?><dd class="profile-posts"><strong><?php echo isset($this->vars['L_POSTS']) ? $this->vars['L_POSTS'] : $this->lang('L_POSTS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <?php if ($postrow_item['U_SEARCH'] !== '') {  ?><a href="<?php echo isset($postrow_item['U_SEARCH']) ? $postrow_item['U_SEARCH'] : ''; ?>"><?php } ?><?php echo isset($postrow_item['POSTER_POSTS']) ? $postrow_item['POSTER_POSTS'] : ''; ?><?php if ($postrow_item['U_SEARCH'] !== '') {  ?></a><?php } ?></dd><?php } ?>
		<?php if ($postrow_item['POSTER_JOINED']) {  ?><dd class="profile-joined"><strong><?php echo isset($this->vars['L_JOINED']) ? $this->vars['L_JOINED'] : $this->lang('L_JOINED'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <?php echo isset($postrow_item['POSTER_JOINED']) ? $postrow_item['POSTER_JOINED'] : ''; ?></dd><?php } ?>
		<?php if ($postrow_item['POSTER_WARNINGS']) {  ?><dd class="profile-warnings"><strong><?php echo isset($this->vars['L_WARNINGS']) ? $this->vars['L_WARNINGS'] : $this->lang('L_WARNINGS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <?php echo isset($postrow_item['POSTER_WARNINGS']) ? $postrow_item['POSTER_WARNINGS'] : ''; ?></dd><?php } ?>

		<?php if ($postrow_item['S_PROFILE_FIELD1']) {  ?>
			<!-- Use a construct like this to include admin defined profile fields. Replace FIELD1 with the name of your field. -->
			<dd><strong><?php echo isset($postrow_item['PROFILE_FIELD1_NAME']) ? $postrow_item['PROFILE_FIELD1_NAME'] : ''; ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <?php echo isset($postrow_item['PROFILE_FIELD1_VALUE']) ? $postrow_item['PROFILE_FIELD1_VALUE'] : ''; ?></dd>
		<?php } ?>

		<!-- EVENT viewtopic_body_postrow_custom_fields_before -->
		<?php

$custom_fields_count = ( isset($postrow_item['custom_fields.']) ) ? sizeof($postrow_item['custom_fields.']) : 0;
for ($custom_fields_i = 0; $custom_fields_i < $custom_fields_count; $custom_fields_i++)
{
 $custom_fields_item = &$postrow_item['custom_fields.'][$custom_fields_i];
 $custom_fields_item['S_ROW_COUNT'] = $custom_fields_i;
 $custom_fields_item['S_NUM_ROWS'] = $custom_fields_count;

?>
			<?php if (! $custom_fields_item['S_PROFILE_CONTACT']) {  ?>
				<dd class="profile-custom-field profile-<?php echo isset($custom_fields_item['PROFILE_FIELD_IDENT']) ? $custom_fields_item['PROFILE_FIELD_IDENT'] : ''; ?>"><strong><?php echo isset($custom_fields_item['PROFILE_FIELD_NAME']) ? $custom_fields_item['PROFILE_FIELD_NAME'] : ''; ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <?php echo isset($custom_fields_item['PROFILE_FIELD_VALUE']) ? $custom_fields_item['PROFILE_FIELD_VALUE'] : ''; ?></dd>
			<?php } ?>
		<?php

} // END custom_fields

if(isset($custom_fields_item)) { unset($custom_fields_item); } 

?>
		<!-- EVENT viewtopic_body_postrow_custom_fields_after -->

		<!-- EVENT viewtopic_body_contact_fields_before -->
		<?php if (! $this->vars['S_IS_BOT'] && $postrow_item['contact.']) {  ?>
			<dd class="profile-contact">
				<strong><?php echo isset($this->vars['L_CONTACT']) ? $this->vars['L_CONTACT'] : $this->lang('L_CONTACT'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong>
				<div class="dropdown-container dropdown-left">
					<a href="#" class="dropdown-trigger"><span class="imageset icon_contact" title="<?php echo isset($postrow_item['CONTACT_USER']) ? $postrow_item['CONTACT_USER'] : ''; ?>"><?php echo isset($postrow_item['CONTACT_USER']) ? $postrow_item['CONTACT_USER'] : ''; ?></span></a>
					<div class="dropdown hidden">
						<div class="pointer"><div class="pointer-inner"></div></div>
						<div class="dropdown-contents contact-icons">
							<?php

$contact_count = ( isset($postrow_item['contact.']) ) ? sizeof($postrow_item['contact.']) : 0;
for ($contact_i = 0; $contact_i < $contact_count; $contact_i++)
{
 $contact_item = &$postrow_item['contact.'][$contact_i];
 $contact_item['S_ROW_COUNT'] = $contact_i;
 $contact_item['S_NUM_ROWS'] = $contact_count;

?>
								<!-- SET REMAINDER = postrow.contact.S_ROW_COUNT -- 4 -->
								<?php $this->_tpldata['DEFINE']['.']['S_LAST_CELL'] = 0; ?>
								<?php if ($this->vars['REMAINDER'] == 0) {  ?>
									<div>
								<?php } ?>
									<a href="<?php if ($contact_item['U_CONTACT']) {  ?><?php echo isset($contact_item['U_CONTACT']) ? $contact_item['U_CONTACT'] : ''; ?><?php } else { ?><?php echo isset($postrow_item['U_POST_AUTHOR']) ? $postrow_item['U_POST_AUTHOR'] : ''; ?><?php } ?>" title="<?php echo isset($contact_item['NAME']) ? $contact_item['NAME'] : ''; ?>"<?php if ($this->_tpldata['DEFINE']['.']['S_LAST_CELL']) {  ?> class="last-cell"<?php } ?><?php if ($contact_item['ID'] == 'jabber') {  ?> onclick="popup(this.href, 750, 320); return false;"<?php } ?>>
										<span class="contact-icon <?php echo isset($contact_item['ID']) ? $contact_item['ID'] : ''; ?>-icon"><?php echo isset($contact_item['NAME']) ? $contact_item['NAME'] : ''; ?></span>
									</a>
								<?php if ($this->vars['REMAINDER'] == 3 || $contact_item['S_LAST_ROW']) {  ?>
									</div>
								<?php } ?>
							<?php

} // END contact

if(isset($contact_item)) { unset($contact_item); } 

?>
						</div>
					</div>
				</div>
			</dd>
		<?php } ?>
		<!-- EVENT viewtopic_body_contact_fields_after -->

		</dl>

		<div class="postbody">
			<?php if ($postrow_item['S_POST_HIDDEN']) {  ?>
				<?php if ($postrow_item['S_POST_DELETED']) {  ?>
					<div class="ignore" id="post_hidden<?php echo isset($postrow_item['POST_ID']) ? $postrow_item['POST_ID'] : ''; ?>">
						<?php echo isset($postrow_item['L_POST_DELETED_MESSAGE']) ? $postrow_item['L_POST_DELETED_MESSAGE'] : ''; ?><br />
						<?php echo isset($postrow_item['L_POST_DISPLAY']) ? $postrow_item['L_POST_DISPLAY'] : ''; ?>
					</div>
				<?php } elseif ($postrow_item['S_IGNORE_POST']) {  ?>
					<div class="ignore" id="post_hidden<?php echo isset($postrow_item['POST_ID']) ? $postrow_item['POST_ID'] : ''; ?>">
						<?php echo isset($postrow_item['L_IGNORE_POST']) ? $postrow_item['L_IGNORE_POST'] : ''; ?><br />
						<?php echo isset($postrow_item['L_POST_DISPLAY']) ? $postrow_item['L_POST_DISPLAY'] : ''; ?>
					</div>
				<?php } ?>
			<?php } ?>
			<div id="post_content<?php echo isset($postrow_item['POST_ID']) ? $postrow_item['POST_ID'] : ''; ?>"<?php if ($postrow_item['S_POST_HIDDEN']) {  ?> style="display: none;"<?php } ?>>

			<!-- EVENT viewtopic_body_post_subject_before -->
			<h3 <?php if ($postrow_item['S_FIRST_ROW']) {  ?>class="first"<?php } ?>><?php if ($postrow_item['POST_ICON_IMG']) {  ?><img src="<?php echo isset($this->vars['T_ICONS_PATH']) ? $this->vars['T_ICONS_PATH'] : $this->lang('T_ICONS_PATH'); ?><?php echo isset($postrow_item['POST_ICON_IMG']) ? $postrow_item['POST_ICON_IMG'] : ''; ?>" width="<?php echo isset($postrow_item['POST_ICON_IMG_WIDTH']) ? $postrow_item['POST_ICON_IMG_WIDTH'] : ''; ?>" height="<?php echo isset($postrow_item['POST_ICON_IMG_HEIGHT']) ? $postrow_item['POST_ICON_IMG_HEIGHT'] : ''; ?>" alt="" /> <?php } ?><a href="#p<?php echo isset($postrow_item['POST_ID']) ? $postrow_item['POST_ID'] : ''; ?>"><?php echo isset($postrow_item['POST_SUBJECT']) ? $postrow_item['POST_SUBJECT'] : ''; ?></a></h3>

		<?php $this->_tpldata['DEFINE']['.']['SHOW_POST_BUTTONS'] = 0; ?>
		<!-- EVENT viewtopic_body_post_buttons_list_before -->
		<?php if (! $this->vars['S_IS_BOT']) {  ?>
			<?php if ($this->_tpldata['DEFINE']['.']['SHOW_POST_BUTTONS']) {  ?>
				<ul class="post-buttons">
					<!-- EVENT viewtopic_body_post_buttons_before -->
					<?php if ($postrow_item['U_EDIT']) {  ?>
						<li>
							<a href="<?php echo isset($postrow_item['U_EDIT']) ? $postrow_item['U_EDIT'] : ''; ?>" title="<?php echo isset($this->vars['L_EDIT_POST']) ? $this->vars['L_EDIT_POST'] : $this->lang('L_EDIT_POST'); ?>" class="button icon-button edit-icon"><span><?php echo isset($this->vars['L_BUTTON_EDIT']) ? $this->vars['L_BUTTON_EDIT'] : $this->lang('L_BUTTON_EDIT'); ?></span></a>
						</li>
					<?php } ?>
					<?php if ($postrow_item['U_DELETE']) {  ?>
						<li>
							<a href="<?php echo isset($postrow_item['U_DELETE']) ? $postrow_item['U_DELETE'] : ''; ?>" title="<?php echo isset($this->vars['L_DELETE_POST']) ? $this->vars['L_DELETE_POST'] : $this->lang('L_DELETE_POST'); ?>" class="button icon-button delete-icon"><span><?php echo isset($this->vars['L_DELETE_POST']) ? $this->vars['L_DELETE_POST'] : $this->lang('L_DELETE_POST'); ?></span></a>
						</li>
					<?php } ?>
					<?php if ($postrow_item['U_REPORT']) {  ?>
						<li>
							<a href="<?php echo isset($postrow_item['U_REPORT']) ? $postrow_item['U_REPORT'] : ''; ?>" title="<?php echo isset($this->vars['L_REPORT_POST']) ? $this->vars['L_REPORT_POST'] : $this->lang('L_REPORT_POST'); ?>" class="button icon-button report-icon"><span><?php echo isset($this->vars['L_REPORT_POST']) ? $this->vars['L_REPORT_POST'] : $this->lang('L_REPORT_POST'); ?></span></a>
						</li>
					<?php } ?>
					<?php if ($postrow_item['U_WARN']) {  ?>
						<li>
							<a href="<?php echo isset($postrow_item['U_WARN']) ? $postrow_item['U_WARN'] : ''; ?>" title="<?php echo isset($this->vars['L_WARN_USER']) ? $this->vars['L_WARN_USER'] : $this->lang('L_WARN_USER'); ?>" class="button icon-button warn-icon"><span><?php echo isset($this->vars['L_WARN_USER']) ? $this->vars['L_WARN_USER'] : $this->lang('L_WARN_USER'); ?></span></a>
						</li>
					<?php } ?>
					<?php if ($postrow_item['U_INFO']) {  ?>
						<li>
							<a href="<?php echo isset($postrow_item['U_INFO']) ? $postrow_item['U_INFO'] : ''; ?>" title="<?php echo isset($this->vars['L_INFORMATION']) ? $this->vars['L_INFORMATION'] : $this->lang('L_INFORMATION'); ?>" class="button icon-button info-icon"><span><?php echo isset($this->vars['L_INFORMATION']) ? $this->vars['L_INFORMATION'] : $this->lang('L_INFORMATION'); ?></span></a>
						</li>
					<?php } ?>
					<?php if ($postrow_item['U_QUOTE']) {  ?>
						<li>
							<a href="<?php echo isset($postrow_item['U_QUOTE']) ? $postrow_item['U_QUOTE'] : ''; ?>" title="<?php echo isset($this->vars['L_REPLY_WITH_QUOTE']) ? $this->vars['L_REPLY_WITH_QUOTE'] : $this->lang('L_REPLY_WITH_QUOTE'); ?>" class="button icon-button quote-icon"><span><?php echo isset($this->vars['L_QUOTE']) ? $this->vars['L_QUOTE'] : $this->lang('L_QUOTE'); ?></span></a>
						</li>
					<?php } ?>
					<!-- EVENT viewtopic_body_post_buttons_after -->
				</ul>
			<?php } ?>
		<?php } ?>
		<!-- EVENT viewtopic_body_post_buttons_list_after -->

			<!-- EVENT viewtopic_body_postrow_post_details_before -->
			<p class="author"><?php if ($this->vars['S_IS_BOT']) {  ?><?php echo isset($postrow_item['MINI_POST_IMG']) ? $postrow_item['MINI_POST_IMG'] : ''; ?><?php } else { ?><a href="<?php echo isset($postrow_item['U_MINI_POST']) ? $postrow_item['U_MINI_POST'] : ''; ?>"><?php echo isset($postrow_item['MINI_POST_IMG']) ? $postrow_item['MINI_POST_IMG'] : ''; ?></a><?php } ?><span class="responsive-hide"><?php echo isset($this->vars['L_POST_BY_AUTHOR']) ? $this->vars['L_POST_BY_AUTHOR'] : $this->lang('L_POST_BY_AUTHOR'); ?> <strong><?php echo isset($postrow_item['POST_AUTHOR_FULL']) ? $postrow_item['POST_AUTHOR_FULL'] : ''; ?></strong> &raquo; </span><?php echo isset($postrow_item['POST_DATE']) ? $postrow_item['POST_DATE'] : ''; ?> </p>
			<!-- EVENT viewtopic_body_postrow_post_details_after -->

			<?php if ($postrow_item['S_POST_UNAPPROVED']) {  ?>
			<form method="post" class="mcp_approve" action="<?php echo isset($postrow_item['U_APPROVE_ACTION']) ? $postrow_item['U_APPROVE_ACTION'] : ''; ?>">
				<p class="post-notice unapproved">
					<strong><?php echo isset($this->vars['L_POST_UNAPPROVED_ACTION']) ? $this->vars['L_POST_UNAPPROVED_ACTION'] : $this->lang('L_POST_UNAPPROVED_ACTION'); ?></strong>
					<input class="button2" type="submit" value="<?php echo isset($this->vars['L_DISAPPROVE']) ? $this->vars['L_DISAPPROVE'] : $this->lang('L_DISAPPROVE'); ?>" name="action[disapprove]" />
					<input class="button1" type="submit" value="<?php echo isset($this->vars['L_APPROVE']) ? $this->vars['L_APPROVE'] : $this->lang('L_APPROVE'); ?>" name="action[approve]" />
					<input type="hidden" name="post_id_list[]" value="<?php echo isset($postrow_item['POST_ID']) ? $postrow_item['POST_ID'] : ''; ?>" />
					<?php echo isset($this->vars['S_FORM_TOKEN']) ? $this->vars['S_FORM_TOKEN'] : $this->lang('S_FORM_TOKEN'); ?>
				</p>
			</form>
			<?php } elseif ($postrow_item['S_POST_DELETED']) {  ?>
			<form method="post" class="mcp_approve" action="<?php echo isset($postrow_item['U_APPROVE_ACTION']) ? $postrow_item['U_APPROVE_ACTION'] : ''; ?>">
				<p class="post-notice deleted">
					<strong><?php echo isset($this->vars['L_POST_DELETED_ACTION']) ? $this->vars['L_POST_DELETED_ACTION'] : $this->lang('L_POST_DELETED_ACTION'); ?></strong>
					<?php if ($postrow_item['S_DELETE_PERMANENT']) {  ?>
						<input class="button2" type="submit" value="<?php echo isset($this->vars['L_DELETE']) ? $this->vars['L_DELETE'] : $this->lang('L_DELETE'); ?>" name="action[delete]" />
					<?php } ?>
					<input class="button1" type="submit" value="<?php echo isset($this->vars['L_RESTORE']) ? $this->vars['L_RESTORE'] : $this->lang('L_RESTORE'); ?>" name="action[restore]" />
					<input type="hidden" name="post_id_list[]" value="<?php echo isset($postrow_item['POST_ID']) ? $postrow_item['POST_ID'] : ''; ?>" />
					<?php echo isset($this->vars['S_FORM_TOKEN']) ? $this->vars['S_FORM_TOKEN'] : $this->lang('S_FORM_TOKEN'); ?>
				</p>
			</form>
			<?php } ?>

			<?php if ($postrow_item['S_POST_REPORTED']) {  ?>
			<p class="post-notice reported">
				<a href="<?php echo isset($postrow_item['U_MCP_REPORT']) ? $postrow_item['U_MCP_REPORT'] : ''; ?>"><strong><?php echo isset($this->vars['L_POST_REPORTED']) ? $this->vars['L_POST_REPORTED'] : $this->lang('L_POST_REPORTED'); ?></strong></a>
			</p>
			<?php } ?>

			<div class="content"><?php echo isset($postrow_item['MESSAGE']) ? $postrow_item['MESSAGE'] : ''; ?></div>

			<?php if ($postrow_item['S_HAS_ATTACHMENTS']) {  ?>
				<dl class="attachbox">
					<dt>
						<?php echo isset($this->vars['L_ATTACHMENTS']) ? $this->vars['L_ATTACHMENTS'] : $this->lang('L_ATTACHMENTS'); ?>
					</dt>
					<?php

$attachment_count = ( isset($postrow_item['attachment.']) ) ? sizeof($postrow_item['attachment.']) : 0;
for ($attachment_i = 0; $attachment_i < $attachment_count; $attachment_i++)
{
 $attachment_item = &$postrow_item['attachment.'][$attachment_i];
 $attachment_item['S_ROW_COUNT'] = $attachment_i;
 $attachment_item['S_NUM_ROWS'] = $attachment_count;

?>
						<dd><?php echo isset($attachment_item['DISPLAY_ATTACHMENT']) ? $attachment_item['DISPLAY_ATTACHMENT'] : ''; ?></dd>
					<?php

} // END attachment

if(isset($attachment_item)) { unset($attachment_item); } 

?>
				</dl>
			<?php } ?>

			<!-- EVENT viewtopic_body_postrow_post_notices_before -->
			<?php if ($postrow_item['S_DISPLAY_NOTICE']) {  ?><div class="rules"><?php echo isset($this->vars['L_DOWNLOAD_NOTICE']) ? $this->vars['L_DOWNLOAD_NOTICE'] : $this->lang('L_DOWNLOAD_NOTICE'); ?></div><?php } ?>
			<?php if ($postrow_item['DELETED_MESSAGE'] || $postrow_item['DELETE_REASON']) {  ?>
				<div class="notice post_deleted_msg">
					<?php echo isset($postrow_item['DELETED_MESSAGE']) ? $postrow_item['DELETED_MESSAGE'] : ''; ?>
					<?php if ($postrow_item['DELETE_REASON']) {  ?><br /><strong><?php echo isset($this->vars['L_REASON']) ? $this->vars['L_REASON'] : $this->lang('L_REASON'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <em><?php echo isset($postrow_item['DELETE_REASON']) ? $postrow_item['DELETE_REASON'] : ''; ?></em><?php } ?>
				</div>
			<?php } elseif ($postrow_item['EDITED_MESSAGE'] || $postrow_item['EDIT_REASON']) {  ?>
				<div class="notice">
					<?php echo isset($postrow_item['EDITED_MESSAGE']) ? $postrow_item['EDITED_MESSAGE'] : ''; ?>
					<?php if ($postrow_item['EDIT_REASON']) {  ?><br /><strong><?php echo isset($this->vars['L_REASON']) ? $this->vars['L_REASON'] : $this->lang('L_REASON'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></strong> <em><?php echo isset($postrow_item['EDIT_REASON']) ? $postrow_item['EDIT_REASON'] : ''; ?></em><?php } ?>
				</div>
			<?php } ?>

			<?php if ($postrow_item['BUMPED_MESSAGE']) {  ?><div class="notice"><br /><br /><?php echo isset($postrow_item['BUMPED_MESSAGE']) ? $postrow_item['BUMPED_MESSAGE'] : ''; ?></div><?php } ?>
			<!-- EVENT viewtopic_body_postrow_post_notices_after -->
			<?php if ($postrow_item['SIGNATURE']) {  ?><div id="sig<?php echo isset($postrow_item['POST_ID']) ? $postrow_item['POST_ID'] : ''; ?>" class="signature"><?php echo isset($postrow_item['SIGNATURE']) ? $postrow_item['SIGNATURE'] : ''; ?></div><?php } ?>

			<!-- EVENT viewtopic_body_postrow_post_content_footer -->
			</div>

		</div>

		<!-- EVENT viewtopic_body_postrow_back2top_before -->
		<div class="back2top"><!-- EVENT viewtopic_body_postrow_back2top_prepend --><a href="#top" class="top" title="<?php echo isset($this->vars['L_BACK_TO_TOP']) ? $this->vars['L_BACK_TO_TOP'] : $this->lang('L_BACK_TO_TOP'); ?>"><?php echo isset($this->vars['L_BACK_TO_TOP']) ? $this->vars['L_BACK_TO_TOP'] : $this->lang('L_BACK_TO_TOP'); ?></a><!-- EVENT viewtopic_body_postrow_back2top_append --></div>
		<!-- EVENT viewtopic_body_postrow_back2top_after -->

		</div>
	</div>

	<hr class="divider" />
	<!-- EVENT viewtopic_body_postrow_post_after -->
<?php

} // END postrow

if(isset($postrow_item)) { unset($postrow_item); } 

?>

<?php if ($this->vars['S_QUICK_REPLY']) {  ?>
	<?php  $this->set_filename('xs_include_1115da1c822e3f5c354a960d7039a612', 'quickreply_editor.html', true);  $this->pparse('xs_include_1115da1c822e3f5c354a960d7039a612');  ?>
<?php } ?>

<?php if ($this->vars['S_NUM_POSTS'] > 1 || $this->vars['PAGINATION']) {  ?>
	<form id="viewtopic" method="post" action="<?php echo isset($this->vars['S_TOPIC_ACTION']) ? $this->vars['S_TOPIC_ACTION'] : $this->lang('S_TOPIC_ACTION'); ?>">
	<fieldset class="display-options" style="margin-top: 0; ">
		<?php if (! $this->vars['S_IS_BOT']) {  ?>
		<label><?php echo isset($this->vars['L_DISPLAY_POSTS']) ? $this->vars['L_DISPLAY_POSTS'] : $this->lang('L_DISPLAY_POSTS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <?php echo isset($this->vars['S_SELECT_SORT_DAYS']) ? $this->vars['S_SELECT_SORT_DAYS'] : $this->lang('S_SELECT_SORT_DAYS'); ?></label>
		<label><?php echo isset($this->vars['L_SORT_BY']) ? $this->vars['L_SORT_BY'] : $this->lang('L_SORT_BY'); ?> <?php echo isset($this->vars['S_SELECT_SORT_KEY']) ? $this->vars['S_SELECT_SORT_KEY'] : $this->lang('S_SELECT_SORT_KEY'); ?></label> <label><?php echo isset($this->vars['S_SELECT_SORT_DIR']) ? $this->vars['S_SELECT_SORT_DIR'] : $this->lang('S_SELECT_SORT_DIR'); ?></label>
		<input type="submit" name="sort" value="<?php echo isset($this->vars['L_GO']) ? $this->vars['L_GO'] : $this->lang('L_GO'); ?>" class="button2" />
		<?php } ?>
	</fieldset>
	</form>
	<hr />
<?php } ?>

<!-- EVENT viewtopic_body_topic_actions_before -->
<div class="action-bar bottom">
	<div class="buttons">
		<!-- EVENT viewtopic_buttons_bottom_before -->

	<?php if (! $this->vars['S_IS_BOT'] && $this->vars['S_DISPLAY_REPLY_INFO']) {  ?>
		<a href="<?php echo isset($this->vars['U_POST_REPLY_TOPIC']) ? $this->vars['U_POST_REPLY_TOPIC'] : $this->lang('U_POST_REPLY_TOPIC'); ?>" class="button icon-button <?php if ($this->vars['S_IS_LOCKED']) {  ?>locked-icon<?php } else { ?>reply-icon<?php } ?>" title="<?php if ($this->vars['S_IS_LOCKED']) {  ?><?php echo isset($this->vars['L_TOPIC_LOCKED']) ? $this->vars['L_TOPIC_LOCKED'] : $this->lang('L_TOPIC_LOCKED'); ?><?php } else { ?><?php echo isset($this->vars['L_POST_REPLY']) ? $this->vars['L_POST_REPLY'] : $this->lang('L_POST_REPLY'); ?><?php } ?>">
			<?php if ($this->vars['S_IS_LOCKED']) {  ?><?php echo isset($this->vars['L_BUTTON_TOPIC_LOCKED']) ? $this->vars['L_BUTTON_TOPIC_LOCKED'] : $this->lang('L_BUTTON_TOPIC_LOCKED'); ?><?php } else { ?><?php echo isset($this->vars['L_BUTTON_POST_REPLY']) ? $this->vars['L_BUTTON_POST_REPLY'] : $this->lang('L_BUTTON_POST_REPLY'); ?><?php } ?>
		</a>
	<?php } ?>

		<!-- EVENT viewtopic_buttons_bottom_after -->
	</div>

	<?php  $this->set_filename('xs_include_fb8e986bfe395440deb14c3ce8dad406', 'viewtopic_topic_tools.html', true);  $this->pparse('xs_include_fb8e986bfe395440deb14c3ce8dad406');  ?>

	<?php if ($this->vars['QUICKMOD']) {  ?>
		<div class="dropdown-container dropdown-container-<?php echo isset($this->vars['S_CONTENT_FLOW_BEGIN']) ? $this->vars['S_CONTENT_FLOW_BEGIN'] : $this->lang('S_CONTENT_FLOW_BEGIN'); ?> dropdown-up dropdown-<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?> dropdown-button-control" id="quickmod">
			<span title="<?php echo isset($this->vars['L_QUICK_MOD']) ? $this->vars['L_QUICK_MOD'] : $this->lang('L_QUICK_MOD'); ?>" class="dropdown-trigger button icon-button modtools-icon dropdown-select"><?php echo isset($this->vars['L_QUICK_MOD']) ? $this->vars['L_QUICK_MOD'] : $this->lang('L_QUICK_MOD'); ?></span>
			<div class="dropdown hidden">
				<div class="pointer"><div class="pointer-inner"></div></div>
				<ul class="dropdown-contents">
				<?php

$quickmod_count = ( isset($this->_tpldata['quickmod.']) ) ?  sizeof($this->_tpldata['quickmod.']) : 0;
for ($quickmod_i = 0; $quickmod_i < $quickmod_count; $quickmod_i++)
{
 $quickmod_item = &$this->_tpldata['quickmod.'][$quickmod_i];
 $quickmod_item['S_ROW_COUNT'] = $quickmod_i;
 $quickmod_item['S_NUM_ROWS'] = $quickmod_count;

?>
					<?php $this->_tpldata['DEFINE']['.']['QUICKMOD_AJAX'] = 0; ?>
					<li><a href="<?php echo isset($quickmod_item['LINK']) ? $quickmod_item['LINK'] : ''; ?>"<?php if ($this->_tpldata['DEFINE']['.']['QUICKMOD_AJAX']) {  ?> data-ajax="true" data-refresh="true"<?php } ?>><?php echo isset($quickmod_item['TITLE']) ? $quickmod_item['TITLE'] : ''; ?></a></li>
				<?php

} // END quickmod

if(isset($quickmod_item)) { unset($quickmod_item); } 

?>
				</ul>
			</div>
		</div>
	<?php } ?>
	
	<!-- EVENT viewtopic_dropdown_bottom_custom -->

	<?php if ($this->vars['PAGINATION'] || $this->vars['TOTAL_POSTS']) {  ?>
		<div class="pagination">
			<?php echo isset($this->vars['TOTAL_POSTS']) ? $this->vars['TOTAL_POSTS'] : $this->lang('TOTAL_POSTS'); ?>
			<?php if ($this->vars['PAGINATION']) {  ?>
				<?php  $this->set_filename('xs_include_13a40e1d67460d6ca5aa35c6b168136e', 'pagination.html', true);  $this->pparse('xs_include_13a40e1d67460d6ca5aa35c6b168136e');  ?>
			<?php } else { ?>
				&bull; <?php echo isset($this->vars['PAGE_NUMBER']) ? $this->vars['PAGE_NUMBER'] : $this->lang('PAGE_NUMBER'); ?>
			<?php } ?>
		</div>
	<?php } ?>
	<div class="clear"></div>
</div>

<!-- EVENT viewtopic_body_footer_before -->
<?php  $this->set_filename('xs_include_b8dcc5078b21576c527985e5dc97c8d2', 'jumpbox.html', true);  $this->pparse('xs_include_b8dcc5078b21576c527985e5dc97c8d2');  ?>

<?php if ($this->vars['S_DISPLAY_ONLINE_LIST'] && $this->vars['U_VIEWONLINE']) {  ?>
	<div class="stat-block online-list">
		<h3><a href="<?php echo isset($this->vars['U_VIEWONLINE']) ? $this->vars['U_VIEWONLINE'] : $this->lang('U_VIEWONLINE'); ?>"><?php echo isset($this->vars['L_WHO_IS_ONLINE']) ? $this->vars['L_WHO_IS_ONLINE'] : $this->lang('L_WHO_IS_ONLINE'); ?></a></h3>
		<p><?php echo isset($this->vars['LOGGED_IN_USER_LIST']) ? $this->vars['LOGGED_IN_USER_LIST'] : $this->lang('LOGGED_IN_USER_LIST'); ?></p>
	</div>
<?php } ?>

<!-- INCLUDEX overall_footer.html -->
