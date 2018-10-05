<?php

// eXtreme Styles mod cache. Generated on Mon, 24 Sep 2018 03:28:06 +0000 (time=1537759686)

?><?php if ($this->vars['SOCIAL_CONNECT_LINK']) {  ?>
<dl>
	<dt class="row1" colspan="2">
		<img style="float: left; margin-right: 10px;" src="<?php echo isset($this->vars['U_PROFILE_PHOTO']) ? $this->vars['U_PROFILE_PHOTO'] : $this->lang('U_PROFILE_PHOTO'); ?>" alt="<?php echo isset($this->vars['USER_REAL_NAME']) ? $this->vars['USER_REAL_NAME'] : $this->lang('USER_REAL_NAME'); ?>" />
		<span class="genmed">
			<?php echo isset($this->vars['L_SOCIAL_CONNECT_LINK_ACCOUNT']) ? $this->vars['L_SOCIAL_CONNECT_LINK_ACCOUNT'] : $this->lang('L_SOCIAL_CONNECT_LINK_ACCOUNT'); ?><br />
			<img src="<?php echo isset($this->vars['U_SOCIAL_NETWORK_ICON']) ? $this->vars['U_SOCIAL_NETWORK_ICON'] : $this->lang('U_SOCIAL_NETWORK_ICON'); ?>" alt="<?php echo isset($this->vars['SOCIAL_NETWORK_NAME']) ? $this->vars['SOCIAL_NETWORK_NAME'] : $this->lang('SOCIAL_NETWORK_NAME'); ?>" title="<?php echo isset($this->vars['SOCIAL_NETWORK_NAME']) ? $this->vars['SOCIAL_NETWORK_NAME'] : $this->lang('SOCIAL_NETWORK_NAME'); ?>" />&nbsp;<b><?php echo isset($this->vars['USER_REAL_NAME']) ? $this->vars['USER_REAL_NAME'] : $this->lang('USER_REAL_NAME'); ?></b><br/>
			<a href="<?php echo isset($this->vars['U_PROFILE_LINK']) ? $this->vars['U_PROFILE_LINK'] : $this->lang('U_PROFILE_LINK'); ?>" target="_blank"><?php echo isset($this->vars['U_PROFILE_LINK']) ? $this->vars['U_PROFILE_LINK'] : $this->lang('U_PROFILE_LINK'); ?></a>
		</span>
	</dt>
</dl>
<dl><dd class="tvalignm" colspan="2"><?php echo isset($this->vars['L_REGISTRATION_INFO']) ? $this->vars['L_REGISTRATION_INFO'] : $this->lang('L_REGISTRATION_INFO'); ?></dd></dl>
<?php } ?>