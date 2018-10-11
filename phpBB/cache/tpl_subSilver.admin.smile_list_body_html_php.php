<?php

// eXtreme Styles mod cache. Generated on Mon, 08 Oct 2018 14:00:54 +0000 (time=1539007254)

?><h1><?php echo isset($this->vars['L_SMILEY_TITLE']) ? $this->vars['L_SMILEY_TITLE'] : $this->lang('L_SMILEY_TITLE'); ?></h1>
<p><?php echo isset($this->vars['L_SMILEY_TEXT']) ? $this->vars['L_SMILEY_TEXT'] : $this->lang('L_SMILEY_TEXT'); ?></p>

<form method="post" action="<?php echo isset($this->vars['S_SMILEY_ACTION']) ? $this->vars['S_SMILEY_ACTION'] : $this->lang('S_SMILEY_ACTION'); ?>">
<table class="forumline">
<tr>
	<td class="cat"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="add" value="<?php echo isset($this->vars['L_SMILEY_ADD']) ? $this->vars['L_SMILEY_ADD'] : $this->lang('L_SMILEY_ADD'); ?>" class="mainoption" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="import_pack" value="<?php echo isset($this->vars['L_IMPORT_PACK']) ? $this->vars['L_IMPORT_PACK'] : $this->lang('L_IMPORT_PACK'); ?>">&nbsp;&nbsp;<input class="liteoption" type="submit" name="export_pack" value="<?php echo isset($this->vars['L_EXPORT_PACK']) ? $this->vars['L_EXPORT_PACK'] : $this->lang('L_EXPORT_PACK'); ?>"></td>
</tr>
<tr>
	<td class="row1" style="padding: 0px;">
		<table class="nav-div" width="100%" align="center" style="padding: 0px;" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<th style="text-align: center; width: 30px;">&nbsp;</th>
			<th style="text-align: center; width: 160px;"><?php echo isset($this->vars['L_SMILE']) ? $this->vars['L_SMILE'] : $this->lang('L_SMILE'); ?></th>
			<th style="text-align: center; width: 170px;"><?php echo isset($this->vars['L_CODE']) ? $this->vars['L_CODE'] : $this->lang('L_CODE'); ?></th>
			<th style="text-align: center;"><?php echo isset($this->vars['L_EMOT']) ? $this->vars['L_EMOT'] : $this->lang('L_EMOT'); ?></th>
			<th style="text-align: center; width: 150px;"><?php echo isset($this->vars['L_ACTION']) ? $this->vars['L_ACTION'] : $this->lang('L_ACTION'); ?></th>
		</tr>
		</table>
		<ul id="smileys" style="margin: 0px; padding: 0px; list-style-type: none;">
		<?php

$smiles_count = ( isset($this->_tpldata['smiles.']) ) ?  sizeof($this->_tpldata['smiles.']) : 0;
for ($smiles_i = 0; $smiles_i < $smiles_count; $smiles_i++)
{
 $smiles_item = &$this->_tpldata['smiles.'][$smiles_i];
 $smiles_item['S_ROW_COUNT'] = $smiles_i;
 $smiles_item['S_NUM_ROWS'] = $smiles_count;

?>
		<li id="item_<?php echo isset($smiles_item['SMILEY_ID']) ? $smiles_item['SMILEY_ID'] : ''; ?>">
		<table>
		<tr class="<?php echo isset($smiles_item['ROW_CLASS']) ? $smiles_item['ROW_CLASS'] : ''; ?>h">
			<td class="<?php echo isset($smiles_item['ROW_CLASS']) ? $smiles_item['ROW_CLASS'] : ''; ?> row-center" style="padding: 0px; background: none; width: 40px;"><a class="icon-edit-move-empty" href="javascript:void(0);"><img src="../templates/all/images/cms_icon_move.png" alt="<?php echo isset($this->vars['L_MOVE']) ? $this->vars['L_MOVE'] : $this->lang('L_MOVE'); ?> " title="<?php echo isset($this->vars['L_MOVE']) ? $this->vars['L_MOVE'] : $this->lang('L_MOVE'); ?>" /></a></td>
			<td class="<?php echo isset($smiles_item['ROW_CLASS']) ? $smiles_item['ROW_CLASS'] : ''; ?> row-center" style="padding: 0px; background: none; width: 170px;"><img src="<?php echo isset($smiles_item['SMILEY_IMG']) ? $smiles_item['SMILEY_IMG'] : ''; ?>" alt="<?php echo isset($smiles_item['CODE']) ? $smiles_item['CODE'] : ''; ?>" /></td>
			<td class="<?php echo isset($smiles_item['ROW_CLASS']) ? $smiles_item['ROW_CLASS'] : ''; ?>" style="padding: 0px; background: none; width: 180px;"><?php echo isset($smiles_item['CODE']) ? $smiles_item['CODE'] : ''; ?></td>
			<td class="<?php echo isset($smiles_item['ROW_CLASS']) ? $smiles_item['ROW_CLASS'] : ''; ?>" style="padding: 0px; background: none;"><b><?php echo isset($smiles_item['EMOT']) ? $smiles_item['EMOT'] : ''; ?></b> - [<?php echo isset($smiles_item['SMILEY_URL']) ? $smiles_item['SMILEY_URL'] : ''; ?>]</td>
			<td class="<?php echo isset($smiles_item['ROW_CLASS']) ? $smiles_item['ROW_CLASS'] : ''; ?> row-center" style="padding: 0px; background: none; width: 160px;"><a href="<?php echo isset($smiles_item['U_SMILEY_MOVE_TOP']) ? $smiles_item['U_SMILEY_MOVE_TOP'] : ''; ?>"><img src="../templates/all/images/2uparrow.png" alt="<?php echo isset($this->vars['L_MOVE_TOP']) ? $this->vars['L_MOVE_TOP'] : $this->lang('L_MOVE_TOP'); ?> " title="<?php echo isset($this->vars['L_MOVE_TOP']) ? $this->vars['L_MOVE_TOP'] : $this->lang('L_MOVE_TOP'); ?>" /></a><a href="<?php echo isset($smiles_item['U_SMILEY_MOVE_UP']) ? $smiles_item['U_SMILEY_MOVE_UP'] : ''; ?>"><img src="../templates/all/images/1uparrow.png" alt="<?php echo isset($this->vars['L_MOVE_UP']) ? $this->vars['L_MOVE_UP'] : $this->lang('L_MOVE_UP'); ?> " title="<?php echo isset($this->vars['L_MOVE_UP']) ? $this->vars['L_MOVE_UP'] : $this->lang('L_MOVE_UP'); ?>" /></a><a href="<?php echo isset($smiles_item['U_SMILEY_MOVE_DOWN']) ? $smiles_item['U_SMILEY_MOVE_DOWN'] : ''; ?>"><img src="../templates/all/images/1downarrow.png" alt="<?php echo isset($this->vars['L_MOVE_DOWN']) ? $this->vars['L_MOVE_DOWN'] : $this->lang('L_MOVE_DOWN'); ?> " title="<?php echo isset($this->vars['L_MOVE_DOWN']) ? $this->vars['L_MOVE_DOWN'] : $this->lang('L_MOVE_DOWN'); ?>" /></a><a href="<?php echo isset($smiles_item['U_SMILEY_MOVE_END']) ? $smiles_item['U_SMILEY_MOVE_END'] : ''; ?>"><img src="../templates/all/images/2downarrow.png" alt="<?php echo isset($this->vars['L_MOVE_END']) ? $this->vars['L_MOVE_END'] : $this->lang('L_MOVE_END'); ?> " title="<?php echo isset($this->vars['L_MOVE_END']) ? $this->vars['L_MOVE_END'] : $this->lang('L_MOVE_END'); ?>" /></a>&nbsp;<a href="<?php echo isset($smiles_item['U_SMILEY_EDIT']) ? $smiles_item['U_SMILEY_EDIT'] : ''; ?>"><img src="<?php echo isset($this->vars['IMG_CMS_ICON_EDIT']) ? $this->vars['IMG_CMS_ICON_EDIT'] : $this->lang('IMG_CMS_ICON_EDIT'); ?>" alt="<?php echo isset($this->vars['L_EDIT']) ? $this->vars['L_EDIT'] : $this->lang('L_EDIT'); ?>" title="<?php echo isset($this->vars['L_EDIT']) ? $this->vars['L_EDIT'] : $this->lang('L_EDIT'); ?>" /></a>&nbsp;<a href="<?php echo isset($smiles_item['U_SMILEY_DELETE']) ? $smiles_item['U_SMILEY_DELETE'] : ''; ?>"><img src="<?php echo isset($this->vars['IMG_CMS_ICON_DELETE']) ? $this->vars['IMG_CMS_ICON_DELETE'] : $this->lang('IMG_CMS_ICON_DELETE'); ?>" alt="<?php echo isset($this->vars['L_DELETE']) ? $this->vars['L_DELETE'] : $this->lang('L_DELETE'); ?>" title="<?php echo isset($this->vars['L_DELETE']) ? $this->vars['L_DELETE'] : $this->lang('L_DELETE'); ?>" /></a></td>
		</tr>
		</table>
		</li>
		<?php

} // END smiles

if(isset($smiles_item)) { unset($smiles_item); } 

?>
		</ul>
	</td>
</tr>
<tr>
	<td class="cat"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="add" value="<?php echo isset($this->vars['L_SMILEY_ADD']) ? $this->vars['L_SMILEY_ADD'] : $this->lang('L_SMILEY_ADD'); ?>" class="mainoption" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="import_pack" value="<?php echo isset($this->vars['L_IMPORT_PACK']) ? $this->vars['L_IMPORT_PACK'] : $this->lang('L_IMPORT_PACK'); ?>">&nbsp;&nbsp;<input class="liteoption" type="submit" name="export_pack" value="<?php echo isset($this->vars['L_EXPORT_PACK']) ? $this->vars['L_EXPORT_PACK'] : $this->lang('L_EXPORT_PACK'); ?>"></td>
</tr>
</table>
</form>

<form method="post" action="<?php echo isset($this->vars['S_POSITION_ACTION']) ? $this->vars['S_POSITION_ACTION'] : $this->lang('S_POSITION_ACTION'); ?>">
<table class="forumline">
<tr><th colspan="2"><?php echo isset($this->vars['L_SMILEY_CONFIG']) ? $this->vars['L_SMILEY_CONFIG'] : $this->lang('L_SMILEY_CONFIG'); ?></th></tr>
<tr><td class="row1"><?php echo isset($this->vars['L_POSITION_NEW_SMILIES']) ? $this->vars['L_POSITION_NEW_SMILIES'] : $this->lang('L_POSITION_NEW_SMILIES'); ?></td><td class="row2"><?php echo isset($this->vars['POSITION_SELECT']) ? $this->vars['POSITION_SELECT'] : $this->lang('POSITION_SELECT'); ?></td></tr>
<tr><td class="cat tdalignc" colspan="2"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="change" value="<?php echo isset($this->vars['L_SMILEY_CHANGE_POSITION']) ? $this->vars['L_SMILEY_CHANGE_POSITION'] : $this->lang('L_SMILEY_CHANGE_POSITION'); ?>" class="mainoption" /></td></tr>
</table>
</form>

<div id="sort-info-box" class="row-center" style="position: fixed; top: 10px; right: 10px; z-index: 1; background: none; border: none; width: 300px; padding: 3px;"></div>

<script type="text/javascript">
// <![CDATA[
//var box_begin = '<div id="result-box" style="height: 16px; border: solid 1px #228822; background: #77dd99;"><span class="text_green">';
//var box_end = '<\/span><\/div>';
var box_begin = '<div id="result-box" class="rmbox rmb-green"><p class="rmb-center">';
var box_end = '<\/p><\/div>';
var box_updated = box_begin;
var page_url = ip_root_path;

var sort_info_box = jQuery('#sort-info-box');
var smileys = jQuery('#smileys');
box_updated += '<?php echo isset($this->vars['L_SMILEYS_UPDATED']) ? $this->vars['L_SMILEYS_UPDATED'] : $this->lang('L_SMILEYS_UPDATED'); ?>';
box_updated += box_end;
page_url += 'cms_db_update.';
page_url += php_ext;

smileys.sortable(
{
	update: function ()
	{
		update_order();
		sort_info_box.html(box_updated);
		setTimeout(function ()
		{
			sort_info_box.html('');
		}, 2500);
	},

	handle: '.icon-edit-move-empty'

}).disableSelection();

function update_order()
{
	$.post(page_url, 'mode=update_smileys_order&' + smileys.sortable('serialize') + '&sid=' + S_SID);
}
// ]]>
</script>