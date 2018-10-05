<?php

// eXtreme Styles mod cache. Generated on Wed, 26 Sep 2018 04:17:50 +0000 (time=1537935470)

?>
<?php if ($this->vars['S_DISPLAY_JUMPBOX']) {  ?>
	<form method="post" name="jumpbox" action="<?php echo isset($this->vars['S_JUMPBOX_ACTION']) ? $this->vars['S_JUMPBOX_ACTION'] : $this->lang('S_JUMPBOX_ACTION'); ?>" onsubmit="if(document.jumpbox.f.value == -1){return false;}">

	<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td nowrap="nowrap"><span class="gensmall"><?php if ($this->vars['S_IN_MCP'] && $this->vars['S_MERGE_SELECT']) {  ?><?php echo isset($this->vars['L_SELECT_TOPICS_FROM']) ? $this->vars['L_SELECT_TOPICS_FROM'] : $this->lang('L_SELECT_TOPICS_FROM'); ?><?php } elseif ($this->vars['S_IN_MCP']) {  ?><?php echo isset($this->vars['L_MODERATE_FORUM']) ? $this->vars['L_MODERATE_FORUM'] : $this->lang('L_MODERATE_FORUM'); ?><?php } else { ?><?php echo isset($this->vars['L_JUMP_TO']) ? $this->vars['L_JUMP_TO'] : $this->lang('L_JUMP_TO'); ?><?php } ?>:</span>&nbsp;<select name="f" onchange="if(this.options[this.selectedIndex].value != -1){ document.forms['jumpbox'].submit() }">

		<?php

$jumpbox_forums_count = ( isset($this->_tpldata['jumpbox_forums.']) ) ?  sizeof($this->_tpldata['jumpbox_forums.']) : 0;
for ($jumpbox_forums_i = 0; $jumpbox_forums_i < $jumpbox_forums_count; $jumpbox_forums_i++)
{
 $jumpbox_forums_item = &$this->_tpldata['jumpbox_forums.'][$jumpbox_forums_i];
 $jumpbox_forums_item['S_ROW_COUNT'] = $jumpbox_forums_i;
 $jumpbox_forums_item['S_NUM_ROWS'] = $jumpbox_forums_count;

?>
			<?php if ($jumpbox_forums_item['S_FORUM_COUNT'] == 1) {  ?><option value="-1">------------------</option><?php } ?>
			<option value="<?php echo isset($jumpbox_forums_item['FORUM_ID']) ? $jumpbox_forums_item['FORUM_ID'] : ''; ?>"<?php echo isset($jumpbox_forums_item['SELECTED']) ? $jumpbox_forums_item['SELECTED'] : ''; ?>><?php

$level_count = ( isset($jumpbox_forums_item['level.']) ) ? sizeof($jumpbox_forums_item['level.']) : 0;
for ($level_i = 0; $level_i < $level_count; $level_i++)
{
 $level_item = &$jumpbox_forums_item['level.'][$level_i];
 $level_item['S_ROW_COUNT'] = $level_i;
 $level_item['S_NUM_ROWS'] = $level_count;

?>&nbsp; &nbsp;<?php

} // END level

if(isset($level_item)) { unset($level_item); } 

?><?php echo isset($jumpbox_forums_item['FORUM_NAME']) ? $jumpbox_forums_item['FORUM_NAME'] : ''; ?></option>
		<?php

} // END jumpbox_forums

if(isset($jumpbox_forums_item)) { unset($jumpbox_forums_item); } 

?>

		</select>&nbsp;<input class="btnlite" type="submit" value="<?php echo isset($this->vars['L_GO']) ? $this->vars['L_GO'] : $this->lang('L_GO'); ?>" /></td>
	</tr>
	</table>

	</form>
<?php } ?>