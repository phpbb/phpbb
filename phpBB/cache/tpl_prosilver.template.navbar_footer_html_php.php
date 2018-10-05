<?php

// eXtreme Styles mod cache. Generated on Mon, 24 Sep 2018 03:28:06 +0000 (time=1537759686)

?><div class="navbar" role="navigation">
	<div class="inner">

	<ul id="nav-footer" class="linklist bulletin" role="menubar">
		<li class="small-icon icon-home breadcrumbs">
			<?php if ($this->vars['U_SITE_HOME']) {  ?><span class="crumb"><a href="<?php echo isset($this->vars['U_SITE_HOME']) ? $this->vars['U_SITE_HOME'] : $this->lang('U_SITE_HOME'); ?>" data-navbar-reference="home"><?php echo isset($this->vars['L_SITE_HOME']) ? $this->vars['L_SITE_HOME'] : $this->lang('L_SITE_HOME'); ?></a></span><?php } ?>
			<!-- EVENT overall_footer_breadcrumb_prepend -->
			<span class="crumb"><a href="<?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?>" data-navbar-reference="index"><?php echo isset($this->vars['L_INDEX']) ? $this->vars['L_INDEX'] : $this->lang('L_INDEX'); ?></a></span>
			<!-- EVENT overall_footer_breadcrumb_append -->
		</li>
		<?php if ($this->vars['U_WATCH_FORUM_LINK'] && ! $this->vars['S_IS_BOT']) {  ?><li class="small-icon icon-<?php if ($this->vars['S_WATCHING_FORUM']) {  ?>unsubscribe<?php } else { ?>subscribe<?php } ?>" data-last-responsive="true"><a href="<?php echo isset($this->vars['U_WATCH_FORUM_LINK']) ? $this->vars['U_WATCH_FORUM_LINK'] : $this->lang('U_WATCH_FORUM_LINK'); ?>" title="<?php echo isset($this->vars['S_WATCH_FORUM_TITLE']) ? $this->vars['S_WATCH_FORUM_TITLE'] : $this->lang('S_WATCH_FORUM_TITLE'); ?>" data-ajax="toggle_link" data-toggle-class="small-icon icon-<?php if (! $this->vars['S_WATCHING_FORUM']) {  ?>unsubscribe<?php } else { ?>subscribe<?php } ?>" data-toggle-text="<?php echo isset($this->vars['S_WATCH_FORUM_TOGGLE']) ? $this->vars['S_WATCH_FORUM_TOGGLE'] : $this->lang('S_WATCH_FORUM_TOGGLE'); ?>" data-toggle-url="<?php echo isset($this->vars['U_WATCH_FORUM_TOGGLE']) ? $this->vars['U_WATCH_FORUM_TOGGLE'] : $this->lang('U_WATCH_FORUM_TOGGLE'); ?>"><?php echo isset($this->vars['S_WATCH_FORUM_TITLE']) ? $this->vars['S_WATCH_FORUM_TITLE'] : $this->lang('S_WATCH_FORUM_TITLE'); ?></a></li><?php } ?>

		<!-- EVENT overall_footer_timezone_before -->
		<li class="rightside"><?php echo isset($this->vars['S_TIMEZONE']) ? $this->vars['S_TIMEZONE'] : $this->lang('S_TIMEZONE'); ?></li>
		<!-- EVENT overall_footer_timezone_after -->
		<?php if (! $this->vars['S_IS_BOT']) {  ?>
			<li class="small-icon icon-delete-cookies rightside"><a href="<?php echo isset($this->vars['U_DELETE_COOKIES']) ? $this->vars['U_DELETE_COOKIES'] : $this->lang('U_DELETE_COOKIES'); ?>" data-ajax="true" data-refresh="true" role="menuitem"><?php echo isset($this->vars['L_DELETE_COOKIES']) ? $this->vars['L_DELETE_COOKIES'] : $this->lang('L_DELETE_COOKIES'); ?></a></li>
			<?php if ($this->vars['S_DISPLAY_MEMBERLIST']) {  ?><li class="small-icon icon-members rightside" data-last-responsive="true"><a href="<?php echo isset($this->vars['U_MEMBERLIST']) ? $this->vars['U_MEMBERLIST'] : $this->lang('U_MEMBERLIST'); ?>" title="<?php echo isset($this->vars['L_MEMBERLIST_EXPLAIN']) ? $this->vars['L_MEMBERLIST_EXPLAIN'] : $this->lang('L_MEMBERLIST_EXPLAIN'); ?>" role="menuitem"><?php echo isset($this->vars['L_MEMBERLIST']) ? $this->vars['L_MEMBERLIST'] : $this->lang('L_MEMBERLIST'); ?></a></li><?php } ?>
		<?php } ?>
		<!-- EVENT overall_footer_teamlink_before -->
		<?php if ($this->vars['U_TEAM']) {  ?><li class="small-icon icon-team rightside" data-last-responsive="true"><a href="<?php echo isset($this->vars['U_TEAM']) ? $this->vars['U_TEAM'] : $this->lang('U_TEAM'); ?>" role="menuitem"><?php echo isset($this->vars['L_THE_TEAM']) ? $this->vars['L_THE_TEAM'] : $this->lang('L_THE_TEAM'); ?></a></li><?php } ?>
		<!-- EVENT overall_footer_teamlink_after -->
		<?php if ($this->vars['U_CONTACT_US']) {  ?><li class="small-icon icon-contact rightside" data-last-responsive="true"><a href="<?php echo isset($this->vars['U_CONTACT_US']) ? $this->vars['U_CONTACT_US'] : $this->lang('U_CONTACT_US'); ?>" role="menuitem"><?php echo isset($this->vars['L_CONTACT_US']) ? $this->vars['L_CONTACT_US'] : $this->lang('L_CONTACT_US'); ?></a></li><?php } ?>
	</ul>

	</div>
</div>
