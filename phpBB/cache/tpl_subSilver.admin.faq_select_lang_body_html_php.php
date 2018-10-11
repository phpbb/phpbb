<?php

// eXtreme Styles mod cache. Generated on Mon, 08 Oct 2018 23:54:14 +0000 (time=1539042854)

?><h1><?php echo isset($this->vars['L_TITLE']) ? $this->vars['L_TITLE'] : $this->lang('L_TITLE'); ?></h1>
<p><?php echo isset($this->vars['L_EXPLAIN']) ? $this->vars['L_EXPLAIN'] : $this->lang('L_EXPLAIN'); ?></p>

<form action="<?php echo isset($this->vars['S_ACTION']) ? $this->vars['S_ACTION'] : $this->lang('S_ACTION'); ?>" method="post">
<table class="forumline">
<tr><th colspan="2"><?php echo isset($this->vars['L_TITLE']) ? $this->vars['L_TITLE'] : $this->lang('L_TITLE'); ?></th></tr>
<tr>
	<td class="row1"><?php echo isset($this->vars['L_LANGUAGE']) ? $this->vars['L_LANGUAGE'] : $this->lang('L_LANGUAGE'); ?></td>
	<td class="row2"><?php echo isset($this->vars['LANGUAGE_SELECT']) ? $this->vars['LANGUAGE_SELECT'] : $this->lang('LANGUAGE_SELECT'); ?></td>
</tr>
<tr><td class="cat tdalignc" colspan="2"><input type="submit" name="submit" value="<?php echo isset($this->vars['L_SUBMIT']) ? $this->vars['L_SUBMIT'] : $this->lang('L_SUBMIT'); ?>" class="mainoption" /></td></tr>
</table>
</form>

<br clear="all" />