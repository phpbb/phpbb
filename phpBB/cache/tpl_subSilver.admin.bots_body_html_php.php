<?php

// eXtreme Styles mod cache. Generated on Fri, 26 Oct 2018 14:46:57 +0000 (time=1540565217)

?>
<h1><?php echo isset($this->vars['L_BOTS_TITLE']) ? $this->vars['L_BOTS_TITLE'] : $this->lang('L_BOTS_TITLE'); ?></h1>

<p><?php echo isset($this->vars['L_BOTS_EXPLAIN']) ? $this->vars['L_BOTS_EXPLAIN'] : $this->lang('L_BOTS_EXPLAIN'); ?></p>

<form action="<?php echo isset($this->vars['S_BOTS_ACTION']) ? $this->vars['S_BOTS_ACTION'] : $this->lang('S_BOTS_ACTION'); ?>" method="post"><table width="90%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">

	<tr>
		<th nowrap="nowrap"><?php echo isset($this->vars['L_BOT_NAME']) ? $this->vars['L_BOT_NAME'] : $this->lang('L_BOT_NAME'); ?></th>
		<th nowrap="nowrap"><?php echo isset($this->vars['L_BOT_PAGES']) ? $this->vars['L_BOT_PAGES'] : $this->lang('L_BOT_PAGES'); ?></th>
		<th nowrap="nowrap"><?php echo isset($this->vars['L_BOT_VISITS']) ? $this->vars['L_BOT_VISITS'] : $this->lang('L_BOT_VISITS'); ?></th>
		<th nowrap="nowrap"><?php echo isset($this->vars['L_BOT_LAST_VISIT']) ? $this->vars['L_BOT_LAST_VISIT'] : $this->lang('L_BOT_LAST_VISIT'); ?></th>
		<th colspan="2" nowrap="nowrap"><?php echo isset($this->vars['L_BOT_OPTIONS']) ? $this->vars['L_BOT_OPTIONS'] : $this->lang('L_BOT_OPTIONS'); ?></th>
		<th nowrap="nowrap"><?php echo isset($this->vars['L_BOT_MARK']) ? $this->vars['L_BOT_MARK'] : $this->lang('L_BOT_MARK'); ?></th>
	</tr>

	<?php

$botrow_count = ( isset($this->_tpldata['botrow.']) ) ?  sizeof($this->_tpldata['botrow.']) : 0;
for ($botrow_i = 0; $botrow_i < $botrow_count; $botrow_i++)
{
 $botrow_item = &$this->_tpldata['botrow.'][$botrow_i];
 $botrow_item['S_ROW_COUNT'] = $botrow_i;
 $botrow_item['S_NUM_ROWS'] = $botrow_count;

?>

	<tr>
		<td class="<?php echo isset($botrow_item['ROW_CLASS']) ? $botrow_item['ROW_CLASS'] : ''; ?>" width="50%"><?php echo isset($botrow_item['BOT_NAME']) ? $botrow_item['BOT_NAME'] : ''; ?></td>
		<td class="<?php echo isset($botrow_item['ROW_CLASS']) ? $botrow_item['ROW_CLASS'] : ''; ?>" width="10%" align="center" nowrap="nowrap"><?php echo isset($botrow_item['PAGES']) ? $botrow_item['PAGES'] : ''; ?></td>
		<td class="<?php echo isset($botrow_item['ROW_CLASS']) ? $botrow_item['ROW_CLASS'] : ''; ?>" width="10%" align="center" nowrap="nowrap"><?php echo isset($botrow_item['VISITS']) ? $botrow_item['VISITS'] : ''; ?></td>
		<td class="<?php echo isset($botrow_item['ROW_CLASS']) ? $botrow_item['ROW_CLASS'] : ''; ?>" width="20%" align="center" nowrap="nowrap"><?php echo isset($botrow_item['LAST_VISIT']) ? $botrow_item['LAST_VISIT'] : ''; ?></td>
		<td class="<?php echo isset($botrow_item['ROW_CLASS']) ? $botrow_item['ROW_CLASS'] : ''; ?>" width="3%" align="center">&nbsp;<a href="<?php echo isset($this->vars['S_BOTS_ACTION']) ? $this->vars['S_BOTS_ACTION'] : $this->lang('S_BOTS_ACTION'); ?>&id=<?php echo isset($botrow_item['ROW_NUMBER']) ? $botrow_item['ROW_NUMBER'] : ''; ?>&action=edit"><?php echo isset($this->vars['L_BOT_EDIT']) ? $this->vars['L_BOT_EDIT'] : $this->lang('L_BOT_EDIT'); ?></a>&nbsp;</td>
		<td class="<?php echo isset($botrow_item['ROW_CLASS']) ? $botrow_item['ROW_CLASS'] : ''; ?>" width="3%" align="center">&nbsp;<a href="<?php echo isset($this->vars['S_BOTS_ACTION']) ? $this->vars['S_BOTS_ACTION'] : $this->lang('S_BOTS_ACTION'); ?>&id=<?php echo isset($botrow_item['ROW_NUMBER']) ? $botrow_item['ROW_NUMBER'] : ''; ?>&action=delete"><?php echo isset($this->vars['L_BOT_DELETE']) ? $this->vars['L_BOT_DELETE'] : $this->lang('L_BOT_DELETE'); ?></a>&nbsp;</td>
		<td class="<?php echo isset($botrow_item['ROW_CLASS']) ? $botrow_item['ROW_CLASS'] : ''; ?>" width="3%" align="center"><input type="checkbox" name="mark[]" value="<?php echo isset($botrow_item['ROW_NUMBER']) ? $botrow_item['ROW_NUMBER'] : ''; ?>" /></td>	
	</tr>

	<?php

} // END botrow

if(isset($botrow_item)) { unset($botrow_item); } 

?>

	<?php

$nobotrow_count = ( isset($this->_tpldata['nobotrow.']) ) ?  sizeof($this->_tpldata['nobotrow.']) : 0;
for ($nobotrow_i = 0; $nobotrow_i < $nobotrow_count; $nobotrow_i++)
{
 $nobotrow_item = &$this->_tpldata['nobotrow.'][$nobotrow_i];
 $nobotrow_item['S_ROW_COUNT'] = $nobotrow_i;
 $nobotrow_item['S_NUM_ROWS'] = $nobotrow_count;

?>

	<tr>
		<td class="row2" align="center" colspan="8"><br /><?php echo isset($nobotrow_item['NO_BOTS']) ? $nobotrow_item['NO_BOTS'] : ''; ?><br /><br /></td>
	</tr>

	<?php

} // END nobotrow

if(isset($nobotrow_item)) { unset($nobotrow_item); } 

?>

	<tr>
		<td class="cat" colspan="8"><table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td><input type="submit" class="liteoption" name="add" value="<?php echo isset($this->vars['L_BOT_ADD']) ? $this->vars['L_BOT_ADD'] : $this->lang('L_BOT_ADD'); ?>" /></td>
				<td align="right"><select name="action"><option value="edit"><?php echo isset($this->vars['L_BOT_EDIT']) ? $this->vars['L_BOT_EDIT'] : $this->lang('L_BOT_EDIT'); ?></option><option value="delete"><?php echo isset($this->vars['L_BOT_DELETE']) ? $this->vars['L_BOT_DELETE'] : $this->lang('L_BOT_DELETE'); ?></option></select> <input type="submit" class="liteoption" name="submit" value="<?php echo isset($this->vars['L_BOT_SUBMIT']) ? $this->vars['L_BOT_SUBMIT'] : $this->lang('L_BOT_SUBMIT'); ?>" /></td>
			</tr>
		</table></td>
	</tr>

</table></form>

<br />

<h1><?php echo isset($this->vars['L_BOTS_TITLE_PENDING']) ? $this->vars['L_BOTS_TITLE_PENDING'] : $this->lang('L_BOTS_TITLE_PENDING'); ?></h1>

<p><?php echo isset($this->vars['L_BOTS_EXPLAIN_PENDING']) ? $this->vars['L_BOTS_EXPLAIN_PENDING'] : $this->lang('L_BOTS_EXPLAIN_PENDING'); ?></p>

<table width="90%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">

	<tr>
		<th nowrap="nowrap"><?php echo isset($this->vars['L_BOT_NAME']) ? $this->vars['L_BOT_NAME'] : $this->lang('L_BOT_NAME'); ?></th>
		<th nowrap="nowrap"><?php echo isset($this->vars['L_BOT_IP']) ? $this->vars['L_BOT_IP'] : $this->lang('L_BOT_IP'); ?></th>
		<th nowrap="nowrap"><?php echo isset($this->vars['L_BOT_AGENT']) ? $this->vars['L_BOT_AGENT'] : $this->lang('L_BOT_AGENT'); ?></th>
		<th colspan="2" nowrap="nowrap"><?php echo isset($this->vars['L_BOT_OPTIONS']) ? $this->vars['L_BOT_OPTIONS'] : $this->lang('L_BOT_OPTIONS'); ?></th>
	</tr>

	<?php

$pendingrow_count = ( isset($this->_tpldata['pendingrow.']) ) ?  sizeof($this->_tpldata['pendingrow.']) : 0;
for ($pendingrow_i = 0; $pendingrow_i < $pendingrow_count; $pendingrow_i++)
{
 $pendingrow_item = &$this->_tpldata['pendingrow.'][$pendingrow_i];
 $pendingrow_item['S_ROW_COUNT'] = $pendingrow_i;
 $pendingrow_item['S_NUM_ROWS'] = $pendingrow_count;

?>

	<tr>
		<td class="<?php echo isset($pendingrow_item['ROW_CLASS']) ? $pendingrow_item['ROW_CLASS'] : ''; ?>" width="30%"><?php echo isset($pendingrow_item['BOT_NAME']) ? $pendingrow_item['BOT_NAME'] : ''; ?></td>
		<td class="<?php echo isset($pendingrow_item['ROW_CLASS']) ? $pendingrow_item['ROW_CLASS'] : ''; ?>" width="20%" align="center" nowrap="nowrap"><?php echo isset($pendingrow_item['IP']) ? $pendingrow_item['IP'] : ''; ?></td>
		<td class="<?php echo isset($pendingrow_item['ROW_CLASS']) ? $pendingrow_item['ROW_CLASS'] : ''; ?>" width="20%" align="center" nowrap="nowrap"><?php echo isset($pendingrow_item['AGENT']) ? $pendingrow_item['AGENT'] : ''; ?></td>
		<td class="<?php echo isset($pendingrow_item['ROW_CLASS']) ? $pendingrow_item['ROW_CLASS'] : ''; ?>" width="3%" align="center">&nbsp;<a href="<?php echo isset($this->vars['S_BOTS_ACTION']) ? $this->vars['S_BOTS_ACTION'] : $this->lang('S_BOTS_ACTION'); ?>&id=<?php echo isset($pendingrow_item['ROW_NUMBER']) ? $pendingrow_item['ROW_NUMBER'] : ''; ?>&pending=<?php echo isset($pendingrow_item['PENDING_NUMBER']) ? $pendingrow_item['PENDING_NUMBER'] : ''; ?>&data=<?php echo isset($pendingrow_item['PENDING_DATA']) ? $pendingrow_item['PENDING_DATA'] : ''; ?>&action=ignore_pending"><?php echo isset($this->vars['L_BOT_IGNORE']) ? $this->vars['L_BOT_IGNORE'] : $this->lang('L_BOT_IGNORE'); ?></a>&nbsp;</td>
		<td class="<?php echo isset($pendingrow_item['ROW_CLASS']) ? $pendingrow_item['ROW_CLASS'] : ''; ?>" width="3%" align="center">&nbsp;<a href="<?php echo isset($this->vars['S_BOTS_ACTION']) ? $this->vars['S_BOTS_ACTION'] : $this->lang('S_BOTS_ACTION'); ?>&id=<?php echo isset($pendingrow_item['ROW_NUMBER']) ? $pendingrow_item['ROW_NUMBER'] : ''; ?>&pending=<?php echo isset($pendingrow_item['PENDING_NUMBER']) ? $pendingrow_item['PENDING_NUMBER'] : ''; ?>&data=<?php echo isset($pendingrow_item['PENDING_DATA']) ? $pendingrow_item['PENDING_DATA'] : ''; ?>&action=add_pending"><?php echo isset($this->vars['L_BOT_ADD']) ? $this->vars['L_BOT_ADD'] : $this->lang('L_BOT_ADD'); ?></a>&nbsp;</td>
	</tr>

	<?php

} // END pendingrow

if(isset($pendingrow_item)) { unset($pendingrow_item); } 

?>

	<?php

$nopendingrow_count = ( isset($this->_tpldata['nopendingrow.']) ) ?  sizeof($this->_tpldata['nopendingrow.']) : 0;
for ($nopendingrow_i = 0; $nopendingrow_i < $nopendingrow_count; $nopendingrow_i++)
{
 $nopendingrow_item = &$this->_tpldata['nopendingrow.'][$nopendingrow_i];
 $nopendingrow_item['S_ROW_COUNT'] = $nopendingrow_i;
 $nopendingrow_item['S_NUM_ROWS'] = $nopendingrow_count;

?>

	<tr>
		<td class="row2" align="center" colspan="5"><br /><?php echo isset($nopendingrow_item['NO_BOTS']) ? $nopendingrow_item['NO_BOTS'] : ''; ?><br /><br /></td>
	</tr>

	<?php

} // END nopendingrow

if(isset($nopendingrow_item)) { unset($nopendingrow_item); } 

?>

</table>

<br clear="all" />
</table>

<br clear="all" />
