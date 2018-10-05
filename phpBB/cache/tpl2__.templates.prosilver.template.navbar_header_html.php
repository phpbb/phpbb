<?php

// eXtreme Styles mod cache. Generated on Wed, 26 Sep 2018 02:46:16 +0000 (time=1537929976)

?><div class="navbar" role="navigation">
	<div class="inner">

	<ul id="nav-main" class="linklist bulletin" role="menubar">

		<li id="quick-links" class="small-icon responsive-menu dropdown-container<?php if (! $this->vars['S_DISPLAY_QUICK_LINKS'] && ! $this->vars['S_DISPLAY_SEARCH']) {  ?> hidden<?php } ?>" data-skip-responsive="true">
			<a href="#" class="responsive-menu-link dropdown-trigger"><?php echo isset($this->vars['L_QUICK_LINKS']) ? $this->vars['L_QUICK_LINKS'] : $this->lang('L_QUICK_LINKS'); ?></a>
			<div class="dropdown hidden">
				<div class="pointer"><div class="pointer-inner"></div></div>
				<ul class="dropdown-contents" role="menu">
					<!-- EVENT navbar_header_quick_links_before -->

					<?php if ($this->vars['S_DISPLAY_SEARCH']) {  ?>
						<li class="separator"></li>
						<?php if ($this->vars['S_REGISTERED_USER']) {  ?>
							<li class="small-icon icon-search-self"><a href="<?php echo isset($this->vars['U_SEARCH_SELF']) ? $this->vars['U_SEARCH_SELF'] : $this->lang('U_SEARCH_SELF'); ?>" role="menuitem"><?php echo isset($this->vars['L_SEARCH_SELF']) ? $this->vars['L_SEARCH_SELF'] : $this->lang('L_SEARCH_SELF'); ?></a></li>
						<?php } ?>
						<?php if ($this->vars['S_USER_LOGGED_IN']) {  ?>
							<li class="small-icon icon-search-new"><a href="<?php echo isset($this->vars['U_SEARCH_NEW']) ? $this->vars['U_SEARCH_NEW'] : $this->lang('U_SEARCH_NEW'); ?>" role="menuitem"><?php echo isset($this->vars['L_SEARCH_NEW']) ? $this->vars['L_SEARCH_NEW'] : $this->lang('L_SEARCH_NEW'); ?></a></li>
						<?php } ?>
						<?php if ($this->vars['S_LOAD_UNREADS']) {  ?>
							<li class="small-icon icon-search-unread"><a href="<?php echo isset($this->vars['U_SEARCH_UNREAD']) ? $this->vars['U_SEARCH_UNREAD'] : $this->lang('U_SEARCH_UNREAD'); ?>" role="menuitem"><?php echo isset($this->vars['L_SEARCH_UNREAD']) ? $this->vars['L_SEARCH_UNREAD'] : $this->lang('L_SEARCH_UNREAD'); ?></a></li>
						<?php } ?>
						<li class="small-icon icon-search-unanswered"><a href="<?php echo isset($this->vars['U_SEARCH_UNANSWERED']) ? $this->vars['U_SEARCH_UNANSWERED'] : $this->lang('U_SEARCH_UNANSWERED'); ?>" role="menuitem"><?php echo isset($this->vars['L_SEARCH_UNANSWERED']) ? $this->vars['L_SEARCH_UNANSWERED'] : $this->lang('L_SEARCH_UNANSWERED'); ?></a></li>
						<li class="small-icon icon-search-active"><a href="<?php echo isset($this->vars['U_SEARCH_ACTIVE_TOPICS']) ? $this->vars['U_SEARCH_ACTIVE_TOPICS'] : $this->lang('U_SEARCH_ACTIVE_TOPICS'); ?>" role="menuitem"><?php echo isset($this->vars['L_SEARCH_ACTIVE_TOPICS']) ? $this->vars['L_SEARCH_ACTIVE_TOPICS'] : $this->lang('L_SEARCH_ACTIVE_TOPICS'); ?></a></li>
						<li class="separator"></li>
						<li class="small-icon icon-search"><a href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" role="menuitem"><?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?></a></li>
					<?php } ?>

					<?php if (! $this->vars['S_IS_BOT'] && ( $this->vars['S_DISPLAY_MEMBERLIST'] || $this->vars['U_TEAM'] )) {  ?>
						<li class="separator"></li>
						<?php if ($this->vars['S_DISPLAY_MEMBERLIST']) {  ?><li class="small-icon icon-members"><a href="<?php echo isset($this->vars['U_MEMBERLIST']) ? $this->vars['U_MEMBERLIST'] : $this->lang('U_MEMBERLIST'); ?>" role="menuitem"><?php echo isset($this->vars['L_MEMBERLIST']) ? $this->vars['L_MEMBERLIST'] : $this->lang('L_MEMBERLIST'); ?></a></li><?php } ?>
						<?php if ($this->vars['U_TEAM']) {  ?><li class="small-icon icon-team"><a href="<?php echo isset($this->vars['U_TEAM']) ? $this->vars['U_TEAM'] : $this->lang('U_TEAM'); ?>" role="menuitem"><?php echo isset($this->vars['L_THE_TEAM']) ? $this->vars['L_THE_TEAM'] : $this->lang('L_THE_TEAM'); ?></a></li><?php } ?>
					<?php } ?>
					<li class="separator"></li>

					<!-- EVENT navbar_header_quick_links_after -->
				</ul>
			</div>
		</li>

		<!-- EVENT overall_header_navigation_prepend -->
		<li class="small-icon icon-faq" <?php if (! $this->vars['S_USER_LOGGED_IN']) {  ?>data-skip-responsive="true"<?php } else { ?>data-last-responsive="true"<?php } ?>><a href="<?php echo isset($this->vars['U_FAQ']) ? $this->vars['U_FAQ'] : $this->lang('U_FAQ'); ?>" rel="help" title="<?php echo isset($this->vars['L_FAQ_EXPLAIN']) ? $this->vars['L_FAQ_EXPLAIN'] : $this->lang('L_FAQ_EXPLAIN'); ?>" role="menuitem"><?php echo isset($this->vars['L_FAQ']) ? $this->vars['L_FAQ'] : $this->lang('L_FAQ'); ?></a></li>
		<!-- EVENT overall_header_navigation_append -->
		<?php if ($this->vars['U_ACP']) {  ?><li class="small-icon icon-acp" data-last-responsive="true"><a href="<?php echo isset($this->vars['U_ACP']) ? $this->vars['U_ACP'] : $this->lang('U_ACP'); ?>" title="<?php echo isset($this->vars['L_ACP']) ? $this->vars['L_ACP'] : $this->lang('L_ACP'); ?>" role="menuitem"><?php echo isset($this->vars['L_ACP_SHORT']) ? $this->vars['L_ACP_SHORT'] : $this->lang('L_ACP_SHORT'); ?></a></li><?php } ?>
		<?php if ($this->vars['U_MCP']) {  ?><li class="small-icon icon-mcp" data-last-responsive="true"><a href="<?php echo isset($this->vars['U_MCP']) ? $this->vars['U_MCP'] : $this->lang('U_MCP'); ?>" title="<?php echo isset($this->vars['L_MCP']) ? $this->vars['L_MCP'] : $this->lang('L_MCP'); ?>" role="menuitem"><?php echo isset($this->vars['L_MCP_SHORT']) ? $this->vars['L_MCP_SHORT'] : $this->lang('L_MCP_SHORT'); ?></a></li><?php } ?>

	<?php if ($this->vars['S_REGISTERED_USER']) {  ?>
		<!-- EVENT navbar_header_user_profile_prepend -->
		<li id="username_logged_in" class="rightside <?php if ($this->vars['CURRENT_USER_AVATAR']) {  ?> no-bulletin<?php } ?>" data-skip-responsive="true">
			<!-- EVENT navbar_header_username_prepend -->
			<div class="header-profile dropdown-container">
				<a href="<?php echo isset($this->vars['U_PROFILE']) ? $this->vars['U_PROFILE'] : $this->lang('U_PROFILE'); ?>" class="header-avatar dropdown-trigger"><?php if ($this->vars['CURRENT_USER_AVATAR']) {  ?><?php echo isset($this->vars['CURRENT_USER_AVATAR']) ? $this->vars['CURRENT_USER_AVATAR'] : $this->lang('CURRENT_USER_AVATAR'); ?> <?php } ?><?php echo isset($this->vars['CURRENT_USERNAME_SIMPLE']) ? $this->vars['CURRENT_USERNAME_SIMPLE'] : $this->lang('CURRENT_USERNAME_SIMPLE'); ?></a>
				<div class="dropdown hidden">
					<div class="pointer"><div class="pointer-inner"></div></div>
					<ul class="dropdown-contents" role="menu">
						<?php if ($this->vars['U_RESTORE_PERMISSIONS']) {  ?><li class="small-icon icon-restore-permissions"><a href="<?php echo isset($this->vars['U_RESTORE_PERMISSIONS']) ? $this->vars['U_RESTORE_PERMISSIONS'] : $this->lang('U_RESTORE_PERMISSIONS'); ?>"><?php echo isset($this->vars['L_RESTORE_PERMISSIONS']) ? $this->vars['L_RESTORE_PERMISSIONS'] : $this->lang('L_RESTORE_PERMISSIONS'); ?></a></li><?php } ?>

						<!-- EVENT navbar_header_profile_list_before -->

						<li class="small-icon icon-ucp"><a href="<?php echo isset($this->vars['U_PROFILE']) ? $this->vars['U_PROFILE'] : $this->lang('U_PROFILE'); ?>" title="<?php echo isset($this->vars['L_PROFILE']) ? $this->vars['L_PROFILE'] : $this->lang('L_PROFILE'); ?>" role="menuitem"><?php echo isset($this->vars['L_PROFILE']) ? $this->vars['L_PROFILE'] : $this->lang('L_PROFILE'); ?></a></li>
						<li class="small-icon icon-profile"><a href="<?php echo isset($this->vars['U_USER_PROFILE']) ? $this->vars['U_USER_PROFILE'] : $this->lang('U_USER_PROFILE'); ?>" title="<?php echo isset($this->vars['L_READ_PROFILE']) ? $this->vars['L_READ_PROFILE'] : $this->lang('L_READ_PROFILE'); ?>" role="menuitem"><?php echo isset($this->vars['L_READ_PROFILE']) ? $this->vars['L_READ_PROFILE'] : $this->lang('L_READ_PROFILE'); ?></a></li>

						<!-- EVENT navbar_header_profile_list_after -->

						<li class="separator"></li>
						<li class="small-icon icon-logout"><a href="<?php echo isset($this->vars['U_LOGIN_LOGOUT']) ? $this->vars['U_LOGIN_LOGOUT'] : $this->lang('U_LOGIN_LOGOUT'); ?>" title="<?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?>" accesskey="x" role="menuitem"><?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?></a></li>
					</ul>
				</div>
			</div>
			<!-- EVENT navbar_header_username_append -->
		</li>
		<?php if ($this->vars['S_DISPLAY_PM']) {  ?>
			<li class="small-icon icon-pm rightside" data-skip-responsive="true">
				<a href="<?php echo isset($this->vars['U_PRIVATEMSGS']) ? $this->vars['U_PRIVATEMSGS'] : $this->lang('U_PRIVATEMSGS'); ?>" role="menuitem"><span><?php echo isset($this->vars['L_PRIVATE_MESSAGES']) ? $this->vars['L_PRIVATE_MESSAGES'] : $this->lang('L_PRIVATE_MESSAGES'); ?> </span><strong class="badge<?php if (! $this->vars['PRIVATE_MESSAGE_COUNT']) {  ?> hidden<?php } ?>"><?php echo isset($this->vars['PRIVATE_MESSAGE_COUNT']) ? $this->vars['PRIVATE_MESSAGE_COUNT'] : $this->lang('PRIVATE_MESSAGE_COUNT'); ?></strong></a>
			</li>
		<?php } ?>
		<?php if ($this->vars['S_NOTIFICATIONS_DISPLAY']) {  ?>
			<li class="small-icon icon-notification dropdown-container dropdown-<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?> rightside" data-skip-responsive="true">
				<a href="<?php echo isset($this->vars['U_VIEW_ALL_NOTIFICATIONS']) ? $this->vars['U_VIEW_ALL_NOTIFICATIONS'] : $this->lang('U_VIEW_ALL_NOTIFICATIONS'); ?>" id="notification_list_button" class="dropdown-trigger"><span><?php echo isset($this->vars['L_NOTIFICATIONS']) ? $this->vars['L_NOTIFICATIONS'] : $this->lang('L_NOTIFICATIONS'); ?> </span><strong class="badge<?php if (! $this->vars['NOTIFICATIONS_COUNT']) {  ?> hidden<?php } ?>"><?php echo isset($this->vars['NOTIFICATIONS_COUNT']) ? $this->vars['NOTIFICATIONS_COUNT'] : $this->lang('NOTIFICATIONS_COUNT'); ?></strong></a>
				<?php  $this->set_filename('xs_include_884887ed78ff06f08920913674a900a0', 'notification_dropdown.html', true);  $this->pparse('xs_include_884887ed78ff06f08920913674a900a0');  ?>
			</li>
		<?php } ?>
		<!-- EVENT navbar_header_user_profile_append -->
	<?php } else { ?>
		<li class="small-icon icon-logout rightside"  data-skip-responsive="true"><a href="<?php echo isset($this->vars['U_LOGIN_LOGOUT']) ? $this->vars['U_LOGIN_LOGOUT'] : $this->lang('U_LOGIN_LOGOUT'); ?>" title="<?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?>" accesskey="x" role="menuitem"><?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?></a></li>
		<?php if ($this->vars['S_REGISTER_ENABLED'] && ! ( $this->vars['S_SHOW_COPPA'] || $this->vars['S_REGISTRATION'] )) {  ?>
			<li class="small-icon icon-register rightside" data-skip-responsive="true"><a href="<?php echo isset($this->vars['U_REGISTER']) ? $this->vars['U_REGISTER'] : $this->lang('U_REGISTER'); ?>" role="menuitem"><?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?></a></li>
		<?php } ?>
		<!-- EVENT navbar_header_logged_out_content -->
	<?php } ?>
	</ul>

	<ul id="nav-breadcrumbs" class="linklist navlinks" role="menubar">
		<?php $this->_tpldata['DEFINE']['.']['MICRODATA'] = ' itemtype=\"http://data-vocabulary.org/Breadcrumb\" itemscope=\"\"'; ?>
		<!-- EVENT overall_header_breadcrumbs_before -->
		<li class="small-icon icon-home breadcrumbs">
			<?php if ($this->vars['U_SITE_HOME']) {  ?><span class="crumb"<?php echo isset($this->_tpldata['DEFINE']['.']['MICRODATA']) ? $this->_tpldata['DEFINE']['.']['MICRODATA'] : ''; ?>><a href="<?php echo isset($this->vars['U_SITE_HOME']) ? $this->vars['U_SITE_HOME'] : $this->lang('U_SITE_HOME'); ?>" data-navbar-reference="home" itemprop="url"><span itemprop="title"><?php echo isset($this->vars['L_SITE_HOME']) ? $this->vars['L_SITE_HOME'] : $this->lang('L_SITE_HOME'); ?></span></a></span><?php } ?>
			<!-- EVENT overall_header_breadcrumb_prepend -->
			<span class="crumb"<?php echo isset($this->_tpldata['DEFINE']['.']['MICRODATA']) ? $this->_tpldata['DEFINE']['.']['MICRODATA'] : ''; ?>><a href="<?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?>" accesskey="h" data-navbar-reference="index" itemprop="url"><span itemprop="title"><?php echo isset($this->vars['L_INDEX']) ? $this->vars['L_INDEX'] : $this->lang('L_INDEX'); ?></span></a></span>
			<?php

$navlinks_count = ( isset($this->_tpldata['navlinks.']) ) ?  sizeof($this->_tpldata['navlinks.']) : 0;
for ($navlinks_i = 0; $navlinks_i < $navlinks_count; $navlinks_i++)
{
 $navlinks_item = &$this->_tpldata['navlinks.'][$navlinks_i];
 $navlinks_item['S_ROW_COUNT'] = $navlinks_i;
 $navlinks_item['S_NUM_ROWS'] = $navlinks_count;

?>
				<!-- EVENT overall_header_navlink_prepend -->
				<span class="crumb"<?php echo isset($this->_tpldata['DEFINE']['.']['MICRODATA']) ? $this->_tpldata['DEFINE']['.']['MICRODATA'] : ''; ?><?php if ($navlinks_item['MICRODATA']) {  ?> <?php echo isset($navlinks_item['MICRODATA']) ? $navlinks_item['MICRODATA'] : ''; ?><?php } ?>><a href="<?php echo isset($navlinks_item['U_VIEW_FORUM']) ? $navlinks_item['U_VIEW_FORUM'] : ''; ?>" itemprop="url"><span itemprop="title"><?php echo isset($navlinks_item['FORUM_NAME']) ? $navlinks_item['FORUM_NAME'] : ''; ?></span></a></span>
				<!-- EVENT overall_header_navlink_append -->
			<?php

} // END navlinks

if(isset($navlinks_item)) { unset($navlinks_item); } 

?>
			<!-- EVENT overall_header_breadcrumb_append -->
		</li>
		<!-- EVENT overall_header_breadcrumbs_after -->

		<?php if ($this->vars['S_DISPLAY_SEARCH'] && ! $this->vars['S_IN_SEARCH']) {  ?>
			<li class="rightside responsive-search" style="display: none;"><a href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" title="<?php echo isset($this->vars['L_SEARCH_ADV_EXPLAIN']) ? $this->vars['L_SEARCH_ADV_EXPLAIN'] : $this->lang('L_SEARCH_ADV_EXPLAIN'); ?>" role="menuitem"><?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?></a></li>
		<?php } ?>
	</ul>

	</div>
</div>
