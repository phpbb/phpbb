<?php

// eXtreme Styles mod cache. Generated on Tue, 07 Aug 2018 01:39:11 +0000 (time=1533605951)

?><div class="navbar">
			<div class="inner"><span class="corners-top"><span></span></span>
			<ul class="linklist navlinks">
				<li><a class="icon-home" href="<?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?>"><?php echo isset($this->vars['L_INDEX']) ? $this->vars['L_INDEX'] : $this->lang('L_INDEX'); ?></a></li>
				<li class="rightside"></li>
			</ul>
			<ul class="linklist">
				<?php

$switch_user_logged_in_count = ( isset($this->_tpldata['switch_user_logged_in.']) ) ?  sizeof($this->_tpldata['switch_user_logged_in.']) : 0;
for ($switch_user_logged_in_i = 0; $switch_user_logged_in_i < $switch_user_logged_in_count; $switch_user_logged_in_i++)
{
 $switch_user_logged_in_item = &$this->_tpldata['switch_user_logged_in.'][$switch_user_logged_in_i];
 $switch_user_logged_in_item['S_ROW_COUNT'] = $switch_user_logged_in_i;
 $switch_user_logged_in_item['S_NUM_ROWS'] = $switch_user_logged_in_count;

?>
					<li>
						<a href="<?php echo isset($this->vars['U_PROFILE']) ? $this->vars['U_PROFILE'] : $this->lang('U_PROFILE'); ?>" title="<?php echo isset($this->vars['L_PROFILE']) ? $this->vars['L_PROFILE'] : $this->lang('L_PROFILE'); ?>" class="icon-ucp"><?php echo isset($this->vars['L_PROFILE']) ? $this->vars['L_PROFILE'] : $this->lang('L_PROFILE'); ?></a>
							 (<a href="<?php echo isset($this->vars['U_PRIVATEMSGS']) ? $this->vars['U_PRIVATEMSGS'] : $this->lang('U_PRIVATEMSGS'); ?>"><?php echo isset($this->vars['PRIVATE_MESSAGE_INFO']) ? $this->vars['PRIVATE_MESSAGE_INFO'] : $this->lang('PRIVATE_MESSAGE_INFO'); ?></a>) &bull; 
						<a href="<?php echo isset($this->vars['U_SEARCH_SELF']) ? $this->vars['U_SEARCH_SELF'] : $this->lang('U_SEARCH_SELF'); ?>"><?php echo isset($this->vars['L_SEARCH_SELF']) ? $this->vars['L_SEARCH_SELF'] : $this->lang('L_SEARCH_SELF'); ?></a>
					</li>
				<?php

} // END switch_user_logged_in

if(isset($switch_user_logged_in_item)) { unset($switch_user_logged_in_item); } 

?>
				<li class="rightside">
					<a href="<?php echo isset($this->vars['U_FAQ']) ? $this->vars['U_FAQ'] : $this->lang('U_FAQ'); ?>" title="<?php echo isset($this->vars['L_FAQ_EXPLAIN']) ? $this->vars['L_FAQ_EXPLAIN'] : $this->lang('L_FAQ_EXPLAIN'); ?>" class="icon-faq"><?php echo isset($this->vars['L_FAQ']) ? $this->vars['L_FAQ'] : $this->lang('L_FAQ'); ?></a>&nbsp; 
					<a href="<?php echo isset($this->vars['U_SEARCH']) ? $this->vars['U_SEARCH'] : $this->lang('U_SEARCH'); ?>" title="<?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?>" class="icon-search"><?php echo isset($this->vars['L_SEARCH']) ? $this->vars['L_SEARCH'] : $this->lang('L_SEARCH'); ?></a>&nbsp; 
					<a href="<?php echo isset($this->vars['U_MEMBERLIST']) ? $this->vars['U_MEMBERLIST'] : $this->lang('U_MEMBERLIST'); ?>" title="<?php echo isset($this->vars['L_MEMBERLIST_EXPLAIN']) ? $this->vars['L_MEMBERLIST_EXPLAIN'] : $this->lang('L_MEMBERLIST_EXPLAIN'); ?>" class="icon-members"><?php echo isset($this->vars['L_MEMBERLIST']) ? $this->vars['L_MEMBERLIST'] : $this->lang('L_MEMBERLIST'); ?></a>&nbsp; 
						<?php

$switch_user_logged_out_count = ( isset($this->_tpldata['switch_user_logged_out.']) ) ?  sizeof($this->_tpldata['switch_user_logged_out.']) : 0;
for ($switch_user_logged_out_i = 0; $switch_user_logged_out_i < $switch_user_logged_out_count; $switch_user_logged_out_i++)
{
 $switch_user_logged_out_item = &$this->_tpldata['switch_user_logged_out.'][$switch_user_logged_out_i];
 $switch_user_logged_out_item['S_ROW_COUNT'] = $switch_user_logged_out_i;
 $switch_user_logged_out_item['S_NUM_ROWS'] = $switch_user_logged_out_count;

?>
					<a href="<?php echo isset($this->vars['U_REGISTER']) ? $this->vars['U_REGISTER'] : $this->lang('U_REGISTER'); ?>" class="icon-register"><?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?></a>&nbsp; 
					<?php

} // END switch_user_logged_out

if(isset($switch_user_logged_out_item)) { unset($switch_user_logged_out_item); } 

?>
					<a href="<?php echo isset($this->vars['U_LOGIN_LOGOUT']) ? $this->vars['U_LOGIN_LOGOUT'] : $this->lang('U_LOGIN_LOGOUT'); ?>" title="<?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?>" class="icon-logout"><?php echo isset($this->vars['L_LOGIN_LOGOUT']) ? $this->vars['L_LOGIN_LOGOUT'] : $this->lang('L_LOGIN_LOGOUT'); ?></a>
				</li>
			</ul>
			<span class="corners-bottom"><span></span></span></div>
</div>
<p></p>
<div class="panel">
	<div class="inner"><span class="corners-top"><span></span></span>
	<div class="content">
		<h2><?php echo isset($this->vars['SITENAME']) ? $this->vars['SITENAME'] : $this->lang('SITENAME'); ?> - <?php echo isset($this->vars['REGISTRATION']) ? $this->vars['REGISTRATION'] : $this->lang('REGISTRATION'); ?></h2>
		<p><?php echo isset($this->vars['AGREEMENT']) ? $this->vars['AGREEMENT'] : $this->lang('AGREEMENT'); ?></p>
		</div>
		<span class="corners-bottom"><span></span></span></div>
	</div>
<div class="panel">
		<div class="inner"><span class="corners-top"><span></span></span>
		<fieldset class="submit-buttons">
			<a href="<?php echo isset($this->vars['U_AGREE_OVER13']) ? $this->vars['U_AGREE_OVER13'] : $this->lang('U_AGREE_OVER13'); ?>" class="button2"><?php echo isset($this->vars['AGREE_OVER_13']) ? $this->vars['AGREE_OVER_13'] : $this->lang('AGREE_OVER_13'); ?></a>&nbsp; <a href="<?php echo isset($this->vars['U_AGREE_UNDER13']) ? $this->vars['U_AGREE_UNDER13'] : $this->lang('U_AGREE_UNDER13'); ?>" class="button2"><?php echo isset($this->vars['AGREE_UNDER_13']) ? $this->vars['AGREE_UNDER_13'] : $this->lang('AGREE_UNDER_13'); ?></a>&nbsp; <a href="<?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?>" class="button2"><?php echo isset($this->vars['DO_NOT_AGREE']) ? $this->vars['DO_NOT_AGREE'] : $this->lang('DO_NOT_AGREE'); ?></a>
		</fieldset>
		<span class="corners-bottom"><span></span></span></div>
	</div>
<div class="navbar">
		<div class="inner"><span class="corners-top"><span></span></span>
		<ul class="linklist">
			<li>
				<a class="icon-home" href="<?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?>"><?php echo isset($this->vars['L_INDEX']) ? $this->vars['L_INDEX'] : $this->lang('L_INDEX'); ?></a>  
			</li>
			<li class="rightside"><a href="<?php echo isset($this->vars['U_GROUP_CP']) ? $this->vars['U_GROUP_CP'] : $this->lang('U_GROUP_CP'); ?>"><?php echo isset($this->vars['L_USERGROUPS']) ? $this->vars['L_USERGROUPS'] : $this->lang('L_USERGROUPS'); ?></a> &bull; <?php echo isset($this->vars['S_TIMEZONE']) ? $this->vars['S_TIMEZONE'] : $this->lang('S_TIMEZONE'); ?></li>
		</ul>
		<span class="corners-bottom"><span></span></span></div>
</div>