<form method="post" action="{S_FORUM_ACTION}">
<table class="forumline">
<tr><th>{L_MENU_TITLE}</th></tr>
<tr><td class="row1">{L_MENU_EXPLAIN}<br /><br />{L_MASTER_SWITCH}<br /><br />{L_MENU_EXPLAINS}</td></tr>
</table>

<table class="forumline">
<tr><th colspan="4">{L_MENU_SETTINGS}</th></tr>
<tr>
	<th width="35">{L_SHOW}</th>
	<th>{L_TITLE}</td>
	<th class="tw25px">{L_EDIT}</th>
	<th class="tw25px">{L_DELETE}</th>
</tr>
<!-- BEGIN xml_feed -->
<tr> 
	<td class="row1 row-center">{xml_feed.XML_FEED_DISPLAY}</td>
	<td class="row1"><span class="gen" style="overflow: auto;">{xml_feed.XML_TITLE}</span></td>
	<td class="row1 row-center"><span class="gen"><a href="{xml_feed.U_XML_EDIT}">{L_EDIT}</a></span></td>
	<td class="row1 row-center"><span class="gen"><a href="{xml_feed.U_XML_DELETE}">{L_DELETE}</a></span></td>
</tr>
<!-- END xml_feed -->

<!-- BEGIN xml_no_feeds -->
<tr> 
	<td class="row1">&nbsp;</td>
	<td class="row1"><span class="gen">{xml_no_feeds.XML_TITLE}</span></td>
	<td class="row1 row-center">&nbsp;</td>
	<td class="row1 row-center">&nbsp;</td>
</tr>
<!-- END xml_no_feeds -->
<tr><td class="cat" colspan="4"><input type="submit" class="liteoption"  name="addxml" value="{L_CREATE_FEED}" /></td></tr>
</table>
</form>
