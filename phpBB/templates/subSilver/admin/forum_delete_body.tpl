<br clear="all" />

<h1>Delete</h1>

<p>The form below will allow you to delete a forum/category and decide where you want to put all topics/forums inside that forum/category.</p>

<form action="{S_FORUM_ACTION}" method="POST">

<table width="99%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg" width="100%"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td class="cat" colspan="2"><span class="cattitle">Delete</span></td>
			</tr>
			<tr>
				<td class="row1">Name:</td>
				<td class="row2"><span class="row1">{NAME}</span></td>
			</tr>
			<tr>
				<td class="row1">Move everything to:</td>
				<td class="row2">
					{S_SELECT_TO}
				</td>
			</tr>
			<tr>
				<td class="row2" colspan="2" align="center">
					<input type="hidden" name="mode" value="{S_NEWMODE}">
					<input type="hidden" name="from_id" value="{S_FROM_ID}">
					<input type="submit" name="submit" value="{BUTTONVALUE}">
				</td>
			</tr>
		</table></td>
	</tr>
</table>

</form>
		
<br clear="all">
