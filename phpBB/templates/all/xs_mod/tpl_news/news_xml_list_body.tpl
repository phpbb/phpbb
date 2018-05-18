<form method="post" action="{S_FORUM_ACTION}">
<table width="100%" cellspacing="1" cellpadding="3" border="0" class="forumline">
<tr>
	<th colspan="5">{L_MENU_TITLE}</th>
</tr>
<tr>
	<td colspan="5" class="row">{L_MENU_EXPLAIN}</td>
</tr>
<tr>
	<th colspan="5">{L_MENU_SETTINGS}</th>
</tr>
<tr>
	<td class="row" align="center" width="35"><span class="gen">{L_SHOW}</span></td>
	<td class="row" align="left"><span class="gen">{L_TITLE}</span></td>
	<td class="row" align="center" width="25"><span class="gen">{L_EDIT}</span></td>
	<td class="row" align="center" width="25"><span class="gen">{L_DELETE}</span></td>
</tr>
<!-- BEGIN xml_feed -->
<tr> 
	<td class="row2" align="center" >{xml_feed.XML_FEED_DISPLAY}</td>
	<td class="row1" align="left" valign="top"><span class="gen" style="overflow: auto;">{xml_feed.XML_TITLE}</span></td>
	<td class="row2" align="center" valign="middle"><span class="gen"><a href="{xml_feed.U_XML_EDIT}">{L_EDIT}</a></span></td>
	<td class="row1" align="center" valign="middle"><span class="gen"><a href="{xml_feed.U_XML_DELETE}">{L_DELETE}</a></span></td>
</tr>
<!-- END xml_feed -->

<!-- BEGIN xml_no_feeds -->
<tr> 
	<td class="row2">&nbsp;</td>
	<td class="row1" align="left" valign="top"><span class="gen">{xml_no_feeds.XML_TITLE}</span></td>
	<td class="row2" align="center" valign="middle">&nbsp;</td>
	<td class="row1" align="center" valign="middle">&nbsp;</td>
</tr>
<!-- END xml_no_feeds -->
<tr>
	<td colspan="5" align="center" class="catBottom"><input type="submit" class="liteoption"  name="addxml" value="{L_CREATE_FEED}" /></td>
</tr>
</table>
</form>
