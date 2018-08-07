<?php

// eXtreme Styles mod cache. Generated on Fri, 27 Jul 2018 16:44:47 +0000 (time=1532709887)

?><!-- INCLUDEX overall_header.html -->

<!-- EVENT index_body_markforums_before -->

<?php if ($this->vars['U_MCP'] || $this->vars['U_ACP']) {  ?>
	<div id="pageheader">
		<p class="linkmcp">[&nbsp;<?php if ($this->vars['U_ACP']) {  ?><a href="<?php echo isset($this->vars['U_ACP']) ? $this->vars['U_ACP'] : $this->lang('U_ACP'); ?>"><?php echo isset($this->vars['L_ACP']) ? $this->vars['L_ACP'] : $this->lang('L_ACP'); ?></a><?php if ($this->vars['U_MCP']) {  ?>&nbsp;|&nbsp;<?php } ?><?php } ?><?php if ($this->vars['U_MCP']) {  ?><a href="<?php echo isset($this->vars['U_MCP']) ? $this->vars['U_MCP'] : $this->lang('U_MCP'); ?>"><?php echo isset($this->vars['L_MCP']) ? $this->vars['L_MCP'] : $this->lang('L_MCP'); ?></a><?php } ?>&nbsp;]</p>
	</div>

	<br clear="all" /><br />
<?php } ?>

<!-- EVENT index_body_markforums_after -->

<?php  $this->set_filename('xs_include_68df7b30414fe374b38ecb28d580edfd', 'forumlist_body.html', true);  $this->pparse('xs_include_68df7b30414fe374b38ecb28d580edfd');  ?>

<!-- EVENT index_body_forumlist_body_after -->

<?php if (! $this->vars['S_IS_BOT'] || $this->vars['U_TEAM']) {  ?>
<span class="gensmall">
	<?php if (! $this->vars['S_IS_BOT']) {  ?><a href="<?php echo isset($this->vars['U_DELETE_COOKIES']) ? $this->vars['U_DELETE_COOKIES'] : $this->lang('U_DELETE_COOKIES'); ?>"><?php echo isset($this->vars['L_DELETE_COOKIES']) ? $this->vars['L_DELETE_COOKIES'] : $this->lang('L_DELETE_COOKIES'); ?></a><?php } ?>
	<?php if (! $this->vars['S_IS_BOT'] && $this->vars['U_TEAM']) {  ?> | <?php } ?>
	<!-- EVENT overall_footer_teamlink_before -->
	<?php if ($this->vars['U_TEAM']) {  ?><a href="<?php echo isset($this->vars['U_TEAM']) ? $this->vars['U_TEAM'] : $this->lang('U_TEAM'); ?>"><?php echo isset($this->vars['L_THE_TEAM']) ? $this->vars['L_THE_TEAM'] : $this->lang('L_THE_TEAM'); ?></a><?php } ?>
	<?php if ($this->vars['U_CONTACT_US']) {  ?>
		<?php if ($this->vars['U_TEAM']) {  ?> | <?php } ?>
		<a href="<?php echo isset($this->vars['U_CONTACT_US']) ? $this->vars['U_CONTACT_US'] : $this->lang('U_CONTACT_US'); ?>"><?php echo isset($this->vars['L_CONTACT_US']) ? $this->vars['L_CONTACT_US'] : $this->lang('L_CONTACT_US'); ?></a>
	<?php } ?>
	<!-- EVENT overall_footer_teamlink_after -->
</span>
<?php } ?>
<br />

<br clear="all" />

<?php  $this->set_filename('xs_include_c5880a0e3d6cf7f1f834cc2f775f62dc', 'breadcrumbs.html', true);  $this->pparse('xs_include_c5880a0e3d6cf7f1f834cc2f775f62dc');  ?>

<!-- EVENT index_body_stat_blocks_before -->

<?php if ($this->vars['S_DISPLAY_ONLINE_LIST']) {  ?>
	<br clear="all" />

	<table class="tablebg stat-block online-list" width="100--" cellspacing="1">
	<tr>
		<td class="cat" colspan="2"><?php if ($this->vars['U_VIEWONLINE']) {  ?><h4><a href="<?php echo isset($this->vars['U_VIEWONLINE']) ? $this->vars['U_VIEWONLINE'] : $this->lang('U_VIEWONLINE'); ?>"><?php echo isset($this->vars['L_WHO_IS_ONLINE']) ? $this->vars['L_WHO_IS_ONLINE'] : $this->lang('L_WHO_IS_ONLINE'); ?></a></h4><?php } else { ?><h4><?php echo isset($this->vars['L_WHO_IS_ONLINE']) ? $this->vars['L_WHO_IS_ONLINE'] : $this->lang('L_WHO_IS_ONLINE'); ?></h4><?php } ?></td>
	</tr>
	<tr>
	<?php if ($this->vars['LEGEND']) {  ?>
		<td class="row1" rowspan="2" align="center" valign="middle"><img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/whosonline.gif" alt="<?php echo isset($this->vars['L_WHO_IS_ONLINE']) ? $this->vars['L_WHO_IS_ONLINE'] : $this->lang('L_WHO_IS_ONLINE'); ?>" /></td>
	<?php } else { ?>
		<td class="row1" align="center" valign="middle"><img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/whosonline.gif" alt="<?php echo isset($this->vars['L_WHO_IS_ONLINE']) ? $this->vars['L_WHO_IS_ONLINE'] : $this->lang('L_WHO_IS_ONLINE'); ?>" /></td>
	<?php } ?>
		<td class="row1" width="100--">
			<span class="genmed">
				<!-- EVENT index_body_block_online_prepend -->
				<?php echo isset($this->vars['TOTAL_USERS_ONLINE']) ? $this->vars['TOTAL_USERS_ONLINE'] : $this->lang('TOTAL_USERS_ONLINE'); ?> (<?php echo isset($this->vars['L_ONLINE_EXPLAIN']) ? $this->vars['L_ONLINE_EXPLAIN'] : $this->lang('L_ONLINE_EXPLAIN'); ?>)<br /><?php echo isset($this->vars['RECORD_USERS']) ? $this->vars['RECORD_USERS'] : $this->lang('RECORD_USERS'); ?><br /><br /><?php echo isset($this->vars['LOGGED_IN_USER_LIST']) ? $this->vars['LOGGED_IN_USER_LIST'] : $this->lang('LOGGED_IN_USER_LIST'); ?>
				<!-- EVENT index_body_block_online_append -->
			</span>
		</td>
	</tr>
	<?php if ($this->vars['LEGEND']) {  ?>
		<tr>
			<td class="row1"><b class="gensmall"><?php echo isset($this->vars['L_LEGEND']) ? $this->vars['L_LEGEND'] : $this->lang('L_LEGEND'); ?> :: <?php echo isset($this->vars['LEGEND']) ? $this->vars['LEGEND'] : $this->lang('LEGEND'); ?></b></td>
		</tr>
	<?php } ?>
	</table>
<?php } ?>

<?php if ($this->vars['S_DISPLAY_BIRTHDAY_LIST']) {  ?>
	<br clear="all" />

	<table class="tablebg stat-block birthday-list" width="100--" cellspacing="1">
	<tr>
		<td class="cat" colspan="2"><h4><?php echo isset($this->vars['L_BIRTHDAYS']) ? $this->vars['L_BIRTHDAYS'] : $this->lang('L_BIRTHDAYS'); ?></h4></td>
	</tr>
	<tr>
		<td class="row1" align="center" valign="middle"><img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/whosonline.gif" alt="<?php echo isset($this->vars['L_BIRTHDAYS']) ? $this->vars['L_BIRTHDAYS'] : $this->lang('L_BIRTHDAYS'); ?>" /></td>
		<td class="row1" width="100--">
			<p class="genmed">
				<!-- EVENT index_body_block_birthday_prepend -->
				<?php if ($this->vars['S_DISPLAY_BIRTHDAY_LIST']) {  ?><?php echo isset($this->vars['L_CONGRATULATIONS']) ? $this->vars['L_CONGRATULATIONS'] : $this->lang('L_CONGRATULATIONS'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?> <b><?php

$birthdays_count = ( isset($this->_tpldata['birthdays.']) ) ?  sizeof($this->_tpldata['birthdays.']) : 0;
for ($birthdays_i = 0; $birthdays_i < $birthdays_count; $birthdays_i++)
{
 $birthdays_item = &$this->_tpldata['birthdays.'][$birthdays_i];
 $birthdays_item['S_ROW_COUNT'] = $birthdays_i;
 $birthdays_item['S_NUM_ROWS'] = $birthdays_count;

?><?php echo isset($birthdays_item['USERNAME']) ? $birthdays_item['USERNAME'] : ''; ?><?php if ($birthdays_item['AGE'] !== '') {  ?> (<?php echo isset($birthdays_item['AGE']) ? $birthdays_item['AGE'] : ''; ?>)<?php } ?><?php if (! $birthdays_item['S_LAST_ROW']) {  ?>, <?php } ?><?php

} // END birthdays

if(isset($birthdays_item)) { unset($birthdays_item); } 

?></b><?php } else { ?><?php echo isset($this->vars['L_NO_BIRTHDAYS']) ? $this->vars['L_NO_BIRTHDAYS'] : $this->lang('L_NO_BIRTHDAYS'); ?><?php } ?>
				<!-- EVENT index_body_block_birthday_append -->
			</p>
		</td>
	</tr>
	</table>
<?php } ?>

<br clear="all" />

<table class="tablebg stat-block statistics" width="100--" cellspacing="1">
<tr>
	<td class="cat" colspan="2"><h4><?php echo isset($this->vars['L_STATISTICS']) ? $this->vars['L_STATISTICS'] : $this->lang('L_STATISTICS'); ?></h4></td>
</tr>
<tr>
	<td class="row1"><img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/whosonline.gif" alt="<?php echo isset($this->vars['L_STATISTICS']) ? $this->vars['L_STATISTICS'] : $this->lang('L_STATISTICS'); ?>" /></td>
	<td class="row1" width="100--" valign="middle">
		<p class="genmed">
			<!-- EVENT index_body_block_stats_prepend -->
			<?php echo isset($this->vars['TOTAL_POSTS']) ? $this->vars['TOTAL_POSTS'] : $this->lang('TOTAL_POSTS'); ?> | <?php echo isset($this->vars['TOTAL_TOPICS']) ? $this->vars['TOTAL_TOPICS'] : $this->lang('TOTAL_TOPICS'); ?> | <?php echo isset($this->vars['TOTAL_USERS']) ? $this->vars['TOTAL_USERS'] : $this->lang('TOTAL_USERS'); ?> | <?php echo isset($this->vars['NEWEST_USER']) ? $this->vars['NEWEST_USER'] : $this->lang('NEWEST_USER'); ?>
			<!-- EVENT index_body_block_stats_append -->
		</p>
	</td>
</tr>
</table>

<!-- EVENT index_body_stat_blocks_after -->

<?php if (! $this->vars['S_USER_LOGGED_IN'] && ! $this->vars['S_IS_BOT']) {  ?>
	<br clear="all" />

	<form method="post" action="<?php echo isset($this->vars['S_LOGIN_ACTION']) ? $this->vars['S_LOGIN_ACTION'] : $this->lang('S_LOGIN_ACTION'); ?>">

	<table class="tablebg" width="100--" cellspacing="1">
	<tr>
		<td class="cat"><h4><a href="<?php echo isset($this->vars['U_LOGIN_LOGOUT']) ? $this->vars['U_LOGIN_LOGOUT'] : $this->lang('U_LOGIN_LOGOUT'); ?>"><?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?></a></h4></td>
	</tr>
	<tr>
		<td class="row1" align="center"><span class="genmed"><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></span> <input class="post" type="text" name="username" size="10" />&nbsp; <span class="genmed"><?php echo isset($this->vars['L_PASSWORD']) ? $this->vars['L_PASSWORD'] : $this->lang('L_PASSWORD'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></span> <input class="post" type="password" name="password" size="10" autocomplete="off" />&nbsp; <?php if ($this->vars['U_SEND_PASSWORD']) {  ?><a href="<?php echo isset($this->vars['U_SEND_PASSWORD']) ? $this->vars['U_SEND_PASSWORD'] : $this->lang('U_SEND_PASSWORD'); ?>"><?php echo isset($this->vars['L_FORGOT_PASS']) ? $this->vars['L_FORGOT_PASS'] : $this->lang('L_FORGOT_PASS'); ?></a>&nbsp; <?php } ?> <?php if ($this->vars['S_AUTOLOGIN_ENABLED']) {  ?> <span class="gensmall"><?php echo isset($this->vars['L_LOG_ME_IN']) ? $this->vars['L_LOG_ME_IN'] : $this->lang('L_LOG_ME_IN'); ?></span> <input type="checkbox" class="radio" name="autologin" /><?php } ?>&nbsp; <input type="submit" class="btnmain" name="login" value="<?php echo isset($this->vars['L_LOGIN']) ? $this->vars['L_LOGIN'] : $this->lang('L_LOGIN'); ?>" /></td>
	</tr>
	</table>
	<?php echo isset($this->vars['S_LOGIN_REDIRECT']) ? $this->vars['S_LOGIN_REDIRECT'] : $this->lang('S_LOGIN_REDIRECT'); ?>
	<?php echo isset($this->vars['S_FORM_TOKEN']) ? $this->vars['S_FORM_TOKEN'] : $this->lang('S_FORM_TOKEN'); ?>
	</form>
<?php } ?>

<br clear="all" />

<table class="legend">
<tr>
	<td width="20" align="center"><?php echo isset($this->vars['FORUM_UNREAD_IMG']) ? $this->vars['FORUM_UNREAD_IMG'] : $this->lang('FORUM_UNREAD_IMG'); ?></td>
	<td><span class="gensmall"><?php echo isset($this->vars['L_UNREAD_POSTS']) ? $this->vars['L_UNREAD_POSTS'] : $this->lang('L_UNREAD_POSTS'); ?></span></td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><?php echo isset($this->vars['FORUM_IMG']) ? $this->vars['FORUM_IMG'] : $this->lang('FORUM_IMG'); ?></td>
	<td><span class="gensmall"><?php echo isset($this->vars['L_NO_UNREAD_POSTS']) ? $this->vars['L_NO_UNREAD_POSTS'] : $this->lang('L_NO_UNREAD_POSTS'); ?></span></td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><?php echo isset($this->vars['FORUM_LOCKED_IMG']) ? $this->vars['FORUM_LOCKED_IMG'] : $this->lang('FORUM_LOCKED_IMG'); ?></td>
	<td><span class="gensmall"><?php echo isset($this->vars['L_FORUM_LOCKED']) ? $this->vars['L_FORUM_LOCKED'] : $this->lang('L_FORUM_LOCKED'); ?></span></td>
</tr>
</table>

<!-- INCLUDEX overall_footer.html -->
