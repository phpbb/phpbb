
<h1>{L_PAGE_TITLE}</h1>

<p>{L_PAGE_EXPLAIN}</p>

<script type="text/javascript">
function checkAll(theForm, cName, allNo_stat) {
var n=theForm.elements.length;
for (var i=0;i<n;i++){
if (theForm.elements[i].className.indexOf(cName) !=-1){
if (allNo_stat.checked) {
theForm.elements[i].checked = true;
} else {
theForm.elements[i].checked = false;
}
}
}
}
function confirmSubmit()
{
var prune=confirm("{L_CONFIRM_MESSAGE}");
if (prune)
	return true ;
else
	return false ;
}
</script>

<form id="prune_users" method="post" action="{S_FORM_ACTION}"><table cellspacing="1" cellpadding="4" border="0" align="center" class="forumline" width="80%">
	<tr>
		<th class="thCornerL">{L_USERNAME}</th>
		<!-- BEGIN user_lastvisit --><th class="thTop">{L_USER_LAST_VISIT}</th><!-- END user_lastvisit -->			
		<!-- BEGIN user_regdate --><th class="thTop">{L_USER_REGDATE}</th><!-- END user_regdate -->				
		<!-- BEGIN user_active --><th class="thTop">{L_USER_ACTIVE}</th><!-- END user_active -->	
		<!-- BEGIN user_posts --><th class="thTop">{L_USER_POSTS}</th><!-- END user_posts -->	
		<th class="thTop">{L_NOTIFY_USER}</th>		
		<th class="thCornerR">{L_SELECTED}</th>
	</tr>
	<!-- BEGIN inactive_users -->
	<tr>
		<td class="{inactive_users.ROW_CLASS}" align="center"><a href="{inactive_users.U_USER_PROFILE}">{inactive_users.USERNAME}</a></td>
		<!-- BEGIN user_lastvisit --><td class="{inactive_users.ROW_CLASS}" align="center">{inactive_users.USER_LAST_VISIT}</td><!-- END user_lastvisit -->			
		<!-- BEGIN user_regdate --><td class="{inactive_users.ROW_CLASS}" align="center">{inactive_users.USER_REGDATE}</td>	<!-- END user_regdate -->				
		<!-- BEGIN user_active --><td class="{inactive_users.ROW_CLASS}" align="center">{inactive_users.USER_ACTIVE}</td><!-- END user_active -->	
		<!-- BEGIN user_posts --><td class="{inactive_users.ROW_CLASS}" align="center">{inactive_users.USER_POSTS}</td><!-- END user_posts -->	
		<td class="{inactive_users.ROW_CLASS}" align="center"><a href="{inactive_users.U_NOTIFY_USER}">{L_EMAIL}</a></td>
	    <td class="{inactive_users.ROW_CLASS}" align="center"><input type="checkbox" name="inactive_users[]" value="{inactive_users.USER_ID}" class="user_selected" checked="checked"></td>
	</tr>
	<!-- END inactive_users -->		
	<!-- BEGIN no_inactive_users -->
	<tr>
		<td class="row1" align="center" colspan="{NUMBER_OF_COLUMNS}">{L_NONE}</td>
  	</tr>
	<!-- END no_inactive_users -->			
	<tr>
		<td class="catBottom" align="right" colspan="{NUMBER_OF_COLUMNS}">{L_SELECT_ALL_NONE} <input type="checkbox" onclick="checkAll(document.getElementById('prune_users'), 'user_selected',this);" checked="checked"/></td>
	</tr>
</table>
<br><div align="center"><input type="submit" class="mainoption" name="submit" value="{L_SUBMIT}" onClick="return confirmSubmit()" /> </div><br>
</form>
