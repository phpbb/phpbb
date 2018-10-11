<?php

// eXtreme Styles mod cache. Generated on Wed, 10 Oct 2018 01:15:31 +0000 (time=1539134131)

?>
<h1><?php echo isset($this->vars['L_EMAIL_TITLE']) ? $this->vars['L_EMAIL_TITLE'] : $this->lang('L_EMAIL_TITLE'); ?></h1>

<p><?php echo isset($this->vars['L_EMAIL_EXPLAIN']) ? $this->vars['L_EMAIL_EXPLAIN'] : $this->lang('L_EMAIL_EXPLAIN'); ?></p>

<form method="post" action="<?php echo isset($this->vars['S_USER_ACTION']) ? $this->vars['S_USER_ACTION'] : $this->lang('S_USER_ACTION'); ?>">

<?php echo isset($this->vars['ERROR_BOX']) ? $this->vars['ERROR_BOX'] : $this->lang('ERROR_BOX'); ?>

<table cellspacing="1" cellpadding="4" border="0" align="center" class="forumline">
	<tr> 
	  <th class="thHead" colspan="2"><?php echo isset($this->vars['L_COMPOSE']) ? $this->vars['L_COMPOSE'] : $this->lang('L_COMPOSE'); ?></th>
	</tr>
	<tr> 
	  <td class="row1" align="right"><b><?php echo isset($this->vars['L_RECIPIENTS']) ? $this->vars['L_RECIPIENTS'] : $this->lang('L_RECIPIENTS'); ?></b></td>
	  <td class="row2" align="left"><?php echo isset($this->vars['S_GROUP_SELECT']) ? $this->vars['S_GROUP_SELECT'] : $this->lang('S_GROUP_SELECT'); ?></td>
	</tr>
	<tr> 
	  <td class="row1" align="right"><b><?php echo isset($this->vars['L_EMAIL_SUBJECT']) ? $this->vars['L_EMAIL_SUBJECT'] : $this->lang('L_EMAIL_SUBJECT'); ?></b></td>
	  <td class="row2"><span class="gen"><input class="post" type="text" name="subject" size="45" maxlength="100" tabindex="2" class="post" value="<?php echo isset($this->vars['SUBJECT']) ? $this->vars['SUBJECT'] : $this->lang('SUBJECT'); ?>" /></span></td>
	</tr>
	<tr> 
	  <td class="row1" align="right" valign="top"> <span class="gen"><b><?php echo isset($this->vars['L_EMAIL_MSG']) ? $this->vars['L_EMAIL_MSG'] : $this->lang('L_EMAIL_MSG'); ?></b></span> 
	  <td class="row2"><span class="gen"> <textarea name="message" rows="15" cols="35" wrap="virtual" style="width:450px" tabindex="3" class="post"><?php echo isset($this->vars['MESSAGE']) ? $this->vars['MESSAGE'] : $this->lang('MESSAGE'); ?></textarea></span> 
	</tr>
	<tr> 
	  <td class="catBottom" align="center" colspan="2"><input type="submit" value="<?php echo isset($this->vars['L_EMAIL']) ? $this->vars['L_EMAIL'] : $this->lang('L_EMAIL'); ?>" name="submit" class="mainoption" /></td>
	</tr>
</table>

</form>
