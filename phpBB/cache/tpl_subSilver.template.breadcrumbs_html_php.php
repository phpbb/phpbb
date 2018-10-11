<?php

// eXtreme Styles mod cache. Generated on Tue, 09 Oct 2018 07:31:16 +0000 (time=1539070276)

?>	<table class="tablebg table1 main_table" width="100%" cellspacing="0" cellpadding="1" border="0">
	<tbody>
	<tr class="bg1">
		<td class="row1">
			<div class="breadcrumbs">
			<a href="<?php echo isset($this->vars['U_INDEX']) ? $this->vars['U_INDEX'] : $this->lang('U_INDEX'); ?>"><?php echo isset($this->vars['L_INDEX']) ? $this->vars['L_INDEX'] : $this->lang('L_INDEX'); ?></a>
			<?php

$navlinks_count = ( isset($this->_tpldata['navlinks.']) ) ?  sizeof($this->_tpldata['navlinks.']) : 0;
for ($navlinks_i = 0; $navlinks_i < $navlinks_count; $navlinks_i++)
{
 $navlinks_item = &$this->_tpldata['navlinks.'][$navlinks_i];
 $navlinks_item['S_ROW_COUNT'] = $navlinks_i;
 $navlinks_item['S_NUM_ROWS'] = $navlinks_count;

?> 
			&#187; 
			<a href="<?php echo isset($navlinks_item['U_VIEW_FORUM']) ? $navlinks_item['U_VIEW_FORUM'] : ''; ?>">
			<?php echo isset($navlinks_item['FORUM_NAME']) ? $navlinks_item['FORUM_NAME'] : ''; ?>
			</a>
			<?php

} // END navlinks

if(isset($navlinks_item)) { unset($navlinks_item); } 

?>
			</div>
			<p class="datetime"><?php echo isset($this->vars['S_TIMEZONE']) ? $this->vars['S_TIMEZONE'] : $this->lang('S_TIMEZONE'); ?></p>
		</td>
	</tr>
	</tbody>
	</table>