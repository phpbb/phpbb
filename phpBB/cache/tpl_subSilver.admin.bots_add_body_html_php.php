<?php

// eXtreme Styles mod cache. Generated on Fri, 26 Oct 2018 14:47:03 +0000 (time=1540565223)

?>
<h1><?php echo isset($this->vars['L_BOTS_TITLE']) ? $this->vars['L_BOTS_TITLE'] : $this->lang('L_BOTS_TITLE'); ?></h1>

<p><?php echo isset($this->vars['L_BOTS_EXPLAIN']) ? $this->vars['L_BOTS_EXPLAIN'] : $this->lang('L_BOTS_EXPLAIN'); ?></p>

<form action="<?php echo isset($this->vars['S_BOTS_ACTION']) ? $this->vars['S_BOTS_ACTION'] : $this->lang('S_BOTS_ACTION'); ?>" method="post"><table width="90%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
		<th colspan="2"><?php echo isset($this->vars['L_BOTS_TITLE']) ? $this->vars['L_BOTS_TITLE'] : $this->lang('L_BOTS_TITLE'); ?></th>
	</tr>
	<?php

$errorrow_count = ( isset($this->_tpldata['errorrow.']) ) ?  sizeof($this->_tpldata['errorrow.']) : 0;
for ($errorrow_i = 0; $errorrow_i < $errorrow_count; $errorrow_i++)
{
 $errorrow_item = &$this->_tpldata['errorrow.'][$errorrow_i];
 $errorrow_item['S_ROW_COUNT'] = $errorrow_i;
 $errorrow_item['S_NUM_ROWS'] = $errorrow_count;

?>

	<tr>
		<td class="row3" colspan="2" align="center"><span style="color:red"><?php echo isset($errorrow_item['BOT_ERROR']) ? $errorrow_item['BOT_ERROR'] : ''; ?></span></td>
	</tr>

	<?php

} // END errorrow

if(isset($errorrow_item)) { unset($errorrow_item); } 

?>

	<tr>
		<td class="row1" width="40%"><span class="gen"><b><?php echo isset($this->vars['L_BOT_NAME']) ? $this->vars['L_BOT_NAME'] : $this->lang('L_BOT_NAME'); ?>: </b></span><br /><span class="gensmall"><?php echo isset($this->vars['L_BOT_NAME_EXPLAIN']) ? $this->vars['L_BOT_NAME_EXPLAIN'] : $this->lang('L_BOT_NAME_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" name="bot_name" size="30" maxlength="1000" value="<?php echo isset($this->vars['BOT_NAME']) ? $this->vars['BOT_NAME'] : $this->lang('BOT_NAME'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><span class="gen"><b><?php echo isset($this->vars['L_BOT_AGENT']) ? $this->vars['L_BOT_AGENT'] : $this->lang('L_BOT_AGENT'); ?>: </b></span><br /><span class="gensmall"><?php echo isset($this->vars['L_BOT_AGENT_EXPLAIN']) ? $this->vars['L_BOT_AGENT_EXPLAIN'] : $this->lang('L_BOT_AGENT_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" name="bot_agent" size="30" maxlength="1000" value="<?php echo isset($this->vars['BOT_AGENT']) ? $this->vars['BOT_AGENT'] : $this->lang('BOT_AGENT'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><span class="gen"><b><?php echo isset($this->vars['L_BOT_IP']) ? $this->vars['L_BOT_IP'] : $this->lang('L_BOT_IP'); ?>: </b></span><br /><span class="gensmall"><?php echo isset($this->vars['L_BOT_IP_EXPLAIN']) ? $this->vars['L_BOT_IP_EXPLAIN'] : $this->lang('L_BOT_IP_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" name="bot_ip" size="30" maxlength="1000" value="<?php echo isset($this->vars['BOT_IP']) ? $this->vars['BOT_IP'] : $this->lang('BOT_IP'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><span class="gen"><b><?php echo isset($this->vars['L_BOT_STYLE']) ? $this->vars['L_BOT_STYLE'] : $this->lang('L_BOT_STYLE'); ?>: </b></span><br /><span class="gensmall"><?php echo isset($this->vars['L_BOT_STYLE_EXPLAIN']) ? $this->vars['L_BOT_STYLE_EXPLAIN'] : $this->lang('L_BOT_STYLE_EXPLAIN'); ?></span></td>
		<td class="row2"><select name="style"><?php echo isset($this->vars['BOT_STYLE']) ? $this->vars['BOT_STYLE'] : $this->lang('BOT_STYLE'); ?></select></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="mainoption" type="submit" name="submit" value="<?php echo isset($this->vars['L_BOT_SUBMIT']) ? $this->vars['L_BOT_SUBMIT'] : $this->lang('L_BOT_SUBMIT'); ?>" />&nbsp;&nbsp;<input class="liteoption" type="reset" value="<?php echo isset($this->vars['L_BOT_RESET']) ? $this->vars['L_BOT_RESET'] : $this->lang('L_BOT_RESET'); ?>" /></td>
	</tr>
</table></form>
