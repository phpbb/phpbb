<?php

// eXtreme Styles mod cache. Generated on Sat, 26 May 2018 20:57:45 +0000 (time=1527368265)

?><h1><?php echo isset($this->vars['L_GROUP_TITLE']) ? $this->vars['L_GROUP_TITLE'] : $this->lang('L_GROUP_TITLE'); ?></h1>
<p><?php echo isset($this->vars['L_GROUP_EXPLAIN']) ? $this->vars['L_GROUP_EXPLAIN'] : $this->lang('L_GROUP_EXPLAIN'); ?></p>
<form method="post" action="<?php echo isset($this->vars['S_GROUP_ACTION']) ? $this->vars['S_GROUP_ACTION'] : $this->lang('S_GROUP_ACTION'); ?>">
<fieldset>
	<legend><?php echo isset($this->vars['L_GROUP_SELECT']) ? $this->vars['L_GROUP_SELECT'] : $this->lang('L_GROUP_SELECT'); ?></legend>
	<?php

$select_box_count = ( isset($this->_tpldata['select_box.']) ) ?  sizeof($this->_tpldata['select_box.']) : 0;
for ($select_box_i = 0; $select_box_i < $select_box_count; $select_box_i++)
{
 $select_box_item = &$this->_tpldata['select_box.'][$select_box_i];
 $select_box_item['S_ROW_COUNT'] = $select_box_i;
 $select_box_item['S_NUM_ROWS'] = $select_box_count;

?>
	<p><?php echo isset($this->vars['S_GROUP_SELECT']) ? $this->vars['S_GROUP_SELECT'] : $this->lang('S_GROUP_SELECT'); ?>&nbsp; <input type="submit" name="edit" value="<?php echo isset($this->vars['L_LOOK_UP']) ? $this->vars['L_LOOK_UP'] : $this->lang('L_LOOK_UP'); ?>" class="button2" /></p>
	<?php

} // END select_box

if(isset($select_box_item)) { unset($select_box_item); } 

?>
	<p class="submit-buttons">
		<?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?>
		<input class="button1" type="submit" id="submit" name="new" value="<?php echo isset($this->vars['L_CREATE_NEW_GROUP']) ? $this->vars['L_CREATE_NEW_GROUP'] : $this->lang('L_CREATE_NEW_GROUP'); ?>" />
	</p>
</fieldset>
</form>