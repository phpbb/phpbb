<?php

// eXtreme Styles mod cache. Generated on Sat, 26 May 2018 20:57:39 +0000 (time=1527368259)

?><h1><?php echo isset($this->vars['L_FORUM_TITLE']) ? $this->vars['L_FORUM_TITLE'] : $this->lang('L_FORUM_TITLE'); ?></h1>
<p><?php echo isset($this->vars['L_FORUM_EXPLAIN']) ? $this->vars['L_FORUM_EXPLAIN'] : $this->lang('L_FORUM_EXPLAIN'); ?></p>
<form method="post" action="<?php echo isset($this->vars['S_FORUM_ACTION']) ? $this->vars['S_FORUM_ACTION'] : $this->lang('S_FORUM_ACTION'); ?>">
<table cellspacing="1">
<col class="row1" /><col class="row1" /><col class="row1" /><col class="row2" />
	<tbody>
	<?php

$catrow_count = ( isset($this->_tpldata['catrow.']) ) ?  sizeof($this->_tpldata['catrow.']) : 0;
for ($catrow_i = 0; $catrow_i < $catrow_count; $catrow_i++)
{
 $catrow_item = &$this->_tpldata['catrow.'][$catrow_i];
 $catrow_item['S_ROW_COUNT'] = $catrow_i;
 $catrow_item['S_NUM_ROWS'] = $catrow_count;

?>
	<tr>
		<td colspan="3"><strong><a href="<?php echo isset($catrow_item['U_VIEWCAT']) ? $catrow_item['U_VIEWCAT'] : ''; ?>" target="_parent"><?php echo isset($catrow_item['CAT_DESC']) ? $catrow_item['CAT_DESC'] : ''; ?></a></strong></td>
		<td style="text-align:right">&nbsp;<a href="<?php echo isset($catrow_item['U_CAT_MOVE_UP']) ? $catrow_item['U_CAT_MOVE_UP'] : ''; ?>" title="<?php echo isset($this->vars['L_MOVE_UP']) ? $this->vars['L_MOVE_UP'] : $this->lang('L_MOVE_UP'); ?>"><img src="../templates/prosilver/admin/images/icon_up.gif" alt="" /></a>&nbsp;<a href="<?php echo isset($catrow_item['U_CAT_MOVE_DOWN']) ? $catrow_item['U_CAT_MOVE_DOWN'] : ''; ?>" title="<?php echo isset($this->vars['L_MOVE_DOWN']) ? $this->vars['L_MOVE_DOWN'] : $this->lang('L_MOVE_DOWN'); ?>"><img src="../templates/prosilver/admin/images/icon_down.gif" alt="" /></a>&nbsp;<a href="<?php echo isset($catrow_item['U_CAT_EDIT']) ? $catrow_item['U_CAT_EDIT'] : ''; ?>" title="<?php echo isset($this->vars['L_EDIT']) ? $this->vars['L_EDIT'] : $this->lang('L_EDIT'); ?>"><img src="../templates/prosilver/admin/images/icon_edit.gif" alt="" /></a>&nbsp;<a href="<?php echo isset($catrow_item['U_CAT_DELETE']) ? $catrow_item['U_CAT_DELETE'] : ''; ?>" title="<?php echo isset($this->vars['L_DELETE']) ? $this->vars['L_DELETE'] : $this->lang('L_DELETE'); ?>"><img src="../templates/prosilver/admin/images/icon_delete.gif" alt="" /></a>&nbsp;</td>
	</tr>
	<?php

$forumrow_count = ( isset($catrow_item['forumrow.']) ) ? sizeof($catrow_item['forumrow.']) : 0;
for ($forumrow_i = 0; $forumrow_i < $forumrow_count; $forumrow_i++)
{
 $forumrow_item = &$catrow_item['forumrow.'][$forumrow_i];
 $forumrow_item['S_ROW_COUNT'] = $forumrow_i;
 $forumrow_item['S_NUM_ROWS'] = $forumrow_count;

?>
	<tr> 
		<td><a href="<?php echo isset($forumrow_item['U_VIEWFORUM']) ? $forumrow_item['U_VIEWFORUM'] : ''; ?>" target="_parent"><?php echo isset($forumrow_item['FORUM_NAME']) ? $forumrow_item['FORUM_NAME'] : ''; ?></a><br /><?php echo isset($forumrow_item['FORUM_DESC']) ? $forumrow_item['FORUM_DESC'] : ''; ?></td>
		<td><?php echo isset($forumrow_item['NUM_TOPICS']) ? $forumrow_item['NUM_TOPICS'] : ''; ?></td>
		<td><?php echo isset($forumrow_item['NUM_POSTS']) ? $forumrow_item['NUM_POSTS'] : ''; ?></td>
		<td style="text-align:right">&nbsp;<a href="<?php echo isset($forumrow_item['U_FORUM_MOVE_UP']) ? $forumrow_item['U_FORUM_MOVE_UP'] : ''; ?>" title="<?php echo isset($this->vars['L_MOVE_UP']) ? $this->vars['L_MOVE_UP'] : $this->lang('L_MOVE_UP'); ?>"><img src="../templates/prosilver/admin/images/icon_up.gif" alt="" /></a>&nbsp;<a href="<?php echo isset($forumrow_item['U_FORUM_MOVE_DOWN']) ? $forumrow_item['U_FORUM_MOVE_DOWN'] : ''; ?>" title="<?php echo isset($this->vars['L_MOVE_DOWN']) ? $this->vars['L_MOVE_DOWN'] : $this->lang('L_MOVE_DOWN'); ?>"><img src="../templates/prosilver/admin/images/icon_down.gif" alt="" /></a>&nbsp;<a href="<?php echo isset($forumrow_item['U_FORUM_EDIT']) ? $forumrow_item['U_FORUM_EDIT'] : ''; ?>" title="<?php echo isset($this->vars['L_EDIT']) ? $this->vars['L_EDIT'] : $this->lang('L_EDIT'); ?>"><img src="../templates/prosilver/admin/images/icon_edit.gif" alt="" /></a>&nbsp;<a href="<?php echo isset($forumrow_item['U_FORUM_RESYNC']) ? $forumrow_item['U_FORUM_RESYNC'] : ''; ?>" title="<?php echo isset($this->vars['L_RESYNC']) ? $this->vars['L_RESYNC'] : $this->lang('L_RESYNC'); ?>"><img src="../templates/prosilver/admin/images/icon_sync.gif" alt="" /></a>&nbsp;<a href="<?php echo isset($forumrow_item['U_FORUM_DELETE']) ? $forumrow_item['U_FORUM_DELETE'] : ''; ?>" title="<?php echo isset($this->vars['L_DELETE']) ? $this->vars['L_DELETE'] : $this->lang('L_DELETE'); ?>"><img src="../templates/prosilver/admin/images/icon_delete.gif" alt="" /></a>&nbsp;</td>
	</tr>
	<?php

} // END forumrow

if(isset($forumrow_item)) { unset($forumrow_item); } 

?>
	<tr>
		<td colspan="3"><input type="text" name="<?php echo isset($catrow_item['S_ADD_FORUM_NAME']) ? $catrow_item['S_ADD_FORUM_NAME'] : ''; ?>" /> <input type="submit" class="button2" name="<?php echo isset($catrow_item['S_ADD_FORUM_SUBMIT']) ? $catrow_item['S_ADD_FORUM_SUBMIT'] : ''; ?>" value="<?php echo isset($this->vars['L_CREATE_FORUM']) ? $this->vars['L_CREATE_FORUM'] : $this->lang('L_CREATE_FORUM'); ?>" /></td>
		<td></td>
	</tr>
	<?php

} // END catrow

if(isset($catrow_item)) { unset($catrow_item); } 

?>
	<tr>
		<td colspan="3"><input type="text" name="categoryname" /> <input type="submit" class="button2" name="addcategory" value="<?php echo isset($this->vars['L_CREATE_CATEGORY']) ? $this->vars['L_CREATE_CATEGORY'] : $this->lang('L_CREATE_CATEGORY'); ?>" /></td>
		<td></td>
	</tr>
	</tbody>
</table>
</form>