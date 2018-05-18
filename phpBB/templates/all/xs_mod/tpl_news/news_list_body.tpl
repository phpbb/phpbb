<form method="post" action="{S_FORUM_ACTION}">
<table class="forumline">
<tr><th>{L_MENU_TITLE}</th></tr>
<tr><td class="row1">{L_MENU_EXPLAIN}</td></tr>
</table>

<table class="forumline">
<tr><th colspan="5">{L_MENU_SETTINGS}</th></tr>
<tr>
	<th width="35">{L_SHOW}</th>
	<th width="75">{L_DATE}</td>
	<th>{L_TITLE}</td>
	<th class="tw25px">{L_EDIT}</th>
	<th class="tw25px">{L_DELETE}</th>
</tr>
<!-- BEGIN newsitem -->
<tr> 
	<td class="row1 row-center">{newsitem.NEWS_ITEM_DISPLAY}</td>
	<td class="row1 row-center">{newsitem.NEWS_DATE}</td>
	<td class="row1"><span class="gen" style="overflow: auto;">{newsitem.NEWS_ITEM}</span></td>
	<td class="row1 row-center"><span class="gen"><a href="{newsitem.U_NEWS_EDIT}">{L_EDIT}</a></span></td>
	<td class="row1 row-center"><span class="gen"><a href="{newsitem.U_NEWS_DELETE}">{L_DELETE}</a></span></td>
</tr>
<!-- END newsitem -->

<!-- BEGIN no_news -->
<tr> 
	<td class="row1">&nbsp;</td>
	<td class="row1"><span class="gen">{no_news.NEWS_DATE}</span></td>
	<td class="row1"><span class="gen">{no_news.NEWS_ITEM}</span></td>
	<td class="row1 row-center">&nbsp;</td>
	<td class="row1 row-center">&nbsp;</td>
</tr>
<!-- END no_news -->
<tr><td class="cat" colspan="5"><input type="submit" class="liteoption" name="addnews" value="{L_CREATE_NEWS}" /></td></tr>
</table>
</form>
