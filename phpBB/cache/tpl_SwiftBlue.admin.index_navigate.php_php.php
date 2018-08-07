<?php

// eXtreme Styles mod cache. Generated on Fri, 27 Jul 2018 16:46:29 +0000 (time=1532709989)

?> 
<table width="100--" cellpadding="4" cellspacing="0" border="0" align="center">
  <tr> 
	<td align="center" ><a href="<?php echo isset($this->vars['U_FORUM_INDEX']) ? $this->vars['U_FORUM_INDEX'] : $this->lang('U_FORUM_INDEX'); ?>" target="_parent"><img src="../templates/subSilver/images/logo_phpBB_med.gif" border="0" /></a></td>
  </tr>
  <tr> 
	<td align="center" > 
	  <table width="100--" cellpadding="4" cellspacing="1" border="0" class="forumline">
		<tr> 
		  <th height="25" class="thHead"><b><?php echo isset($this->vars['L_ADMIN']) ? $this->vars['L_ADMIN'] : $this->lang('L_ADMIN'); ?></b></th>
		</tr>
		<tr> 
		  <td class="row1"><span class="genmed"><a href="<?php echo isset($this->vars['U_ADMIN_INDEX']) ? $this->vars['U_ADMIN_INDEX'] : $this->lang('U_ADMIN_INDEX'); ?>" target="main" class="genmed"><?php echo isset($this->vars['L_ADMIN_INDEX']) ? $this->vars['L_ADMIN_INDEX'] : $this->lang('L_ADMIN_INDEX'); ?></a></span></td>
		</tr>
		<tr> 
		  <td class="row1"><span class="genmed"><a href="<?php echo isset($this->vars['U_FORUM_INDEX']) ? $this->vars['U_FORUM_INDEX'] : $this->lang('U_FORUM_INDEX'); ?>" target="_parent" class="genmed"><?php echo isset($this->vars['L_FORUM_INDEX']) ? $this->vars['L_FORUM_INDEX'] : $this->lang('L_FORUM_INDEX'); ?></a></span></td>
		</tr>
		<tr> 
		  <td class="row1"><span class="genmed"><a href="<?php echo isset($this->vars['U_FORUM_INDEX']) ? $this->vars['U_FORUM_INDEX'] : $this->lang('U_FORUM_INDEX'); ?>" target="main" class="genmed"><?php echo isset($this->vars['L_PREVIEW_FORUM']) ? $this->vars['L_PREVIEW_FORUM'] : $this->lang('L_PREVIEW_FORUM'); ?></a></span></td>
		</tr>
		<?php

$catrow_count = ( isset($this->_tpldata['catrow.']) ) ?  sizeof($this->_tpldata['catrow.']) : 0;
for ($catrow_i = 0; $catrow_i < $catrow_count; $catrow_i++)
{
 $catrow_item = &$this->_tpldata['catrow.'][$catrow_i];
 $catrow_item['S_ROW_COUNT'] = $catrow_i;
 $catrow_item['S_NUM_ROWS'] = $catrow_count;

?>
		<tr> 
		  <td height="28" class="catSides"><span class="cattitle"><?php echo isset($catrow_item['ADMIN_CATEGORY']) ? $catrow_item['ADMIN_CATEGORY'] : ''; ?></span></td>
		</tr>
		<?php

$modulerow_count = ( isset($catrow_item['modulerow.']) ) ? sizeof($catrow_item['modulerow.']) : 0;
for ($modulerow_i = 0; $modulerow_i < $modulerow_count; $modulerow_i++)
{
 $modulerow_item = &$catrow_item['modulerow.'][$modulerow_i];
 $modulerow_item['S_ROW_COUNT'] = $modulerow_i;
 $modulerow_item['S_NUM_ROWS'] = $modulerow_count;

?>
		<tr> 
		  <td class="row1"><span class="genmed"><a href="<?php echo isset($modulerow_item['U_ADMIN_MODULE']) ? $modulerow_item['U_ADMIN_MODULE'] : ''; ?>"  target="main" class="genmed"><?php echo isset($modulerow_item['ADMIN_MODULE']) ? $modulerow_item['ADMIN_MODULE'] : ''; ?></a></span> 
		  </td>
		</tr>
		<?php

} // END modulerow

if(isset($modulerow_item)) { unset($modulerow_item); } 

?>
		<?php

} // END catrow

if(isset($catrow_item)) { unset($catrow_item); } 

?>
	  </table>
	</td>
  </tr>
</table>

<br />
