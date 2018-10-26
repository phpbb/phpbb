<h1>{L_SMILEY_TITLE}</h1>
<p>{L_SMILEY_TEXT}</p>

<form method="post" action="{S_SMILEY_ACTION}">
<table class="forumline">
<tr>
	<td class="cat">{S_HIDDEN_FIELDS}<input type="submit" name="add" value="{L_SMILEY_ADD}" class="mainoption" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="import_pack" value="{L_IMPORT_PACK}">&nbsp;&nbsp;<input class="liteoption" type="submit" name="export_pack" value="{L_EXPORT_PACK}"></td>
</tr>
<tr>
	<td class="row1" style="padding: 0px;">
		<table class="nav-div" width="100%" align="center" style="padding: 0px;" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<th style="text-align: center; width: 30px;">&nbsp;</th>
			<th style="text-align: center; width: 160px;">{L_SMILE}</th>
			<th style="text-align: center; width: 170px;">{L_CODE}</th>
			<th style="text-align: center;">{L_EMOT}</th>
			<th style="text-align: center; width: 150px;">{L_ACTION}</th>
		</tr>
		</table>
		<ul id="smileys" style="margin: 0px; padding: 0px; list-style-type: none;">
		<!-- BEGIN smiles -->
		<li id="item_{smiles.SMILEY_ID}">
		<table>
		<tr class="{smiles.ROW_CLASS}h">
			<td class="{smiles.ROW_CLASS} row-center" style="padding: 0px; background: none; width: 40px;"><a class="icon-edit-move-empty" href="javascript:void(0);"><img src="../templates/all/images/cms_icon_move.png" alt="{L_MOVE} " title="{L_MOVE}" /></a></td>
			<td class="{smiles.ROW_CLASS} row-center" style="padding: 0px; background: none; width: 170px;"><img src="{smiles.SMILEY_IMG}" alt="{smiles.CODE}" /></td>
			<td class="{smiles.ROW_CLASS}" style="padding: 0px; background: none; width: 180px;">{smiles.CODE}</td>
			<td class="{smiles.ROW_CLASS}" style="padding: 0px; background: none;"><b>{smiles.EMOT}</b> - [{smiles.SMILEY_URL}]</td>
			<td class="{smiles.ROW_CLASS} row-center" style="padding: 0px; background: none; width: 160px;"><a href="{smiles.U_SMILEY_MOVE_TOP}"><img src="../templates/all/images/2uparrow.png" alt="{L_MOVE_TOP} " title="{L_MOVE_TOP}" /></a><a href="{smiles.U_SMILEY_MOVE_UP}"><img src="../templates/all/images/1uparrow.png" alt="{L_MOVE_UP} " title="{L_MOVE_UP}" /></a><a href="{smiles.U_SMILEY_MOVE_DOWN}"><img src="../templates/all/images/1downarrow.png" alt="{L_MOVE_DOWN} " title="{L_MOVE_DOWN}" /></a><a href="{smiles.U_SMILEY_MOVE_END}"><img src="../templates/all/images/2downarrow.png" alt="{L_MOVE_END} " title="{L_MOVE_END}" /></a>&nbsp;<a href="{smiles.U_SMILEY_EDIT}"><img src="{IMG_CMS_ICON_EDIT}" alt="{L_EDIT}" title="{L_EDIT}" /></a>&nbsp;<a href="{smiles.U_SMILEY_DELETE}"><img src="{IMG_CMS_ICON_DELETE}" alt="{L_DELETE}" title="{L_DELETE}" /></a></td>
		</tr>
		</table>
		</li>
		<!-- END smiles -->
		</ul>
	</td>
</tr>
<tr>
	<td class="cat">{S_HIDDEN_FIELDS}<input type="submit" name="add" value="{L_SMILEY_ADD}" class="mainoption" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="import_pack" value="{L_IMPORT_PACK}">&nbsp;&nbsp;<input class="liteoption" type="submit" name="export_pack" value="{L_EXPORT_PACK}"></td>
</tr>
</table>
</form>

<form method="post" action="{S_POSITION_ACTION}">
<table class="forumline">
<tr><th colspan="2">{L_SMILEY_CONFIG}</th></tr>
<tr><td class="row1">{L_POSITION_NEW_SMILIES}</td><td class="row2">{POSITION_SELECT}</td></tr>
<tr><td class="cat tdalignc" colspan="2">{S_HIDDEN_FIELDS}<input type="submit" name="change" value="{L_SMILEY_CHANGE_POSITION}" class="mainoption" /></td></tr>
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
box_updated += '{L_SMILEYS_UPDATED}';
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