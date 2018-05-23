<?php

// eXtreme Styles mod cache. Generated on Fri, 18 May 2018 18:23:35 +0000 (time=1526667815)

?><!-- INCLUDEX overall_header.html -->

<p class="<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?> responsive-center time<?php if ($this->vars['S_USER_LOGGED_IN']) {  ?> rightside<?php } ?>"><?php if ($this->vars['S_USER_LOGGED_IN']) {  ?><?php echo isset($this->vars['LAST_VISIT_DATE']) ? $this->vars['LAST_VISIT_DATE'] : $this->lang('LAST_VISIT_DATE'); ?><?php } else { ?><?php echo isset($this->vars['CURRENT_TIME']) ? $this->vars['CURRENT_TIME'] : $this->lang('CURRENT_TIME'); ?><?php } ?></p>
<?php if ($this->vars['S_USER_LOGGED_IN']) {  ?><p class="responsive-center time"><?php echo isset($this->vars['CURRENT_TIME']) ? $this->vars['CURRENT_TIME'] : $this->lang('CURRENT_TIME'); ?></p><?php } ?>

<!-- EVENT index_body_markforums_before -->
<?php if ($this->vars['U_MARK_FORUMS']) {  ?>
	<div class="action-bar compact">
		<a href="<?php echo isset($this->vars['U_MARK_FORUMS']) ? $this->vars['U_MARK_FORUMS'] : $this->lang('U_MARK_FORUMS'); ?>" class="mark-read rightside" accesskey="m" data-ajax="mark_forums_read"><?php echo isset($this->vars['L_MARK_FORUMS_READ']) ? $this->vars['L_MARK_FORUMS_READ'] : $this->lang('L_MARK_FORUMS_READ'); ?></a>
	</div>
<?php } ?>
<!-- EVENT index_body_markforums_after -->

<?php  $this->set_filename('xs_include_a617bcd01ef3024d0f09bacee87adfe2', 'forumlist_body.html', true);  $this->pparse('xs_include_a617bcd01ef3024d0f09bacee87adfe2');  ?>

<!-- EVENT index_body_forumlist_body_after -->

<?php if (! $this->vars['S_USER_LOGGED_IN'] && ! $this->vars['S_IS_BOT']) {  ?>
	<form method="post" action="<?php echo isset($this->vars['S_LOGIN_ACTION']) ? $this->vars['S_LOGIN_ACTION'] : $this->lang('S_LOGIN_ACTION'); ?>" class="headerspace">
	<h3><a href="<?php echo isset($this->vars['U_LOGIN_LOGOUT']) ? $this->vars['U_LOGIN_LOGOUT'] : $this->lang('U_LOGIN_LOGOUT'); ?>"><?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?></a><?php if ($this->vars['S_REGISTER_ENABLED']) {  ?>&nbsp; &bull; &nbsp;<a href="<?php echo isset($this->vars['U_REGISTER']) ? $this->vars['U_REGISTER'] : $this->lang('U_REGISTER'); ?>"><?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?></a><?php } ?></h3>
		<fieldset class="quick-login">
			<label for="username"><span><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></span> <input type="text" tabindex="1" name="username" id="username" size="10" class="inputbox" title="<?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?>" /></label>
			<label for="password"><span><?php echo isset($this->vars['L_PASSWORD']) ? $this->vars['L_PASSWORD'] : $this->lang('L_PASSWORD'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></span> <input type="password" tabindex="2" name="password" id="password" size="10" class="inputbox" title="<?php echo isset($this->vars['L_PASSWORD']) ? $this->vars['L_PASSWORD'] : $this->lang('L_PASSWORD'); ?>" autocomplete="off" /></label>
			<?php if ($this->vars['U_SEND_PASSWORD']) {  ?>
				<a href="<?php echo isset($this->vars['U_SEND_PASSWORD']) ? $this->vars['U_SEND_PASSWORD'] : $this->lang('U_SEND_PASSWORD'); ?>"><?php echo isset($this->vars['L_FORGOT_PASS']) ? $this->vars['L_FORGOT_PASS'] : $this->lang('L_FORGOT_PASS'); ?></a>
			<?php } ?>
			<?php if ($this->vars['S_AUTOLOGIN_ENABLED']) {  ?>
				<span class="responsive-hide">|</span> <label for="autologin"><?php echo isset($this->vars['L_LOG_ME_IN']) ? $this->vars['L_LOG_ME_IN'] : $this->lang('L_LOG_ME_IN'); ?> <input type="checkbox" tabindex="4" name="autologin" id="autologin" /></label>
			<?php } ?>
			<input type="submit" tabindex="5" name="login" value="<?php echo isset($this->vars['L_LOGIN']) ? $this->vars['L_LOGIN'] : $this->lang('L_LOGIN'); ?>" class="button2" />
			<?php echo isset($this->vars['S_LOGIN_REDIRECT']) ? $this->vars['S_LOGIN_REDIRECT'] : $this->lang('S_LOGIN_REDIRECT'); ?>
		</fieldset>
	</form>
<?php } ?>

<!-- EVENT index_body_stat_blocks_before -->

<?php if ($this->vars['S_DISPLAY_ONLINE_LIST']) {  ?>
	<div class="stat-block online-list">
		<?php if ($this->vars['U_VIEWONLINE']) {  ?><h3><a href="<?php echo isset($this->vars['U_VIEWONLINE']) ? $this->vars['U_VIEWONLINE'] : $this->lang('U_VIEWONLINE'); ?>"><?php echo isset($this->vars['L_WHO_IS_ONLINE']) ? $this->vars['L_WHO_IS_ONLINE'] : $this->lang('L_WHO_IS_ONLINE'); ?></a></h3><?php } else { ?><h3><?php echo isset($this->vars['L_WHO_IS_ONLINE']) ? $this->vars['L_WHO_IS_ONLINE'] : $this->lang('L_WHO_IS_ONLINE'); ?></h3><?php } ?>
		<p>
			<!-- EVENT index_body_block_online_prepend -->
			<?php echo isset($this->vars['TOTAL_USERS_ONLINE']) ? $this->vars['TOTAL_USERS_ONLINE'] : $this->lang('TOTAL_USERS_ONLINE'); ?> (<?php echo isset($this->vars['L_ONLINE_EXPLAIN']) ? $this->vars['L_ONLINE_EXPLAIN'] : $this->lang('L_ONLINE_EXPLAIN'); ?>)<br /><?php echo isset($this->vars['RECORD_USERS']) ? $this->vars['RECORD_USERS'] : $this->lang('RECORD_USERS'); ?><br /> 
			<?php if ($this->vars['U_VIEWONLINE']) {  ?>
				<br /><?php echo isset($this->vars['LOGGED_IN_USER_LIST']) ? $this->vars['LOGGED_IN_USER_LIST'] : $this->lang('LOGGED_IN_USER_LIST'); ?>
				<?php if ($this->vars['LEGEND']) {  ?><br /><em><?php echo isset($this->vars['L_LEGEND']) ? $this->vars['L_LEGEND'] : $this->lang('L_LEGEND'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <?php echo isset($this->vars['LEGEND']) ? $this->vars['LEGEND'] : $this->lang('LEGEND'); ?></em><?php } ?>
			<?php } ?>
			<!-- EVENT index_body_block_online_append -->
		</p>
	</div>
<?php } ?>

<!-- EVENT index_body_birthday_block_before -->

<?php if ($this->vars['S_DISPLAY_BIRTHDAY_LIST']) {  ?>
	<div class="stat-block birthday-list">
		<h3><?php echo isset($this->vars['L_BIRTHDAYS']) ? $this->vars['L_BIRTHDAYS'] : $this->lang('L_BIRTHDAYS'); ?></h3>
		<p>
			<!-- EVENT index_body_block_birthday_prepend -->
			<?php if ($this->vars['S_DISPLAY_BIRTHDAY_LIST']) {  ?><?php echo isset($this->vars['L_CONGRATULATIONS']) ? $this->vars['L_CONGRATULATIONS'] : $this->lang('L_CONGRATULATIONS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <strong><?php

$birthdays_count = ( isset($this->_tpldata['birthdays.']) ) ?  sizeof($this->_tpldata['birthdays.']) : 0;
for ($birthdays_i = 0; $birthdays_i < $birthdays_count; $birthdays_i++)
{
 $birthdays_item = &$this->_tpldata['birthdays.'][$birthdays_i];
 $birthdays_item['S_ROW_COUNT'] = $birthdays_i;
 $birthdays_item['S_NUM_ROWS'] = $birthdays_count;

?><?php echo isset($birthdays_item['USERNAME']) ? $birthdays_item['USERNAME'] : ''; ?><?php if ($birthdays_item['AGE'] !== '') {  ?> (<?php echo isset($birthdays_item['AGE']) ? $birthdays_item['AGE'] : ''; ?>)<?php } ?><?php if (! $birthdays_item['S_LAST_ROW']) {  ?>, <?php } ?><?php

} // END birthdays

if(isset($birthdays_item)) { unset($birthdays_item); } 

?></strong><?php } else { ?><?php echo isset($this->vars['L_NO_BIRTHDAYS']) ? $this->vars['L_NO_BIRTHDAYS'] : $this->lang('L_NO_BIRTHDAYS'); ?><?php } ?>
			<!-- EVENT index_body_block_birthday_append -->
		</p>
	</div>
<?php } ?>

<?php if ($this->vars['NEWEST_USER']) {  ?>
	<div class="stat-block statistics">
		<h3><?php echo isset($this->vars['L_STATISTICS']) ? $this->vars['L_STATISTICS'] : $this->lang('L_STATISTICS'); ?></h3>
		<p>
			<!-- EVENT index_body_block_stats_prepend -->
			<?php echo isset($this->vars['TOTAL_POSTS']) ? $this->vars['TOTAL_POSTS'] : $this->lang('TOTAL_POSTS'); ?> &bull; <?php echo isset($this->vars['TOTAL_TOPICS']) ? $this->vars['TOTAL_TOPICS'] : $this->lang('TOTAL_TOPICS'); ?> &bull; <?php echo isset($this->vars['TOTAL_USERS']) ? $this->vars['TOTAL_USERS'] : $this->lang('TOTAL_USERS'); ?> &bull; <?php echo isset($this->vars['NEWEST_USER']) ? $this->vars['NEWEST_USER'] : $this->lang('NEWEST_USER'); ?>
			<!-- EVENT index_body_block_stats_append -->
		</p>
	</div>
<?php } ?>

<!-- EVENT index_body_stat_blocks_after -->

<!-- INCLUDEX overall_footer.html -->
