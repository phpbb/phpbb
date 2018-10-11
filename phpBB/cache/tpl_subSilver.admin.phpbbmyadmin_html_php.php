<?php

// eXtreme Styles mod cache. Generated on Mon, 08 Oct 2018 23:52:03 +0000 (time=1539042723)

?><h1 align="center"><?php echo isset($this->vars['HEADER']) ? $this->vars['HEADER'] : $this->lang('HEADER'); ?></h1>

<p align="center" class="genmed"><?php echo isset($this->vars['CURRENT_TIME']) ? $this->vars['CURRENT_TIME'] : $this->lang('CURRENT_TIME'); ?></p>

<form action="<?php echo isset($this->vars['SQL_ACTION']) ? $this->vars['SQL_ACTION'] : $this->lang('SQL_ACTION'); ?>" method="post">

<table cellspacing="0" cellpadding="0" border="0" align="center" width="100">
	<tr>
		<td align="center"><span class="gen"><b><?php echo isset($this->vars['QUERY_TITLE']) ? $this->vars['QUERY_TITLE'] : $this->lang('QUERY_TITLE'); ?></b></span></td>
	</tr>
	<tr>
		<td><textarea name="this_query" rows="10" cols="100" class="post"></textarea></td>
	</tr>
	<tr>
		<td align="center">
			<input type="submit" name="repairall" value="<?php echo isset($this->vars['REPAIR_ALL_BUTTON']) ? $this->vars['REPAIR_ALL_BUTTON'] : $this->lang('REPAIR_ALL_BUTTON'); ?>" class="liteoption">&nbsp;<input type="submit" name="submit" value="<?php echo isset($this->vars['SUBMIT_BUTTON']) ? $this->vars['SUBMIT_BUTTON'] : $this->lang('SUBMIT_BUTTON'); ?>" class="liteoption">&nbsp;<input type="submit" name="optimizeall" value="<?php echo isset($this->vars['OPTIMIZE_ALL_BUTTON']) ? $this->vars['OPTIMIZE_ALL_BUTTON'] : $this->lang('OPTIMIZE_ALL_BUTTON'); ?>" class="liteoption">
		</td>
	</tr>
	<tr>
		<td align="center">
			<br />
			<?php echo isset($this->vars['L_WITH_SELECTED_WORD']) ? $this->vars['L_WITH_SELECTED_WORD'] : $this->lang('L_WITH_SELECTED_WORD'); ?>
			<select name="with_selected">
				<option value=optimize><?php echo isset($this->vars['L_WITH_SELECTED_OPTIMIZE']) ? $this->vars['L_WITH_SELECTED_OPTIMIZE'] : $this->lang('L_WITH_SELECTED_OPTIMIZE'); ?>
				<option value=repair><?php echo isset($this->vars['L_WITH_SELECTED_REPAIR']) ? $this->vars['L_WITH_SELECTED_REPAIR'] : $this->lang('L_WITH_SELECTED_REPAIR'); ?>
				<option value=empty><?php echo isset($this->vars['L_WITH_SELECTED_EMPTY']) ? $this->vars['L_WITH_SELECTED_EMPTY'] : $this->lang('L_WITH_SELECTED_EMPTY'); ?>
				<option value=drop><?php echo isset($this->vars['L_WITH_SELECTED_DROP']) ? $this->vars['L_WITH_SELECTED_DROP'] : $this->lang('L_WITH_SELECTED_DROP'); ?>
			</select>
			<input type="submit" name="go_with_selected" value="<?php echo isset($this->vars['SUBMIT_BUTTON']) ? $this->vars['SUBMIT_BUTTON'] : $this->lang('SUBMIT_BUTTON'); ?>" class="liteoption">
		</td>
	</tr>
</table>

<br />

<?php

$switch_submit_result_count = ( isset($this->_tpldata['switch_submit_result.']) ) ?  sizeof($this->_tpldata['switch_submit_result.']) : 0;
for ($switch_submit_result_i = 0; $switch_submit_result_i < $switch_submit_result_count; $switch_submit_result_i++)
{
 $switch_submit_result_item = &$this->_tpldata['switch_submit_result.'][$switch_submit_result_i];
 $switch_submit_result_item['S_ROW_COUNT'] = $switch_submit_result_i;
 $switch_submit_result_item['S_NUM_ROWS'] = $switch_submit_result_count;

?>
<table width="100%" cellspacing="0" cellpadding="0" border="1" class="forumline">
	<tr>
		<td colspan="<?php echo isset($this->vars['SUBMIT_RESULT_FIELD_COUNT']) ? $this->vars['SUBMIT_RESULT_FIELD_COUNT'] : $this->lang('SUBMIT_RESULT_FIELD_COUNT'); ?>" align="left" class="catLeft">
			<span class="gen"><?php echo isset($this->vars['SUBMIT_RESULT_QUERY']) ? $this->vars['SUBMIT_RESULT_QUERY'] : $this->lang('SUBMIT_RESULT_QUERY'); ?></span>
		</td>
	</tr>
	<tr>
		<?php

$submit_result_fields_count = ( isset($switch_submit_result_item['submit_result_fields.']) ) ? sizeof($switch_submit_result_item['submit_result_fields.']) : 0;
for ($submit_result_fields_i = 0; $submit_result_fields_i < $submit_result_fields_count; $submit_result_fields_i++)
{
 $submit_result_fields_item = &$switch_submit_result_item['submit_result_fields.'][$submit_result_fields_i];
 $submit_result_fields_item['S_ROW_COUNT'] = $submit_result_fields_i;
 $submit_result_fields_item['S_NUM_ROWS'] = $submit_result_fields_count;

?>
		<td align="center" class="row1"><span class="genmed"><?php echo isset($submit_result_fields_item['SUBMIT_RESULT_FIELD_NAME']) ? $submit_result_fields_item['SUBMIT_RESULT_FIELD_NAME'] : ''; ?></span></td>
		<?php

} // END submit_result_fields

if(isset($submit_result_fields_item)) { unset($submit_result_fields_item); } 

?>
	</tr>
	<?php

$submit_result_data_count = ( isset($switch_submit_result_item['submit_result_data.']) ) ? sizeof($switch_submit_result_item['submit_result_data.']) : 0;
for ($submit_result_data_i = 0; $submit_result_data_i < $submit_result_data_count; $submit_result_data_i++)
{
 $submit_result_data_item = &$switch_submit_result_item['submit_result_data.'][$submit_result_data_i];
 $submit_result_data_item['S_ROW_COUNT'] = $submit_result_data_i;
 $submit_result_data_item['S_NUM_ROWS'] = $submit_result_data_count;

?>
	<tr>
		<?php

$submit_result_data_row_count = ( isset($submit_result_data_item['submit_result_data_row.']) ) ? sizeof($submit_result_data_item['submit_result_data_row.']) : 0;
for ($submit_result_data_row_i = 0; $submit_result_data_row_i < $submit_result_data_row_count; $submit_result_data_row_i++)
{
 $submit_result_data_row_item = &$submit_result_data_item['submit_result_data_row.'][$submit_result_data_row_i];
 $submit_result_data_row_item['S_ROW_COUNT'] = $submit_result_data_row_i;
 $submit_result_data_row_item['S_NUM_ROWS'] = $submit_result_data_row_count;

?>
		<td align="center" class="row1"><span class="genmed"><?php echo isset($submit_result_data_row_item['SUBMIT_RESULT_DATA']) ? $submit_result_data_row_item['SUBMIT_RESULT_DATA'] : ''; ?></span></td>
		<?php

} // END submit_result_data_row

if(isset($submit_result_data_row_item)) { unset($submit_result_data_row_item); } 

?>
	</tr>
	<?php

} // END submit_result_data

if(isset($submit_result_data_item)) { unset($submit_result_data_item); } 

?>
</table>
<br />
<?php

} // END switch_submit_result

if(isset($switch_submit_result_item)) { unset($switch_submit_result_item); } 

?>

<?php

$switch_table_browse_count = ( isset($this->_tpldata['switch_table_browse.']) ) ?  sizeof($this->_tpldata['switch_table_browse.']) : 0;
for ($switch_table_browse_i = 0; $switch_table_browse_i < $switch_table_browse_count; $switch_table_browse_i++)
{
 $switch_table_browse_item = &$this->_tpldata['switch_table_browse.'][$switch_table_browse_i];
 $switch_table_browse_item['S_ROW_COUNT'] = $switch_table_browse_i;
 $switch_table_browse_item['S_NUM_ROWS'] = $switch_table_browse_count;

?>
<table width="100%" cellspacing="0" cellpadding="0" border="1" class="forumline">
	<?php

$table_browse_menu_count = ( isset($switch_table_browse_item['table_browse_menu.']) ) ? sizeof($switch_table_browse_item['table_browse_menu.']) : 0;
for ($table_browse_menu_i = 0; $table_browse_menu_i < $table_browse_menu_count; $table_browse_menu_i++)
{
 $table_browse_menu_item = &$switch_table_browse_item['table_browse_menu.'][$table_browse_menu_i];
 $table_browse_menu_item['S_ROW_COUNT'] = $table_browse_menu_i;
 $table_browse_menu_item['S_NUM_ROWS'] = $table_browse_menu_count;

?>
	<tr>
		<td colspan="<?php echo isset($table_browse_menu_item['BROWSE_MENU_COLSPAN']) ? $table_browse_menu_item['BROWSE_MENU_COLSPAN'] : ''; ?>" align="left" class="catLeft">
			<span class="gen"><a href="<?php echo isset($table_browse_menu_item['FIRST_PAGE']) ? $table_browse_menu_item['FIRST_PAGE'] : ''; ?>"><?php echo isset($table_browse_menu_item['L_FIRST_PAGE']) ? $table_browse_menu_item['L_FIRST_PAGE'] : ''; ?></a>&nbsp;</span>
			<span class="gen"><a href="<?php echo isset($table_browse_menu_item['NEXT_PAGE']) ? $table_browse_menu_item['NEXT_PAGE'] : ''; ?>"><?php echo isset($table_browse_menu_item['L_NEXT_PAGE']) ? $table_browse_menu_item['L_NEXT_PAGE'] : ''; ?></a>&nbsp;</span>
			<span class="gen"><a href="<?php echo isset($table_browse_menu_item['PREVIOUS_PAGE']) ? $table_browse_menu_item['PREVIOUS_PAGE'] : ''; ?>"><?php echo isset($table_browse_menu_item['L_PREVIOUS_PAGE']) ? $table_browse_menu_item['L_PREVIOUS_PAGE'] : ''; ?></a>&nbsp;</span>
			<span class="gen"><a href="<?php echo isset($table_browse_menu_item['SORT_ASC']) ? $table_browse_menu_item['SORT_ASC'] : ''; ?>"><?php echo isset($table_browse_menu_item['L_SORT_ASC']) ? $table_browse_menu_item['L_SORT_ASC'] : ''; ?></a>&nbsp;</span>
			<span class="gen"><a href="<?php echo isset($table_browse_menu_item['SORT_DESC']) ? $table_browse_menu_item['SORT_DESC'] : ''; ?>"><?php echo isset($table_browse_menu_item['L_SORT_DESC']) ? $table_browse_menu_item['L_SORT_DESC'] : ''; ?></a>&nbsp;</span>
		</td>
	</tr>
	<?php

} // END table_browse_menu

if(isset($table_browse_menu_item)) { unset($table_browse_menu_item); } 

?>
	<tr>
		<td class="row1">&nbsp;</td>
		<?php

$table_browse_fields_count = ( isset($switch_table_browse_item['table_browse_fields.']) ) ? sizeof($switch_table_browse_item['table_browse_fields.']) : 0;
for ($table_browse_fields_i = 0; $table_browse_fields_i < $table_browse_fields_count; $table_browse_fields_i++)
{
 $table_browse_fields_item = &$switch_table_browse_item['table_browse_fields.'][$table_browse_fields_i];
 $table_browse_fields_item['S_ROW_COUNT'] = $table_browse_fields_i;
 $table_browse_fields_item['S_NUM_ROWS'] = $table_browse_fields_count;

?>
		<td class="row1"><span class="genmed"><b><a href="<?php echo isset($table_browse_fields_item['TABLE_BROWSE_FIELD_ORDER']) ? $table_browse_fields_item['TABLE_BROWSE_FIELD_ORDER'] : ''; ?>"><?php echo isset($table_browse_fields_item['TABLE_BROWSE_FIELD_NAME']) ? $table_browse_fields_item['TABLE_BROWSE_FIELD_NAME'] : ''; ?></a></b>&nbsp;</span></td>
		<?php

} // END table_browse_fields

if(isset($table_browse_fields_item)) { unset($table_browse_fields_item); } 

?>
	</tr>
	<?php

$table_browse_data_count = ( isset($switch_table_browse_item['table_browse_data.']) ) ? sizeof($switch_table_browse_item['table_browse_data.']) : 0;
for ($table_browse_data_i = 0; $table_browse_data_i < $table_browse_data_count; $table_browse_data_i++)
{
 $table_browse_data_item = &$switch_table_browse_item['table_browse_data.'][$table_browse_data_i];
 $table_browse_data_item['S_ROW_COUNT'] = $table_browse_data_i;
 $table_browse_data_item['S_NUM_ROWS'] = $table_browse_data_count;

?>
	<tr>
		<td class="row1"><span class="gensmall"><a href="<?php echo isset($table_browse_data_item['TABLE_BROWSE_DELETE']) ? $table_browse_data_item['TABLE_BROWSE_DELETE'] : ''; ?>"><?php echo isset($this->vars['L_TABLE_BROWSE_DELETE']) ? $this->vars['L_TABLE_BROWSE_DELETE'] : $this->lang('L_TABLE_BROWSE_DELETE'); ?></a>&nbsp;</span></td>
		<?php

$table_browse_data_field_count = ( isset($table_browse_data_item['table_browse_data_field.']) ) ? sizeof($table_browse_data_item['table_browse_data_field.']) : 0;
for ($table_browse_data_field_i = 0; $table_browse_data_field_i < $table_browse_data_field_count; $table_browse_data_field_i++)
{
 $table_browse_data_field_item = &$table_browse_data_item['table_browse_data_field.'][$table_browse_data_field_i];
 $table_browse_data_field_item['S_ROW_COUNT'] = $table_browse_data_field_i;
 $table_browse_data_field_item['S_NUM_ROWS'] = $table_browse_data_field_count;

?>
		<td class="row1"><span class="gensmall"><?php echo isset($table_browse_data_field_item['TABLE_BROWSE_DATA']) ? $table_browse_data_field_item['TABLE_BROWSE_DATA'] : ''; ?>&nbsp;</span></td>
		<?php

} // END table_browse_data_field

if(isset($table_browse_data_field_item)) { unset($table_browse_data_field_item); } 

?>
	</tr>
	<?php

} // END table_browse_data

if(isset($table_browse_data_item)) { unset($table_browse_data_item); } 

?>
</table>
<br />
<?php

} // END switch_table_browse

if(isset($switch_table_browse_item)) { unset($switch_table_browse_item); } 

?>

<?php

$switch_table_structure_count = ( isset($this->_tpldata['switch_table_structure.']) ) ?  sizeof($this->_tpldata['switch_table_structure.']) : 0;
for ($switch_table_structure_i = 0; $switch_table_structure_i < $switch_table_structure_count; $switch_table_structure_i++)
{
 $switch_table_structure_item = &$this->_tpldata['switch_table_structure.'][$switch_table_structure_i];
 $switch_table_structure_item['S_ROW_COUNT'] = $switch_table_structure_i;
 $switch_table_structure_item['S_NUM_ROWS'] = $switch_table_structure_count;

?>
<table width="100%" cellspacing="0" cellpadding="0" border="1" class="forumline">
	<tr>
		<td colspan="7" align="center" class="catLeft"><span class="gen"><b><?php echo isset($this->vars['L_TABLE_STRUCTURE_TABLENAME']) ? $this->vars['L_TABLE_STRUCTURE_TABLENAME'] : $this->lang('L_TABLE_STRUCTURE_TABLENAME'); ?></b></span></td>
	</tr>
	<tr>
		<td class="row1"><span class="genmed"><b>&nbsp;</b></span></td>
		<td class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_STRUCTURE_FIELD']) ? $this->vars['L_TABLE_STRUCTURE_FIELD'] : $this->lang('L_TABLE_STRUCTURE_FIELD'); ?></b>&nbsp;</span></td>
		<td class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_STRUCTURE_TYPE']) ? $this->vars['L_TABLE_STRUCTURE_TYPE'] : $this->lang('L_TABLE_STRUCTURE_TYPE'); ?></b>&nbsp;</span></td>
		<td class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_STRUCTURE_NULL']) ? $this->vars['L_TABLE_STRUCTURE_NULL'] : $this->lang('L_TABLE_STRUCTURE_NULL'); ?></b>&nbsp;</span></td>
		<td class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_STRUCTURE_KEY']) ? $this->vars['L_TABLE_STRUCTURE_KEY'] : $this->lang('L_TABLE_STRUCTURE_KEY'); ?></b>&nbsp;</span></td>
		<td class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_STRUCTURE_DEFAULT']) ? $this->vars['L_TABLE_STRUCTURE_DEFAULT'] : $this->lang('L_TABLE_STRUCTURE_DEFAULT'); ?></b>&nbsp;</span></td>
		<td class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_STRUCTURE_EXTRA']) ? $this->vars['L_TABLE_STRUCTURE_EXTRA'] : $this->lang('L_TABLE_STRUCTURE_EXTRA'); ?></b>&nbsp;</span></td>
	</tr>
	<?php

$actual_table_structure_count = ( isset($switch_table_structure_item['actual_table_structure.']) ) ? sizeof($switch_table_structure_item['actual_table_structure.']) : 0;
for ($actual_table_structure_i = 0; $actual_table_structure_i < $actual_table_structure_count; $actual_table_structure_i++)
{
 $actual_table_structure_item = &$switch_table_structure_item['actual_table_structure.'][$actual_table_structure_i];
 $actual_table_structure_item['S_ROW_COUNT'] = $actual_table_structure_i;
 $actual_table_structure_item['S_NUM_ROWS'] = $actual_table_structure_count;

?>
	<tr>
		<td class="row1"><span class="gensmall"><a href="<?php echo isset($actual_table_structure_item['TABLE_STRUCTURE_DROP']) ? $actual_table_structure_item['TABLE_STRUCTURE_DROP'] : ''; ?>"><?php echo isset($this->vars['L_TABLE_STRUCTURE_DROP']) ? $this->vars['L_TABLE_STRUCTURE_DROP'] : $this->lang('L_TABLE_STRUCTURE_DROP'); ?></a>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><?php echo isset($actual_table_structure_item['TABLE_STRUCTURE_FIELD']) ? $actual_table_structure_item['TABLE_STRUCTURE_FIELD'] : ''; ?>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><?php echo isset($actual_table_structure_item['TABLE_STRUCTURE_TYPE']) ? $actual_table_structure_item['TABLE_STRUCTURE_TYPE'] : ''; ?>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><?php echo isset($actual_table_structure_item['TABLE_STRUCTURE_NULL']) ? $actual_table_structure_item['TABLE_STRUCTURE_NULL'] : ''; ?>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><?php echo isset($actual_table_structure_item['TABLE_STRUCTURE_KEY']) ? $actual_table_structure_item['TABLE_STRUCTURE_KEY'] : ''; ?>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><?php echo isset($actual_table_structure_item['TABLE_STRUCTURE_DEFAULT']) ? $actual_table_structure_item['TABLE_STRUCTURE_DEFAULT'] : ''; ?>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><?php echo isset($actual_table_structure_item['TABLE_STRUCTURE_EXTRA']) ? $actual_table_structure_item['TABLE_STRUCTURE_EXTRA'] : ''; ?>&nbsp;</span></td>
	</tr>
	<?php

} // END actual_table_structure

if(isset($actual_table_structure_item)) { unset($actual_table_structure_item); } 

?>
</table>
<br />
<?php

} // END switch_table_structure

if(isset($switch_table_structure_item)) { unset($switch_table_structure_item); } 

?>

<table width="100%" cellspacing="0" cellpadding="0" border="1" class="forumline">
	<tr>
		<td colspan="12" align="center" class="catLeft"><span class="gen"><b><?php echo isset($this->vars['TABLE_TITLE']) ? $this->vars['TABLE_TITLE'] : $this->lang('TABLE_TITLE'); ?></b></span></td>
	</tr>
	<tr>
		<td class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_NAME']) ? $this->vars['L_TABLE_NAME'] : $this->lang('L_TABLE_NAME'); ?></b>&nbsp;</span></td>
		<td colspan="6" align="center" class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_ACTIONS']) ? $this->vars['L_TABLE_ACTIONS'] : $this->lang('L_TABLE_ACTIONS'); ?></b>&nbsp;</span></td>
		<td class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_TYPE']) ? $this->vars['L_TABLE_TYPE'] : $this->lang('L_TABLE_TYPE'); ?></b>&nbsp;</span></td>
		<td class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_ROWS']) ? $this->vars['L_TABLE_ROWS'] : $this->lang('L_TABLE_ROWS'); ?></b>&nbsp;</span></td>
		<td class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_DATA_LENGTH']) ? $this->vars['L_TABLE_DATA_LENGTH'] : $this->lang('L_TABLE_DATA_LENGTH'); ?></b>&nbsp;</span></td>
		<td class="row1"><span class="genmed"><b><?php echo isset($this->vars['L_TABLE_OPTIMIZATION_LEVEL']) ? $this->vars['L_TABLE_OPTIMIZATION_LEVEL'] : $this->lang('L_TABLE_OPTIMIZATION_LEVEL'); ?></b>&nbsp;</span></td>
		<td class="row1"><span class="genmed">&nbsp;</span></td>
	</tr>
<?php

$table_list_count = ( isset($this->_tpldata['table_list.']) ) ?  sizeof($this->_tpldata['table_list.']) : 0;
for ($table_list_i = 0; $table_list_i < $table_list_count; $table_list_i++)
{
 $table_list_item = &$this->_tpldata['table_list.'][$table_list_i];
 $table_list_item['S_ROW_COUNT'] = $table_list_i;
 $table_list_item['S_NUM_ROWS'] = $table_list_count;

?>
	<tr>
		<td class="row1"><span class="gensmall"><?php echo isset($table_list_item['TABLE_NAME']) ? $table_list_item['TABLE_NAME'] : ''; ?>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><a href="<?php echo isset($table_list_item['TABLE_STRUCTURE']) ? $table_list_item['TABLE_STRUCTURE'] : ''; ?>"><?php echo isset($this->vars['L_TABLE_STRUCTURE']) ? $this->vars['L_TABLE_STRUCTURE'] : $this->lang('L_TABLE_STRUCTURE'); ?></a>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><a href="<?php echo isset($table_list_item['TABLE_BROWSE']) ? $table_list_item['TABLE_BROWSE'] : ''; ?>"><?php echo isset($this->vars['L_TABLE_BROWSE']) ? $this->vars['L_TABLE_BROWSE'] : $this->lang('L_TABLE_BROWSE'); ?></a>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><a href="<?php echo isset($table_list_item['TABLE_OPTIMIZE']) ? $table_list_item['TABLE_OPTIMIZE'] : ''; ?>"><?php echo isset($this->vars['L_TABLE_OPTIMIZE']) ? $this->vars['L_TABLE_OPTIMIZE'] : $this->lang('L_TABLE_OPTIMIZE'); ?></a>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><a href="<?php echo isset($table_list_item['TABLE_REPAIR']) ? $table_list_item['TABLE_REPAIR'] : ''; ?>"><?php echo isset($this->vars['L_TABLE_REPAIR']) ? $this->vars['L_TABLE_REPAIR'] : $this->lang('L_TABLE_REPAIR'); ?></a>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><a href="<?php echo isset($table_list_item['TABLE_EMPTY']) ? $table_list_item['TABLE_EMPTY'] : ''; ?>"><?php echo isset($this->vars['L_TABLE_EMPTY']) ? $this->vars['L_TABLE_EMPTY'] : $this->lang('L_TABLE_EMPTY'); ?></a>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><a href="<?php echo isset($table_list_item['TABLE_DROP']) ? $table_list_item['TABLE_DROP'] : ''; ?>"><?php echo isset($this->vars['L_TABLE_DROP']) ? $this->vars['L_TABLE_DROP'] : $this->lang('L_TABLE_DROP'); ?></a>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><?php echo isset($table_list_item['TABLE_TYPE']) ? $table_list_item['TABLE_TYPE'] : ''; ?>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><?php echo isset($table_list_item['TABLE_ROWS']) ? $table_list_item['TABLE_ROWS'] : ''; ?>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><?php echo isset($table_list_item['TABLE_DATA_LENGTH']) ? $table_list_item['TABLE_DATA_LENGTH'] : ''; ?>&nbsp;</span></td>
		<td class="row1"><span class="gensmall"><?php echo isset($table_list_item['TABLE_OPTIMIZATION_LEVEL']) ? $table_list_item['TABLE_OPTIMIZATION_LEVEL'] : ''; ?>%&nbsp;</span></td>
		<td class="row1"><input type="checkbox" name="with_selected_table_list[]" value="<?php echo isset($table_list_item['TABLE_NAME']) ? $table_list_item['TABLE_NAME'] : ''; ?>"></td>
	</tr>
<?php

} // END table_list

if(isset($table_list_item)) { unset($table_list_item); } 

?>
</table>

</form>

<table width="100%" cellspacing="0" border="0" cellpadding="0">
	<tr>
		<td align="center"><span class="copyright"><?php echo isset($this->vars['COPYRIGHT']) ? $this->vars['COPYRIGHT'] : $this->lang('COPYRIGHT'); ?></span></td>
	</tr>
</table>