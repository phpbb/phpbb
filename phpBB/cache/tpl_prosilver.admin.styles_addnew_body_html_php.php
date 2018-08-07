<?php

// eXtreme Styles mod cache. Generated on Sun, 27 May 2018 21:59:21 +0000 (time=1527458361)

?><h1><?php echo isset($this->vars['L_STYLES_TITLE']) ? $this->vars['L_STYLES_TITLE'] : $this->lang('L_STYLES_TITLE'); ?></h1>
<p><?php echo isset($this->vars['L_STYLES_ADD_TEXT']) ? $this->vars['L_STYLES_ADD_TEXT'] : $this->lang('L_STYLES_ADD_TEXT'); ?></p>
<table cellspacing="1">
	<col class="row1" /><col class="row2" /><col class="row2" />
	<thead>
	<tr>
		<th><?php echo isset($this->vars['L_STYLE']) ? $this->vars['L_STYLE'] : $this->lang('L_STYLE'); ?></th>
		<th><?php echo isset($this->vars['L_TEMPLATE']) ? $this->vars['L_TEMPLATE'] : $this->lang('L_TEMPLATE'); ?></th>
		<th><?php echo isset($this->vars['L_ACTION']) ? $this->vars['L_ACTION'] : $this->lang('L_ACTION'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php

$styles_count = ( isset($this->_tpldata['styles.']) ) ?  sizeof($this->_tpldata['styles.']) : 0;
for ($styles_i = 0; $styles_i < $styles_count; $styles_i++)
{
 $styles_item = &$this->_tpldata['styles.'][$styles_i];
 $styles_item['S_ROW_COUNT'] = $styles_i;
 $styles_item['S_NUM_ROWS'] = $styles_count;

?>
	<tr>
		<td><strong><?php echo isset($styles_item['STYLE_NAME']) ? $styles_item['STYLE_NAME'] : ''; ?></strong></td>
		<td style="text-align: center;">
			<?php echo isset($styles_item['TEMPLATE_NAME']) ? $styles_item['TEMPLATE_NAME'] : ''; ?>
		</td>
		<td style="text-align: center;">
			<a href="<?php echo isset($styles_item['U_STYLES_INSTALL']) ? $styles_item['U_STYLES_INSTALL'] : ''; ?>"><?php echo isset($this->vars['L_INSTALL']) ? $this->vars['L_INSTALL'] : $this->lang('L_INSTALL'); ?></a>
		</td>
	</tr>
	<?php

} // END styles

if(isset($styles_item)) { unset($styles_item); } 

?>
	</tbody>
	</table>