<?php

// eXtreme Styles mod cache. Generated on Tue, 09 Oct 2018 10:11:58 +0000 (time=1539079918)

?>
<h1><?php echo isset($this->vars['L_FLAGS_TITLE']) ? $this->vars['L_FLAGS_TITLE'] : $this->lang('L_FLAGS_TITLE'); ?></h1>

<p><?php echo isset($this->vars['L_FLAGS_TEXT']) ? $this->vars['L_FLAGS_TEXT'] : $this->lang('L_FLAGS_TEXT'); ?></p>

<form action="<?php echo isset($this->vars['S_FLAG_ACTION']) ? $this->vars['S_FLAG_ACTION'] : $this->lang('S_FLAG_ACTION'); ?>" method="post"><table class="forumline" cellpadding="4" cellspacing="1" border="0" align="center">
	<tr>
		<th class="thTop" colspan="2"><?php echo isset($this->vars['L_FLAGS_TITLE']) ? $this->vars['L_FLAGS_TITLE'] : $this->lang('L_FLAGS_TITLE'); ?></th>
	</tr>
	<tr>
		<td class="row1" width="38%"><span class="gen"><?php echo isset($this->vars['L_FLAG_NAME']) ? $this->vars['L_FLAG_NAME'] : $this->lang('L_FLAG_NAME'); ?>:</span></td>
		<td class="row2"><input class="post" type="text" name="title" size="35" maxlength="40" value="<?php echo isset($this->vars['FLAG']) ? $this->vars['FLAG'] : $this->lang('FLAG'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="38%"><span class="gen"><?php echo isset($this->vars['L_FLAG_IMAGE']) ? $this->vars['L_FLAG_IMAGE'] : $this->lang('L_FLAG_IMAGE'); ?>:</span><br />
		<span class="gensmall"><?php echo isset($this->vars['L_FLAG_IMAGE_EXPLAIN']) ? $this->vars['L_FLAG_IMAGE_EXPLAIN'] : $this->lang('L_FLAG_IMAGE_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" name="flag_image" size="40" maxlength="255" value="<?php echo isset($this->vars['IMAGE']) ? $this->vars['IMAGE'] : $this->lang('IMAGE'); ?>" /><br /><?php echo isset($this->vars['IMAGE_DISPLAY']) ? $this->vars['IMAGE_DISPLAY'] : $this->lang('IMAGE_DISPLAY'); ?></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center"><input type="submit" name="submit" value="<?php echo isset($this->vars['L_SUBMIT']) ? $this->vars['L_SUBMIT'] : $this->lang('L_SUBMIT'); ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo isset($this->vars['L_RESET']) ? $this->vars['L_RESET'] : $this->lang('L_RESET'); ?>" class="liteoption" /></td>
	</tr>
</table>
<?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?></form>
