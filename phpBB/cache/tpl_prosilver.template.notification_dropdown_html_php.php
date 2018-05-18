<?php

// eXtreme Styles mod cache. Generated on Thu, 17 May 2018 09:51:16 +0000 (time=1526550676)

?><div id="notification_list" class="dropdown dropdown-extended notification_list">
	<div class="pointer"><div class="pointer-inner"></div></div>
	<div class="dropdown-contents">
		<div class="header">
			<?php echo isset($this->vars['L_NOTIFICATIONS']) ? $this->vars['L_NOTIFICATIONS'] : $this->lang('L_NOTIFICATIONS'); ?>
			<span class="header_settings">
				<a href="<?php echo isset($this->vars['U_NOTIFICATION_SETTINGS']) ? $this->vars['U_NOTIFICATION_SETTINGS'] : $this->lang('U_NOTIFICATION_SETTINGS'); ?>"><?php echo isset($this->vars['L_SETTINGS']) ? $this->vars['L_SETTINGS'] : $this->lang('L_SETTINGS'); ?></a>
				<?php if ($this->vars['NOTIFICATIONS_COUNT']) {  ?>
					<span id="mark_all_notifications"> &bull; <a href="<?php echo isset($this->vars['U_MARK_ALL_NOTIFICATIONS']) ? $this->vars['U_MARK_ALL_NOTIFICATIONS'] : $this->lang('U_MARK_ALL_NOTIFICATIONS'); ?>" data-ajax="notification.mark_all_read"><?php echo isset($this->vars['L_MARK_ALL_READ']) ? $this->vars['L_MARK_ALL_READ'] : $this->lang('L_MARK_ALL_READ'); ?></a></span>
				<?php } ?>
			</span>
		</div>

		<ul>
			<?php if (! $this->vars['NOTIFICATIONS_COUNT']) {  ?>
				<li class="no_notifications">
					<?php echo isset($this->vars['L_NO_NOTIFICATIONS']) ? $this->vars['L_NO_NOTIFICATIONS'] : $this->lang('L_NO_NOTIFICATIONS'); ?>
				</li>
			<?php } ?>
			<?php

$notifications_count = ( isset($this->_tpldata['notifications.']) ) ?  sizeof($this->_tpldata['notifications.']) : 0;
for ($notifications_i = 0; $notifications_i < $notifications_count; $notifications_i++)
{
 $notifications_item = &$this->_tpldata['notifications.'][$notifications_i];
 $notifications_item['S_ROW_COUNT'] = $notifications_i;
 $notifications_item['S_NUM_ROWS'] = $notifications_count;

?>
				<li class="<?php if ($notifications_item['UNREAD']) {  ?> bg2<?php } ?><?php if ($notifications_item['STYLING']) {  ?> <?php echo isset($notifications_item['STYLING']) ? $notifications_item['STYLING'] : ''; ?><?php } ?><?php if (! $notifications_item['URL']) {  ?> no-url<?php } ?>">
					<?php if ($notifications_item['URL']) {  ?>
						<a class="notification-block" href="<?php if ($notifications_item['UNREAD']) {  ?><?php echo isset($notifications_item['U_MARK_READ']) ? $notifications_item['U_MARK_READ'] : ''; ?>" data-real-url="<?php echo isset($notifications_item['URL']) ? $notifications_item['URL'] : ''; ?><?php } else { ?><?php echo isset($notifications_item['URL']) ? $notifications_item['URL'] : ''; ?><?php } ?>">
					<?php } ?>
						<?php if ($notifications_item['AVATAR']) {  ?><?php echo isset($notifications_item['AVATAR']) ? $notifications_item['AVATAR'] : ''; ?><?php } else { ?><img src="<?php echo isset($this->vars['T_THEME_PATH']) ? $this->vars['T_THEME_PATH'] : $this->lang('T_THEME_PATH'); ?>/images/no_avatar.gif" alt="" /><?php } ?>
						<div class="notification_text">
							<p class="notification-title"><?php echo isset($notifications_item['FORMATTED_TITLE']) ? $notifications_item['FORMATTED_TITLE'] : ''; ?></p>
							<?php if ($notifications_item['REFERENCE']) {  ?><p class="notification-reference"><?php echo isset($notifications_item['REFERENCE']) ? $notifications_item['REFERENCE'] : ''; ?></p><?php } ?>
							<?php if ($notifications_item['FORUM']) {  ?><p class="notification-forum"><?php echo isset($notifications_item['FORUM']) ? $notifications_item['FORUM'] : ''; ?></p><?php } ?>
							<?php if ($notifications_item['REASON']) {  ?><p class="notification-reason"><?php echo isset($notifications_item['REASON']) ? $notifications_item['REASON'] : ''; ?></p><?php } ?>
							<p class="notification-time"><?php echo isset($notifications_item['TIME']) ? $notifications_item['TIME'] : ''; ?></p>
						</div>
					<?php if ($notifications_item['URL']) {  ?></a><?php } ?>
					<?php if ($notifications_item['UNREAD']) {  ?>
						<a href="<?php echo isset($notifications_item['U_MARK_READ']) ? $notifications_item['U_MARK_READ'] : ''; ?>" class="mark_read icon-mark" data-ajax="notification.mark_read" title="<?php echo isset($this->vars['L_MARK_READ']) ? $this->vars['L_MARK_READ'] : $this->lang('L_MARK_READ'); ?>"></a>
					<?php } ?>
				</li>
			<?php

} // END notifications

if(isset($notifications_item)) { unset($notifications_item); } 

?>
		</ul>

		<div class="footer">
			<a href="<?php echo isset($this->vars['U_VIEW_ALL_NOTIFICATIONS']) ? $this->vars['U_VIEW_ALL_NOTIFICATIONS'] : $this->lang('U_VIEW_ALL_NOTIFICATIONS'); ?>"><span><?php echo isset($this->vars['L_SEE_ALL']) ? $this->vars['L_SEE_ALL'] : $this->lang('L_SEE_ALL'); ?></span></a>
		</div>
	</div>
</div>
