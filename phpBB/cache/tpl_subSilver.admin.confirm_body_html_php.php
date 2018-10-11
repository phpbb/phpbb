<?php

// eXtreme Styles mod cache. Generated on Mon, 08 Oct 2018 14:01:39 +0000 (time=1539007299)

?> 
<table class="forumline" width="100%" cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th class="thHead" height="25" valign="middle"><?php echo isset($this->vars['MESSAGE_TITLE']) ? $this->vars['MESSAGE_TITLE'] : $this->lang('MESSAGE_TITLE'); ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><form action="<?php echo isset($this->vars['S_CONFIRM_ACTION']) ? $this->vars['S_CONFIRM_ACTION'] : $this->lang('S_CONFIRM_ACTION'); ?>" method="post"><span class="gen"><br /><?php echo isset($this->vars['MESSAGE_TEXT']) ? $this->vars['MESSAGE_TEXT'] : $this->lang('MESSAGE_TEXT'); ?><br /><br /><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="confirm" value="<?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>" class="mainoption" />&nbsp;&nbsp;<input type="submit" name="cancel" value="<?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?>" class="liteoption" /></span></form></td>
	</tr>
</table>

<br clear="all" />
