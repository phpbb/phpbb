<?php

// eXtreme Styles mod cache. Generated on Fri, 18 May 2018 18:18:08 +0000 (time=1526667488)

?><a href="<?php echo isset($this->vars['U_FORUM_INDEX']) ? $this->vars['U_FORUM_INDEX'] : $this->lang('U_FORUM_INDEX'); ?>" target="_parent"><img src="../templates/prosilver/admin/images/phpbb_logo.gif" alt="" /></a>
<div id="menu">
<ul>
	<li class="header"><?php echo isset($this->vars['L_ADMIN']) ? $this->vars['L_ADMIN'] : $this->lang('L_ADMIN'); ?></li>
	<li><a href="<?php echo isset($this->vars['U_ADMIN_INDEX']) ? $this->vars['U_ADMIN_INDEX'] : $this->lang('U_ADMIN_INDEX'); ?>" target="main"><span><?php echo isset($this->vars['L_ADMIN_INDEX']) ? $this->vars['L_ADMIN_INDEX'] : $this->lang('L_ADMIN_INDEX'); ?></span></a></li>
	<li><a href="<?php echo isset($this->vars['U_FORUM_INDEX']) ? $this->vars['U_FORUM_INDEX'] : $this->lang('U_FORUM_INDEX'); ?>" target="_parent"><span><?php echo isset($this->vars['L_FORUM_INDEX']) ? $this->vars['L_FORUM_INDEX'] : $this->lang('L_FORUM_INDEX'); ?></span></a></li>
	<li><a href="<?php echo isset($this->vars['U_FORUM_INDEX']) ? $this->vars['U_FORUM_INDEX'] : $this->lang('U_FORUM_INDEX'); ?>" target="main"><span><?php echo isset($this->vars['L_PREVIEW_FORUM']) ? $this->vars['L_PREVIEW_FORUM'] : $this->lang('L_PREVIEW_FORUM'); ?></span></a></li>
	<?php

$catrow_count = ( isset($this->_tpldata['catrow.']) ) ?  sizeof($this->_tpldata['catrow.']) : 0;
for ($catrow_i = 0; $catrow_i < $catrow_count; $catrow_i++)
{
 $catrow_item = &$this->_tpldata['catrow.'][$catrow_i];
 $catrow_item['S_ROW_COUNT'] = $catrow_i;
 $catrow_item['S_NUM_ROWS'] = $catrow_count;

?>
	<li class="header"><?php echo isset($catrow_item['ADMIN_CATEGORY']) ? $catrow_item['ADMIN_CATEGORY'] : ''; ?></li>
	<?php

$modulerow_count = ( isset($catrow_item['modulerow.']) ) ? sizeof($catrow_item['modulerow.']) : 0;
for ($modulerow_i = 0; $modulerow_i < $modulerow_count; $modulerow_i++)
{
 $modulerow_item = &$catrow_item['modulerow.'][$modulerow_i];
 $modulerow_item['S_ROW_COUNT'] = $modulerow_i;
 $modulerow_item['S_NUM_ROWS'] = $modulerow_count;

?>
	<li><a href="<?php echo isset($modulerow_item['U_ADMIN_MODULE']) ? $modulerow_item['U_ADMIN_MODULE'] : ''; ?>" target="main"><span><?php echo isset($modulerow_item['ADMIN_MODULE']) ? $modulerow_item['ADMIN_MODULE'] : ''; ?></span></a></li>
	<?php

} // END modulerow

if(isset($modulerow_item)) { unset($modulerow_item); } 

?>
	<?php

} // END catrow

if(isset($catrow_item)) { unset($catrow_item); } 

?>
</ul>
</div>